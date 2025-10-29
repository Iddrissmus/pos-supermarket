<?php

namespace App\Services;

use App\Models\BranchProduct;
use App\Models\StockLog;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Support\Facades\DB;
use App\Models\StockTransfer;

class StockReorderService
{
    /**
     * Run the reorder scan.
     *
     * Returns array with counts for reporting.
     */
    public function run(): array
    {
        $checked = 0;
        $created = 0;

        // Iterate cursor to keep memory usage small
        $query = BranchProduct::where('reorder_level', '>', 0)
            ->whereColumn('stock_quantity', '<=', 'reorder_level')
            // only select the minimal columns we need
            ->select(['id', 'branch_id', 'product_id', 'stock_quantity', 'reorder_level']);

        foreach ($query->cursor() as $bp) {
            $checked++;

            // process each item in its own transaction
            DB::transaction(function () use ($bp, &$created) {
                // Create a stock log entry if none exists in last 24 hours for this request
                $recent = StockLog::where('branch_id', $bp->branch_id)
                    ->where('product_id', $bp->product_id)
                    ->where('action', 'reorder_requested')
                    ->where('created_at', '>=', now()->subDay())
                    ->exists();

                if (!$recent) {
                    StockLog::create([
                        'branch_id' => $bp->branch_id,
                        'product_id' => $bp->product_id,
                        'action' => 'reorder_requested',
                        'quantity' => $bp->reorder_level - $bp->stock_quantity,
                        'note' => 'Auto-generated reorder request',
                    ]);
                }

                // Create a pending stock transfer if none exists (from central warehouse)
                $existsTransfer = StockTransfer::where('to_branch_id', $bp->branch_id)
                    ->where('product_id', $bp->product_id)
                    ->where('status', 'pending')
                    ->exists();

                if (!$existsTransfer) {
                    $fromBranch = env('REORDER_SOURCE_BRANCH_ID') ?: $bp->branch_id;

                    StockTransfer::create([
                        'from_branch_id' => $fromBranch,
                        'to_branch_id' => $bp->branch_id,
                        'product_id' => $bp->product_id,
                        'quantity' => max(0, $bp->reorder_level - $bp->stock_quantity),
                        'status' => 'pending',
                    ]);

                    $created++;
                }
            });
        }

        return ['checked' => $checked, 'requests_created' => $created];
    }

    /**
     * Lightweight check for a single branch/product. Intended to be called on stock adjustments.
     * Returns true if a reorder request was created.
     */
    public function checkItem(int $branchId, int $productId): bool
    {
        $bp = BranchProduct::with(['branch', 'product'])
            ->where('branch_id', $branchId)
            ->where('product_id', $productId)
            ->first();

        if (!$bp) return false;

        if ($bp->reorder_level <= 0) return false;

        if ($bp->stock_quantity > $bp->reorder_level) return false;

        return DB::transaction(function () use ($bp) {
            $recent = StockLog::where('branch_id', $bp->branch_id)
                ->where('product_id', $bp->product_id)
                ->where('action', 'reorder_requested')
                ->where('created_at', '>=', now()->subDay())
                ->exists();

            if (!$recent) {
                StockLog::create([
                    'branch_id' => $bp->branch_id,
                    'product_id' => $bp->product_id,
                    'action' => 'reorder_requested',
                    'quantity' => $bp->reorder_level - $bp->stock_quantity,
                    'note' => 'Auto-generated reorder request',
                ]);
            }

            $existsTransfer = \App\Models\StockTransfer::where('to_branch_id', $bp->branch_id)
                ->where('product_id', $bp->product_id)
                ->where('status', 'pending')
                ->exists();

            $requestCreated = false;

            if (!$existsTransfer) {
                $fromBranch = env('REORDER_SOURCE_BRANCH_ID') ?: $bp->branch_id;

                \App\Models\StockTransfer::create([
                    'from_branch_id' => $fromBranch,
                    'to_branch_id' => $bp->branch_id,
                    'product_id' => $bp->product_id,
                    'quantity' => max(0, $bp->reorder_level - $bp->stock_quantity),
                    'status' => 'pending',
                ]);

                $requestCreated = true;
            }

            // Send notification to branch manager(s)
            $this->notifyBranchManager($bp);

            return $requestCreated;
        });
    }

    /**
     * Send low stock notification to the branch manager
     */
    private function notifyBranchManager(BranchProduct $branchProduct): void
    {
        try {
            logger()->info('Low stock detected', [
                'product_id' => $branchProduct->product_id,
                'branch_id' => $branchProduct->branch_id,
                'stock_quantity' => $branchProduct->stock_quantity,
                'reorder_level' => $branchProduct->reorder_level,
            ]);

            // Find all managers for this branch
            $managers = User::where('role', 'manager')
                ->where('branch_id', $branchProduct->branch_id)
                ->get();

            logger()->info('Managers found for branch', [
                'branch_id' => $branchProduct->branch_id,
                'manager_count' => $managers->count(),
            ]);

            // If no branch-specific manager, notify business admin users
            if ($managers->isEmpty()) {
                $managers = User::where('role', 'business_admin')
                    ->where('business_id', $branchProduct->branch->business_id)
                    ->get();
                logger()->info('No branch managers, using business admins', [
                    'business_admin_count' => $managers->count(),
                ]);
            }

            // Send notification to each manager
            foreach ($managers as $manager) {
                logger()->info('Sending notification to user', [
                    'user_id' => $manager->id,
                    'user_name' => $manager->name,
                    'user_email' => $manager->email,
                ]);
                $manager->notify(new LowStockNotification($branchProduct));
            }

            logger()->info('Notifications sent successfully');
        } catch (\Throwable $e) {
            logger()->error('Failed to send low stock notification: ' . $e->getMessage(), [
                'exception' => $e->getTraceAsString()
            ]);
        }
    }
}
