<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Branch;
use App\Models\Product;
use App\Models\BranchProduct;
use App\Models\StockTransfer;
use App\Services\StockReorderService;

class StockReorderServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_transfer_when_stock_below_reorder()
    {
        $user = \App\Models\User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'role' => 'owner',
        ]);

        $business = \App\Models\Business::create(['name' => 'Test Business', 'owner_id' => $user->id]);

        $branch = Branch::create([
            'business_id' => $business->id,
            'name' => 'Main Branch',
        ]);

        $product = Product::create([
            'name' => 'Test Product',
            'business_id' => $business->id,
        ]);

        // Create pivot record
        $bp = BranchProduct::create([
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'stock_quantity' => 2,
            'reorder_level' => 5,
            'price' => 10,
            'cost_price' => 7,
        ]);

        $service = new StockReorderService();
        $result = $service->run();

        $this->assertEquals(1, $result['checked']);
        $this->assertEquals(1, $result['requests_created']);

        $this->assertDatabaseHas('stock_transfers', [
            'to_branch_id' => $branch->id,
            'product_id' => $product->id,
            'status' => 'pending',
        ]);
    }
}
