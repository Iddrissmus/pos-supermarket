<?php

namespace Database\Seeders;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\BranchProduct;
use App\Models\User;
use Illuminate\Database\Seeder;

class SalesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get cashiers
        $cashiers = User::where('role', 'cashier')->get();

        if ($cashiers->isEmpty()) {
            $this->command->warn('⚠ No cashiers found, skipping sales seeding');
            return;
        }

        $salesCount = 0;
        $itemsCount = 0;

        foreach ($cashiers as $cashier) {
            // Create 3-5 sales per cashier
            $numberOfSales = rand(3, 5);

            for ($i = 0; $i < $numberOfSales; $i++) {
                // Get random products from the cashier's branch
                $branchProducts = BranchProduct::where('branch_id', $cashier->branch_id)
                    ->where('stock_quantity', '>', 5) // Only products with enough stock
                    ->inRandomOrder()
                    ->limit(rand(2, 4)) // 2-4 items per sale
                    ->with('product')
                    ->get();

                if ($branchProducts->isEmpty()) {
                    continue;
                }

                // Calculate sale totals
                $subtotal = 0;
                $saleItems = [];

                foreach ($branchProducts as $branchProduct) {
                    $quantity = rand(1, 3);
                    $unitPrice = (float) $branchProduct->price;
                    $unitCost = (float) $branchProduct->cost_price;
                    $lineTotal = $quantity * $unitPrice;
                    $totalCost = $quantity * $unitCost;
                    $grossMargin = $lineTotal - $totalCost;
                    $marginPercent = $totalCost > 0 ? ($grossMargin / $totalCost) * 100 : 0;
                    $subtotal += $lineTotal;

                    $saleItems[] = [
                        'product_id' => $branchProduct->product_id,
                        'quantity' => $quantity,
                        'price' => $unitPrice,
                        'unit_cost' => $unitCost,
                        'total' => $lineTotal,
                        'total_cost' => $totalCost,
                        'gross_margin' => $grossMargin,
                        'margin_percent' => round($marginPercent, 2),
                    ];
                }

                // Apply tax (12.5% VAT for Ghana)
                $taxRate = 12.5;
                $taxAmount = round($subtotal * ($taxRate / 100), 2);
                $total = $subtotal + $taxAmount;

                // Create sale
                $sale = Sale::create([
                    'branch_id' => $cashier->branch_id,
                    'cashier_id' => $cashier->id,
                    'customer_id' => null,
                    'subtotal' => $subtotal,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmount,
                    'tax_components' => json_encode(['VAT' => $taxAmount]),
                    'total' => $total,
                    'payment_method' => ['cash', 'card', 'mobile_money'][rand(0, 2)],
                    'created_at' => now()->subDays(rand(0, 7))->subHours(rand(0, 23)), // Random time in last week
                    'updated_at' => now()->subDays(rand(0, 7))->subHours(rand(0, 23)),
                ]);

                // Create sale items
                foreach ($saleItems as $item) {
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'unit_cost' => $item['unit_cost'],
                        'total' => $item['total'],
                        'total_cost' => $item['total_cost'],
                        'gross_margin' => $item['gross_margin'],
                        'margin_percent' => $item['margin_percent'],
                    ]);
                    $itemsCount++;
                }

                $salesCount++;
            }
        }

        $this->command->info("✓ Created {$salesCount} sales with {$itemsCount} items");
    }
}
