<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchProduct extends Model
{
    protected $table = 'branch_products';
    protected $fillable = ['branch_id', 'product_id', 'stock_quantity', 'reorder_level', 'price', 'cost_price'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Adjust stock by a delta (positive to increase, negative to decrease).
     * Creates a StockLog entry and triggers an immediate reorder check for this item.
     *
     * @param int $delta
     * @param string $action
     * @param string|null $note
     * @return void
     */
    public function adjustStock(int $delta, string $action = 'adjusted', ?string $note = null): void
    {
        $this->stock_quantity = max(0, $this->stock_quantity + $delta);
        $this->save();

        // Create stock log
        \App\Models\StockLog::create([
            'branch_id' => $this->branch_id,
            'product_id' => $this->product_id,
            'action' => $action,
            'quantity' => $delta,
            'note' => $note,
        ]);

        // Run immediate reorder check for this branch/product
        try {
            (new \App\Services\StockReorderService())->checkItem($this->branch_id, $this->product_id);
        } catch (\Throwable $e) {
            logger()->error('StockReorderService failed: ' . $e->getMessage());
        }
    }
}
