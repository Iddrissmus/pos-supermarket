<?php

namespace App\Http\Controllers;

use App\Models\StockTransfer;
use App\Models\BranchProduct;
use App\Models\StockLog;
use App\Models\User;
use App\Notifications\StockTransferCompletedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RequestApprovalController extends Controller
{
    /**
     * Display pending requests for approval
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get pending requests based on user role
        $query = StockTransfer::where('status', 'pending')
            ->with(['product', 'fromBranch.business', 'toBranch.business', 'requestedByUser', 'fromBranch.branchProducts']);

        if ($user->role === 'business_admin') {
            // Business admins can see all pending requests for their business
            // Include both Branch-to-Branch AND Warehouse-to-Branch requests
            $businessId = $user->business_id;
            
            $pendingRequests = $query->where(function($q) use ($businessId) {
                    $q->whereHas('fromBranch', function($sq) use ($businessId) {
                        $sq->where('business_id', $businessId);
                    })
                    ->orWhere(function($sq) use ($businessId) {
                        $sq->whereNull('from_branch_id')
                           ->whereHas('toBranch', function($ssq) use ($businessId) {
                               $ssq->where('business_id', $businessId);
                           });
                    });
                })
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        } elseif ($user->role === 'manager' && $user->branch_id) {
            // Managers only see requests FROM their branch (as source) to approve? 
            // Usually managers request IN, but if they receive a request FROM another branch, they might need to approve?
            // Assuming this controller is for OUTBOUND approval (sending stock).
            $pendingRequests = $query->where('from_branch_id', $user->branch_id)
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        } else {
            $pendingRequests = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15, 1, ['path' => request()->url()]);
        }

        return view('requests.approval', compact('pendingRequests'));
    }

    public function approve(Request $request, StockTransfer $stockTransfer)
    {
        $user = Auth::user();
        
        if (!$this->canProcessRequest($user, $stockTransfer)) {
            return back()->with('error', 'You are not authorized to approve this request.');
        }

        if ($stockTransfer->status !== 'pending') {
            return back()->with('error', 'This request has already been processed.');
        }

        $validated = $request->validate([
            'approval_note' => 'nullable|string|max:500'
        ]);

        try {
            DB::transaction(function () use ($stockTransfer, $validated, $user) {
                $product = $stockTransfer->product;
                
                // COST PRICE & PRICE determination
                $transferCostPrice = 0;
                $transferPrice = 0;
                $transferReorderLevel = 10;

                // HANDLE SOURCE DEDUCTION
                if ($stockTransfer->from_branch_id) {
                    // Branch-to-Branch
                    $sourceBranchProduct = BranchProduct::where('branch_id', $stockTransfer->from_branch_id)
                        ->where('product_id', $stockTransfer->product_id)
                        ->first();

                    if (!$sourceBranchProduct || $sourceBranchProduct->stock_quantity < $stockTransfer->quantity) {
                        throw new \Exception('Insufficient stock at source branch.');
                    }
                    
                    $transferCostPrice = $sourceBranchProduct->cost_price;
                    $transferPrice = $sourceBranchProduct->price;
                    $transferReorderLevel = $sourceBranchProduct->reorder_level ?? 10;

                    $sourceBranchProduct->adjustStock(
                        -$stockTransfer->quantity, 
                        'transfer_out', 
                        "Transfer to {$stockTransfer->toBranch->display_label} - Request #{$stockTransfer->id}"
                    );
                } else {
                    // Warehouse-to-Branch
                    // Check central availability
                    if (!$product->hasAvailableUnits($stockTransfer->quantity)) {
                         throw new \Exception('Insufficient stock in Central Warehouse. Available: ' . $product->available_units);
                    }
                    
                    $transferCostPrice = $product->cost_price;
                    $transferPrice = $product->price;
                    
                    // Deduct from Central
                    $product->assignUnits($stockTransfer->quantity);
                }

                // HANDLE DESTINATION ADDITION
                $destBranchProduct = BranchProduct::firstOrCreate(
                    [
                        'branch_id' => $stockTransfer->to_branch_id,
                        'product_id' => $stockTransfer->product_id
                    ],
                    [
                        'stock_quantity' => 0,
                        'cost_price' => $transferCostPrice,
                        'price' => $transferPrice,
                        'reorder_level' => $transferReorderLevel
                    ]
                );

                $sourceLabel = $stockTransfer->from_branch_id ? $stockTransfer->fromBranch->display_label : 'Central Warehouse';
                
                $destBranchProduct->adjustStock(
                    $stockTransfer->quantity, 
                    'transfer_in', 
                    "Transfer from {$sourceLabel} - Request #{$stockTransfer->id}"
                );

                // Update request status
                $stockTransfer->update([
                    'status' => 'approved',
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                    'approval_note' => $validated['approval_note'] ?? null
                ]);
            });

            $this->notifyTransferCompletion($stockTransfer->fresh(['fromBranch', 'toBranch', 'product']));

            return back()->with('success', 'Request approved successfully. Stock has been transferred.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to approve request: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, StockTransfer $stockTransfer)
    {
        $user = Auth::user();
        
        if (!$this->canProcessRequest($user, $stockTransfer)) {
            return back()->with('error', 'You are not authorized to reject this request.');
        }

        if ($stockTransfer->status !== 'pending') {
            return back()->with('error', 'This request has already been processed.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        $stockTransfer->update([
            'status' => 'rejected',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'approval_note' => $validated['rejection_reason']
        ]);

        return back()->with('success', 'Request rejected successfully.');
    }

    private function canProcessRequest($user, StockTransfer $stockTransfer): bool
    {
        // Business admins can process any request within their business
        if ($user->role === 'business_admin') {
            // Check if FROM branch is in business OR if TO branch is in business (for Warehouse requests)
            if ($stockTransfer->from_branch_id) {
                return $stockTransfer->fromBranch->business_id === $user->business_id;
            } else {
                // Warehouse Request: Admin owns the destination branch? Then they can approve fetching from Warehouse.
                return $stockTransfer->toBranch->business_id === $user->business_id;
            }
        }

        // Managers can only process requests FROM their branch
        if ($user->role === 'manager' && $stockTransfer->from_branch_id) {
            return $user->branch_id === $stockTransfer->from_branch_id;
        }

        return false;
    }

    private function notifyTransferCompletion(StockTransfer $transfer): void
    {
        try {
            // Notify sender branch manager (if not warehouse)
            if ($transfer->from_branch_id) {
                $fromBranchManagers = User::where('role', 'manager')
                    ->where('branch_id', $transfer->from_branch_id)
                    ->get();

                foreach ($fromBranchManagers as $manager) {
                    $manager->notify(new StockTransferCompletedNotification($transfer, false));
                }
            }

            // Notify recipient branch manager
            $toBranchManagers = User::where('role', 'manager')
                ->where('branch_id', $transfer->to_branch_id)
                ->get();

            foreach ($toBranchManagers as $manager) {
                $manager->notify(new StockTransferCompletedNotification($transfer, true));
            }
        } catch (\Exception $e) {
            logger()->error('Failed to send stock transfer completion notifications: ' . $e->getMessage());
        }
    }
}