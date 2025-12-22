<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\BranchProduct;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get low stock items (stock_quantity < reorder_level)
        $lowStockItems = BranchProduct::whereRaw('stock_quantity < reorder_level')
            ->with(['product', 'branch'])
            ->get();

        // Get business admins and managers to notify
        $businessAdmins = User::where('role', 'business_admin')->get();
        $managers = User::where('role', 'manager')->get();
        $usersToNotify = $businessAdmins->merge($managers);

        $notificationCount = 0;

        foreach ($lowStockItems as $branchProduct) {
            foreach ($usersToNotify as $user) {
                DB::table('notifications')->insert([
                    'id' => Str::uuid(),
                    'type' => 'App\\Notifications\\LowStockNotification',
                    'notifiable_type' => 'App\\Models\\User',
                    'notifiable_id' => $user->id,
                    'data' => json_encode([
                        'type' => 'low_stock',
                        'title' => 'Low Stock Alert',
                        'message' => "Low stock alert: {$branchProduct->product->name} at {$branchProduct->branch->name}",
                        'icon' => 'fa-exclamation-triangle',
                        'color' => 'red',
                        'urgency' => $branchProduct->stock_quantity < 5 ? 'critical' : 'normal',
                        'product_id' => $branchProduct->product_id,
                        'product_name' => $branchProduct->product->name,
                        'branch_id' => $branchProduct->branch_id,
                        'branch_name' => $branchProduct->branch->name,
                        'current_stock' => $branchProduct->stock_quantity,
                        'reorder_level' => $branchProduct->reorder_level,
                    ]),
                    'read_at' => null, // Unread notifications
                    'created_at' => now()->subHours(rand(1, 48)), // Random time in last 2 days
                    'updated_at' => now()->subHours(rand(1, 48)),
                ]);
                $notificationCount++;
            }
        }

        $this->command->info("✓ Created {$notificationCount} low stock notifications");
    }
}
