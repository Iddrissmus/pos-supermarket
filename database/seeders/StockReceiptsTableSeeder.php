<?php

namespace Database\Seeders;

use App\Models\StockReceipt;
use App\Models\StockReceiptItem;
use App\Models\Branch;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class StockReceiptsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branches = Branch::all();
        $suppliers = Supplier::all();
        $businessAdmin = User::where('role', 'business_admin')->first();

        if ($branches->isEmpty() || $suppliers->isEmpty() || !$businessAdmin) {
            $this->command->warn('⚠ Missing required data, skipping stock receipts seeding');
            return;
        }

        $receiptsCount = 0;
        $itemsCount = 0;

        // Create 2-3 stock receipts per branch
        foreach ($branches as $branch) {
            $numberOfReceipts = rand(2, 3);

            for ($i = 0; $i < $numberOfReceipts; $i++) {
                $supplier = $suppliers->random();
                $receiptNumber = 'SR-' . $branch->id . '-' . date('Y') . '-' . str_pad($receiptsCount + 1, 4, '0', STR_PAD_LEFT);

                // Get 3-6 random products
                $products = Product::inRandomOrder()->limit(rand(3, 6))->get();

                $totalAmount = 0;
                $receiptItems = [];

                foreach ($products as $product) {
                    $quantity = rand(10, 50);
                    // Use cost_price from branch_products or default
                    $unitCost = rand(10, 100);
                    $lineTotal = $quantity * $unitCost;
                    $totalAmount += $lineTotal;

                    $receiptItems[] = [
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_cost' => $unitCost,
                        'line_total' => $lineTotal,
                    ];
                }

                // Create stock receipt
                $stockReceipt = StockReceipt::create([
                    'branch_id' => $branch->id,
                    'supplier_id' => $supplier->id,
                    'receipt_number' => $receiptNumber,
                    'type' => 'purchase',
                    'received_at' => now()->subDays(rand(1, 14)), // Random date in last 2 weeks
                    'notes' => 'Stock delivery from ' . $supplier->name,
                    'created_by' => $businessAdmin->id,
                    'total_amount' => $totalAmount,
                    'created_at' => now()->subDays(rand(1, 14)),
                    'updated_at' => now()->subDays(rand(1, 14)),
                ]);

                // Create stock receipt items
                foreach ($receiptItems as $item) {
                    StockReceiptItem::create([
                        'stock_receipt_id' => $stockReceipt->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_cost' => $item['unit_cost'],
                        'line_total' => $item['line_total'],
                        'notes' => null,
                        'created_at' => $stockReceipt->created_at,
                        'updated_at' => $stockReceipt->updated_at,
                    ]);
                    $itemsCount++;
                }

                $receiptsCount++;
            }
        }

        $this->command->info("✓ Created {$receiptsCount} stock receipts with {$itemsCount} items");
    }
}
