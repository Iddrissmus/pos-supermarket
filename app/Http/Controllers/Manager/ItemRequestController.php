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
        $manager = Auth::user();
        
        if (!$manager->managesBranch()) {
            return redirect()->route('dashboard.manager')
                ->with('error', 'You must be assigned to a branch to request items.');
        }

        // Get pending requests for this manager's branch
        $pendingRequests = StockTransfer::where('to_branch_id', $manager->branch_id)
            ->where('status', 'pending')
            ->with(['product', 'fromBranch.business', 'toBranch.business'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Get completed requests for reference
        $completedRequests = StockTransfer::where('to_branch_id', $manager->branch_id)
            ->whereIn('status', ['approved', 'completed', 'rejected', 'cancelled'])
            ->with(['product', 'fromBranch.business', 'toBranch.business'])
            ->orderBy('updated_at', 'desc')
            ->paginate(10, ['*'], 'completed_page');

        // Get available products from other branches for requesting
        $businessId = $manager->branch->business_id;
        $availableProducts = Product::where('business_id', $businessId)
            ->with(['branchProducts' => function ($query) use ($manager) {
                $query->where('branch_id', '!=', $manager->branch_id)
                      ->where('stock_quantity', '>', 0)
                      ->with('branch.business');
            }])
            ->get()
            ->filter(function ($product) {
                // Only show products that have stock in other branches
                return $product->branchProducts->count() > 0;
            });

        return view('manager.item-requests', compact('pendingRequests', 'completedRequests', 'availableProducts', 'manager'));
    }

    /**
     * Store a new item request
     */
    public function store(Request $request)
    {
        $manager = Auth::user();
        
        if (!$manager->managesBranch()) {
            return back()->with('error', 'You are not authorized to request items.');
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'from_branch_id' => 'required|exists:branches,id',
            'quantity_of_boxes' => 'required|integer|min:1',
            'quantity_per_box' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:500',
        ]);

        // Calculate total quantity
        $totalQuantity = $validated['quantity_of_boxes'] * $validated['quantity_per_box'];

        // Validate that the product exists in the source branch with sufficient stock
        $sourceBranchProduct = \App\Models\BranchProduct::where('branch_id', $validated['from_branch_id'])
            ->where('product_id', $validated['product_id'])
            ->first();

        if (!$sourceBranchProduct) {
            return back()->with('error', 'Product not found in the selected branch.');
        }

        if ($sourceBranchProduct->stock_quantity < $totalQuantity) {
            return back()->with('error', 'Insufficient stock in the selected branch. Available: ' . $sourceBranchProduct->stock_quantity . ' units.');
        }

        if ($sourceBranchProduct->quantity_of_boxes < $validated['quantity_of_boxes']) {
            return back()->with('error', 'Insufficient boxes in the selected branch. Available: ' . $sourceBranchProduct->quantity_of_boxes . ' boxes.');
        }

        // Check if there's already a pending request for the same product from the same branch
        $existingRequest = StockTransfer::where('to_branch_id', $manager->branch_id)
            ->where('from_branch_id', $validated['from_branch_id'])
            ->where('product_id', $validated['product_id'])
            ->where('status', 'pending')
            ->exists();

        if ($existingRequest) {
            return back()->with('error', 'You already have a pending request for this product from this branch.');
        }

        // Fetch pricing information from source branch product
        $sourceBranchProduct = BranchProduct::where('branch_id', $validated['from_branch_id'])
            ->where('product_id', $validated['product_id'])
            ->first();

        StockTransfer::create([
            'from_branch_id' => $validated['from_branch_id'],
            'to_branch_id' => $manager->branch_id,
            'product_id' => $validated['product_id'],
            'quantity' => $totalQuantity,
            'quantity_of_boxes' => $validated['quantity_of_boxes'],
            'quantity_per_box' => $validated['quantity_per_box'],
            'reason' => $validated['reason'],
            'status' => 'pending',
            'requested_by' => $manager->id,
            'requested_at' => now(),
            // Include pricing information from source branch
            'price' => $sourceBranchProduct->price ?? null,
            'cost_price' => $sourceBranchProduct->cost_price ?? null,
            'price_per_kilo' => $sourceBranchProduct->price_per_kilo ?? null,
            'price_per_box' => $sourceBranchProduct->price_per_box ?? null,
            'weight_unit' => $sourceBranchProduct->weight_unit ?? null,
            'price_per_unit_weight' => $sourceBranchProduct->price_per_unit_weight ?? null,
        ]);

        return back()->with('success', 'Item request submitted successfully. Requesting ' . $validated['quantity_of_boxes'] . ' boxes (' . $totalQuantity . ' units) for review by an administrator.');
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