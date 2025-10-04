<?php

namespace App\Services;

use App\Models\BranchProduct;
use App\Models\StockReceipt;
use App\Models\StockReceiptItem;
use App\Models\StockLog;
use Illuminate\Support\Facades\DB;

class ReceiveStockService
{
    /**
     * Receive stock with weighted average cost calculation
     */
    public function receiveStock(array $data): StockReceipt
    {
        return DB::transaction(function () use ($data) {
            // Create the stock receipt
            $receipt = StockReceipt::create([
                'branch_id' => $data['branch_id'],
                'supplier_id' => $data['supplier_id'],
                'receipt_number' => $data['receipt_number'] ?? $this->generateReceiptNumber(),
                'received_at' => $data['received_date'] ?? now(),
                'total_amount' => 0, // Will be calculated
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $totalAmount = 0;

            // Process each item
            foreach ($data['items'] as $itemData) {
                $receiptItem = StockReceiptItem::create([
                    'stock_receipt_id' => $receipt->id,
                    'product_id' => $itemData['product_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_cost' => $itemData['unit_cost'],
                    'line_total' => $itemData['quantity'] * $itemData['unit_cost'],
                ]);

                $totalAmount += $receiptItem->line_total;

                // Update branch product with weighted average cost
                $this->updateBranchProductWithWeightedAverage(
                    $data['branch_id'],
                    $itemData['product_id'],
                    $itemData['quantity'],
                    $itemData['unit_cost']
                );

                // Log the stock movement
                StockLog::create([
                    'branch_id' => $data['branch_id'],
                    'product_id' => $itemData['product_id'],
                    'action' => 'received',
                    'quantity' => $itemData['quantity'],
                    'note' => "Stock received via receipt #{$receipt->receipt_number}",
                ]);
            }

            // Update receipt total
            $receipt->update(['total_amount' => $totalAmount]);

            return $receipt->load(['items.product', 'supplier', 'branch']);
        });
    }

    /**
     * Update branch product with weighted average cost calculation
     */
    private function updateBranchProductWithWeightedAverage(
        int $branchId,
        int $productId,
        int $newQuantity,
        float $newUnitCost
    ): void {
        $branchProduct = BranchProduct::firstOrCreate(
            [
                'branch_id' => $branchId,
                'product_id' => $productId,
            ],
            [
                'stock_quantity' => 0,
                'cost_price' => 0,
                'selling_price' => 0,
            ]
        );

        // Calculate weighted average cost
        $currentStock = $branchProduct->stock_quantity;
        $currentCost = $branchProduct->cost_price;
        
        // Current total value
        $currentValue = $currentStock * $currentCost;
        
        // New stock value
        $newValue = $newQuantity * $newUnitCost;
        
        // Calculate new weighted average
        $totalQuantity = $currentStock + $newQuantity;
        $totalValue = $currentValue + $newValue;
        
        $weightedAverageCost = $totalQuantity > 0 ? $totalValue / $totalQuantity : 0;

        // Update the branch product
        $branchProduct->update([
            'stock_quantity' => $totalQuantity,
            'cost_price' => round($weightedAverageCost, 4), // Keep 4 decimal places for precision
        ]);
    }

    /**
     * Generate a unique receipt number
     */
    private function generateReceiptNumber(): string
    {
        $prefix = 'REC-' . date('Ymd') . '-';
        $lastReceipt = StockReceipt::where('receipt_number', 'like', $prefix . '%')
            ->orderBy('receipt_number', 'desc')
            ->first();

        if ($lastReceipt) {
            $lastNumber = (int) substr($lastReceipt->receipt_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get current cost for a product at a branch (for COGS calculation)
     */
    public function getCurrentCostPrice(int $branchId, int $productId): float
    {
        $branchProduct = BranchProduct::where('branch_id', $branchId)
            ->where('product_id', $productId)
            ->first();

        return $branchProduct ? $branchProduct->cost_price : 0;
    }

    /**
     * Process sale and reduce stock with COGS tracking
     */
    public function processSale(int $branchId, int $productId, int $quantity): array
    {
        $branchProduct = BranchProduct::where('branch_id', $branchId)
            ->where('product_id', $productId)
            ->first();

        if (!$branchProduct || $branchProduct->stock_quantity < $quantity) {
            throw new \Exception('Insufficient stock for this product');
        }

        $costPrice = $branchProduct->cost_price;
        $totalCost = $quantity * $costPrice;

        // Reduce stock
        $branchProduct->decrement('stock_quantity', $quantity);

        // Log the stock movement
        StockLog::create([
            'branch_id' => $branchId,
            'product_id' => $productId,
            'action' => 'sold',
            'quantity' => -$quantity, // Negative for outbound
            'note' => "Stock sold",
        ]);

        return [
            'unit_cost' => $costPrice,
            'total_cost' => $totalCost,
        ];
    }
}