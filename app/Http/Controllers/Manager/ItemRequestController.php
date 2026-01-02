<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\StockTransfer;
use App\Models\Product;
use App\Models\Branch;
use App\Models\BranchProduct;
use App\Imports\ItemRequestImport;
use App\Exports\ItemRequestTemplateExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ItemRequestController extends Controller
{
    /**
     * Display the item request interface for managers
     */
    public function index()
    {
        $user = Auth::user();
        
        // Allowed destination branches
        $destinationBranches = collect();

        if ($user->role === 'business_admin') {
             $destinationBranches = Branch::where('business_id', $user->business_id)->get();
        } elseif ($user->role === 'manager' && $user->branch_id) {
             $destinationBranches = Branch::where('id', $user->branch_id)->get();
        } else {
             return redirect()->route('dashboard.manager')
                ->with('error', 'You must be assigned to a branch or business to request items.');
        }

        // Get pending requests
        $query = StockTransfer::where('status', 'pending')
             ->with(['product', 'fromBranch.business', 'toBranch.business'])
             ->orderBy('created_at', 'desc');

        if ($user->role === 'business_admin') {
            $query->whereHas('toBranch', function($q) use ($user) {
                $q->where('business_id', $user->business_id);
            });
        } else {
            $query->where('to_branch_id', $user->branch_id);
        }
        
        $pendingRequests = $query->paginate(10);

        // Get completed requests
        $completedQuery = StockTransfer::whereIn('status', ['approved', 'completed', 'rejected', 'cancelled'])
             ->with(['product', 'fromBranch.business', 'toBranch.business'])
             ->orderBy('updated_at', 'desc');

        if ($user->role === 'business_admin') {
            $completedQuery->whereHas('toBranch', function($q) use ($user) {
                $q->where('business_id', $user->business_id);
            });
        } else {
            $completedQuery->where('to_branch_id', $user->branch_id);
        }

        $completedRequests = $completedQuery->paginate(10, ['*'], 'completed_page');

        // Get available products from other branches OR from Warehouse
        $businessId = $user->business_id ?? $user->branch->business_id;
        
        // Exclude current user's branch IF they are a manager (to prevent self-request)
        // If Business Admin, they can request FROM any branch TO any branch, so we might need distinct logic.
        // For simplicity, let's list all valid sources.
        
        $availableProducts = Product::where('business_id', $businessId)
            ->where(function($query) {
                // Product has unassigned units (Warehouse Stock)
                $query->whereColumn('total_units', '>', 'assigned_units')
                      // OR Product has stock in other branches
                      ->orWhereHas('branchProducts', function($bp) {
                          $bp->where('stock_quantity', '>', 0);
                      });
            })
            // Continue to eagerly load branchProducts for dropdown
            ->with(['branchProducts' => function ($query) {
                $query->where('stock_quantity', '>', 0)
                      ->with('branch.business');
            }])
            ->get();

        // Check how many branches the business has in total
        $totalBusinessBranches = Branch::where('business_id', $businessId)->count();

        return view('manager.item-requests', compact('pendingRequests', 'completedRequests', 'availableProducts', 'user', 'destinationBranches', 'totalBusinessBranches'));
    }

    /**
     * Store a new item request
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Define allowed destination branches
        $allowedDestinationIds = [];
        if ($user->role === 'business_admin') {
             $allowedDestinationIds = Branch::where('business_id', $user->business_id)->pluck('id')->toArray();
        } elseif ($user->role === 'manager' && $user->branch_id) {
             $allowedDestinationIds = [$user->branch_id];
        } else {
            return back()->with('error', 'You are not authorized to request items.');
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'to_branch_id' => ['required', 'exists:branches,id', \Illuminate\Validation\Rule::in($allowedDestinationIds)],
            'from_branch_id' => [
                'nullable',
                'exists:branches,id',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value == $request->to_branch_id) {
                        $fail('Source and Destination branches cannot be the same.');
                    }
                },
            ],
            'quantity_of_boxes' => 'required|integer|min:1',
            'quantity_per_box' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:500',
        ]);
        
        $destinationBranchId = $validated['to_branch_id'];

        // Calculate total quantity
        $totalQuantity = $validated['quantity_of_boxes'] * $validated['quantity_per_box'];
        $product = Product::findOrFail($validated['product_id']);

        // IF requesting from Warehouse (from_branch_id is null)
        if (empty($validated['from_branch_id'])) {
            if (!$product->hasAvailableUnits($totalQuantity)) {
                return back()->with('error', 'Insufficient stock in Warehouse. Available: ' . $product->available_units . ' units.');
            }
            
            // For Warehouse requests, we rely on the main product pricing
            $price = $product->price;
            $costPrice = $product->cost_price;
            $pricePerKilo = $product->price_per_kilo;
            $pricePerBox = $product->price_per_box;
            $weightUnit = $product->weight_unit;
            $pricePerUnitWeight = $product->price_per_unit_weight;

        } else {
            // Requesting from another Branch
            $sourceBranchProduct = \App\Models\BranchProduct::where('branch_id', $validated['from_branch_id'])
                ->where('product_id', $validated['product_id'])
                ->first();

            if (!$sourceBranchProduct) {
                return back()->with('error', 'Product not found in the selected branch.');
            }
            
            // Check Available Stock (stock_quantity)
            if ($sourceBranchProduct->stock_quantity < $totalQuantity) {
                return back()->with('error', 'Insufficient stock in the selected branch. Available: ' . $sourceBranchProduct->stock_quantity . ' units.');
            }
            
            // Ideally check boxes too, but if not tracked strictly, stock_quantity is primary.

            // Use branch-specific pricing/cost
            $price = $sourceBranchProduct->price;
            $costPrice = $sourceBranchProduct->cost_price;
            $pricePerKilo = $sourceBranchProduct->price_per_kilo;
            $pricePerBox = $sourceBranchProduct->price_per_box;
            $weightUnit = $sourceBranchProduct->weight_unit;
            $pricePerUnitWeight = $sourceBranchProduct->price_per_unit_weight;
        }

        // Check duplicate pending requests
        $existingRequest = StockTransfer::where('to_branch_id', $destinationBranchId)
            ->where('from_branch_id', $validated['from_branch_id'])
            ->where('product_id', $validated['product_id'])
            ->where('status', 'pending')
            ->exists();

        if ($existingRequest) {
            return back()->with('error', 'You already have a pending request for this product from this source.');
        }

        StockTransfer::create([
            'from_branch_id' => $validated['from_branch_id'],
            'to_branch_id' => $destinationBranchId,
            'product_id' => $validated['product_id'],
            'quantity' => $totalQuantity,
            'quantity_of_boxes' => $validated['quantity_of_boxes'],
            'quantity_per_box' => $validated['quantity_per_box'],
            'reason' => $validated['reason'],
            'status' => 'pending',
            'requested_by' => $user->id,
            'requested_at' => now(),
            // Pricing info
            'price' => $price,
            'cost_price' => $costPrice,
            'price_per_kilo' => $pricePerKilo,
            'price_per_box' => $pricePerBox,
            'weight_unit' => $weightUnit,
            'price_per_unit_weight' => $pricePerUnitWeight,
        ]);

        $sourceName = empty($validated['from_branch_id']) ? 'Central Warehouse' : 'Selected Branch';
        return back()->with('success', 'Item request submitted successfully. Requesting ' . $validated['quantity_of_boxes'] . ' boxes (' . $totalQuantity . ' units) from ' . $sourceName . '.');
    }

    /**
     * Cancel a pending request
     */
    public function cancel(StockTransfer $stockTransfer)
    {
        $manager = Auth::user();
        
        if ($stockTransfer->to_branch_id !== $manager->branch_id || $stockTransfer->status !== 'pending') {
            return back()->with('error', 'You cannot cancel this request.');
        }

        $stockTransfer->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return back()->with('success', 'Request cancelled successfully.');
    }

    /**
     * Download bulk item request template
     */
    public function downloadTemplate()
    {
        return Excel::download(new ItemRequestTemplateExport(), 'item_request_template.xlsx');
    }

    /**
     * Upload and process bulk item requests
     */
    public function uploadBulkRequests(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:2048',
        ]);

        try {
            $import = new ItemRequestImport();
            Excel::import($import, $request->file('file'));

            $errors = $import->getErrors();
            
            if (!empty($errors)) {
                $errorMessage = "Import completed with errors:\n" . implode("\n", $errors);
                return back()->with('warning', $import->getSummary())
                    ->with('import_errors', $errors);
            }

            return back()->with('success', $import->getSummary());

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to process bulk upload: ' . $e->getMessage());
        }
    }
}