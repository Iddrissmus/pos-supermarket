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
            $businessId = $user->business_id;
            $pendingRequests = $query->whereHas('fromBranch', function($q) use ($businessId) {
                    $q->where('business_id', $businessId);
                })
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        } elseif ($user->role === 'manager' && $user->branch_id) {
            // Managers only see requests for their branch (as source)
            $pendingRequests = $query->where('from_branch_id', $user->branch_id)
                ->orderBy('created_at', 'desc')
                ->paginate(15);
        } else {
            // For users without permission, return empty paginated result
            $pendingRequests = new \Illuminate\Pagination\LengthAwarePaginator(
                [],
                0,
                15,
                1,
                ['path' => request()->url()]
            );
        }

        return view('requests.approval', compact('pendingRequests'));
    }

    /**
     * Approve a request and execute stock transfer
     */
    public function approve(Request $request, StockTransfer $stockTransfer)
    {
        $user = Auth::user();
        
        // Authorization check
        if (!$this->canProcessRequest($user, $stockTransfer)) {
            return back()->with('error', 'You are not authorized to approve this request.');
        }

        // Validate that request is still pending
        if ($stockTransfer->status !== 'pending') {
            return back()->with('error', 'This request has already been processed.');
        }

        $validated = $request->validate([
            'approval_note' => 'nullable|string|max:500'
        ]);

        try {
            DB::transaction(function () use ($stockTransfer, $validated, $user) {
                // Check source branch has sufficient stock
                $sourceBranchProduct = BranchProduct::where('branch_id', $stockTransfer->from_branch_id)
                    ->where('product_id', $stockTransfer->product_id)
                    ->first();

                if (!$sourceBranchProduct || $sourceBranchProduct->stock_quantity < $stockTransfer->quantity) {
                    throw new \Exception('Insufficient stock at source branch.');
                }

                // Get or create destination branch product
                $destBranchProduct = BranchProduct::firstOrCreate(
                    [
                        'branch_id' => $stockTransfer->to_branch_id,
                        'product_id' => $stockTransfer->product_id
                    ],
                    [
                        'stock_quantity' => 0,
                        'cost_price' => $sourceBranchProduct->cost_price,
                        'price' => $sourceBranchProduct->price,
                        'reorder_level' => $sourceBranchProduct->reorder_level ?? 10
                    ]
                );

                // Execute stock transfer using adjustStock method
                $sourceBranchProduct->adjustStock(
                    -$stockTransfer->quantity, 
                    'transfer_out', 
                    "Transfer to {$stockTransfer->toBranch->display_label} - Request #{$stockTransfer->id}"
                );
                
                $destBranchProduct->adjustStock(
                    $stockTransfer->quantity, 
                    'transfer_in', 
                    "Transfer from {$stockTransfer->fromBranch->display_label} - Request #{$stockTransfer->id}"
                );

                // Update request status
                $stockTransfer->update([
                    'status' => 'approved',
                    'approved_by' => $user->id,
                    'approved_at' => now(),
                    'approval_note' => $validated['approval_note'] ?? null
                ]);
            });

            // Send notifications after transaction completes
            $this->notifyTransferCompletion($stockTransfer->fresh(['fromBranch', 'toBranch', 'product']));

            return back()->with('success', 'Request approved successfully. Stock has been transferred.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to approve request: ' . $e->getMessage());
        }
    }

    /**
     * Reject a request
     */
    public function reject(Request $request, StockTransfer $stockTransfer)
    {
        $user = Auth::user();
        
        // Authorization check
        if (!$this->canProcessRequest($user, $stockTransfer)) {
            return back()->with('error', 'You are not authorized to reject this request.');
        }

        // Validate that request is still pending
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

    /**
     * Check if user can process this request
     */
    private function canProcessRequest($user, StockTransfer $stockTransfer): bool
    {
        // Business admins can process any request within their business
        if ($user->role === 'business_admin') {
            return $stockTransfer->fromBranch->business_id === $user->business_id;
        }

        // Managers can only process requests from their branch
        if ($user->role === 'manager' && $user->branch_id === $stockTransfer->from_branch_id) {
            return true;
        }

        return false;
    }

    /**
     * Send notifications to managers when stock transfer is completed
     */
    private function notifyTransferCompletion(StockTransfer $transfer): void
    {
        try {
            // Notify sender branch manager
            $fromBranchManagers = User::where('role', 'manager')
                ->where('branch_id', $transfer->from_branch_id)
                ->get();

            foreach ($fromBranchManagers as $manager) {
                $manager->notify(new StockTransferCompletedNotification($transfer, false));
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