<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\StockTransfer;
use App\Models\Product;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            ->whereIn('status', ['approved', 'completed'])
            ->with(['product', 'fromBranch.business', 'toBranch.business'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

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
            'quantity' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:500',
        ]);

        // Validate that the product exists in the source branch with sufficient stock
        $sourceBranchProduct = \App\Models\BranchProduct::where('branch_id', $validated['from_branch_id'])
            ->where('product_id', $validated['product_id'])
            ->first();

        if (!$sourceBranchProduct || $sourceBranchProduct->stock_quantity < $validated['quantity']) {
            return back()->with('error', 'Insufficient stock in the selected branch.');
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

        StockTransfer::create([
            'from_branch_id' => $validated['from_branch_id'],
            'to_branch_id' => $manager->branch_id,
            'product_id' => $validated['product_id'],
            'quantity' => $validated['quantity'],
            'reason' => $validated['reason'],
            'status' => 'pending',
            'requested_by' => $manager->id,
            'requested_at' => now(),
        ]);

        return back()->with('success', 'Item request submitted successfully. It will be reviewed by an administrator.');
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
}