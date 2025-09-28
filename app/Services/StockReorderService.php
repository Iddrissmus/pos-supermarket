<?php

namespace App\Services;

use App\Models\BranchProduct;
use App\Models\StockLog;
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
        $bp = BranchProduct::where('branch_id', $branchId)
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

            if (!$existsTransfer) {
                $fromBranch = env('REORDER_SOURCE_BRANCH_ID') ?: $bp->branch_id;

                \App\Models\StockTransfer::create([
                    'from_branch_id' => $fromBranch,
                    'to_branch_id' => $bp->branch_id,
                    'product_id' => $bp->product_id,
                    'quantity' => max(0, $bp->reorder_level - $bp->stock_quantity),
                    'status' => 'pending',
                ]);

                return true;
            }

            return false;
        });
    }
}
