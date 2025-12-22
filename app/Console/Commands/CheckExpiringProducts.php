<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BranchProduct;
use App\Models\User;
use App\Notifications\ProductExpiringSoonNotification;
use Carbon\Carbon;

class CheckExpiringProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:check-expiring {--days=30 : Number of days ahead to check for expiring products}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for products expiring soon and send notifications to managers and business admins.';

    public function handle()
    {
        $daysAhead = (int) $this->option('days');
        $expiryDate = Carbon::today()->addDays($daysAhead);
        
        $this->info("Checking for products expiring within {$daysAhead} days (before {$expiryDate->format('Y-m-d')})...");

        $productsChecked = 0;
        $notificationsSent = 0;

        // Check if expiry_date column exists in branch_products table
        $hasExpiryColumn = \Illuminate\Support\Facades\Schema::hasColumn('branch_products', 'expiry_date');
        
        if (!$hasExpiryColumn) {
            $this->warn('expiry_date column does not exist in branch_products table. Skipping expiring products check.');
            $this->info('To enable this feature, add expiry_date column to branch_products table.');
            return 0;
        }

        // Get all branch products with expiry dates
        $branchProducts = BranchProduct::whereNotNull('expiry_date')
            ->where('expiry_date', '<=', $expiryDate->format('Y-m-d'))
            ->where('expiry_date', '>', Carbon::today()->format('Y-m-d')) // Not already expired
            ->where('stock_quantity', '>', 0) // Only products with stock
            ->with(['branch', 'product'])
            ->get();

        foreach ($branchProducts as $branchProduct) {
            $productsChecked++;
            
            $expiryDateObj = Carbon::parse($branchProduct->expiry_date);
            $daysUntilExpiry = Carbon::today()->diffInDays($expiryDateObj, false);

            // Skip if already expired
            if ($daysUntilExpiry < 0) {
                continue;
            }

            // Notify branch managers
            $managers = User::where('role', 'manager')
                ->where('branch_id', $branchProduct->branch_id)
                ->get();

            foreach ($managers as $manager) {
                $manager->notify(new ProductExpiringSoonNotification($branchProduct, $daysUntilExpiry));
                $notificationsSent++;
            }

            // Notify business admins (fallback if no managers)
            if ($managers->isEmpty()) {
                $businessAdmins = User::where('role', 'business_admin')
                    ->where('business_id', $branchProduct->branch->business_id)
                    ->get();

                foreach ($businessAdmins as $admin) {
                    $admin->notify(new ProductExpiringSoonNotification($branchProduct, $daysUntilExpiry));
                    $notificationsSent++;
                }
            }
        }

        $this->info("Checked {$productsChecked} products. Sent {$notificationsSent} notifications.");
        return 0;
    }
}

