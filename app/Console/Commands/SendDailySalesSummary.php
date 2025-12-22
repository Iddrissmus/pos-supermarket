<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Branch;
use App\Models\Sale;
use App\Models\User;
use App\Notifications\DailySalesSummaryNotification;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SendDailySalesSummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sales:daily-summary {--date= : Date to generate summary for (Y-m-d format, defaults to yesterday)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily sales summary notifications to managers and business admins for each branch.';

    public function handle()
    {
        // Get date (default to yesterday)
        $dateInput = $this->option('date');
        $date = $dateInput ? Carbon::parse($dateInput) : Carbon::yesterday();
        
        $this->info("Generating daily sales summary for {$date->format('Y-m-d')}...");

        $branchesProcessed = 0;
        $notificationsSent = 0;

        // Get all branches (branches table doesn't have status column)
        $branches = Branch::all();

        foreach ($branches as $branch) {
            // Get sales for this branch on the specified date
            $sales = Sale::where('branch_id', $branch->id)
                ->whereDate('created_at', $date->format('Y-m-d'))
                ->with('items.product')
                ->get();

            $totalSales = $sales->count();
            $totalRevenue = $sales->sum('total');

            // Skip if no sales
            if ($totalSales === 0) {
                continue;
            }

            // Get top 5 selling products
            $topProducts = DB::table('sale_items')
                ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->where('sales.branch_id', $branch->id)
                ->whereDate('sales.created_at', $date->format('Y-m-d'))
                ->select('products.name', DB::raw('SUM(sale_items.quantity) as total_quantity'), DB::raw('SUM(sale_items.total) as total_revenue'))
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('total_quantity')
                ->limit(5)
                ->get()
                ->map(function ($item) {
                    return [
                        'name' => $item->name,
                        'quantity' => $item->total_quantity,
                        'revenue' => $item->total_revenue,
                    ];
                })
                ->toArray();

            // Notify branch managers
            $managers = User::where('role', 'manager')
                ->where('branch_id', $branch->id)
                ->get();

            foreach ($managers as $manager) {
                $manager->notify(new DailySalesSummaryNotification(
                    $branch->id,
                    $branch->name,
                    $date->format('Y-m-d'),
                    $totalSales,
                    $totalRevenue,
                    $topProducts
                ));
                $notificationsSent++;
            }

            // Notify business admins (fallback if no managers)
            if ($managers->isEmpty()) {
                $businessAdmins = User::where('role', 'business_admin')
                    ->where('business_id', $branch->business_id)
                    ->get();

                foreach ($businessAdmins as $admin) {
                    $admin->notify(new DailySalesSummaryNotification(
                        $branch->id,
                        $branch->name,
                        $date->format('Y-m-d'),
                        $totalSales,
                        $totalRevenue,
                        $topProducts
                    ));
                    $notificationsSent++;
                }
            }

            $branchesProcessed++;
        }

        $this->info("Processed {$branchesProcessed} branches. Sent {$notificationsSent} notifications.");
        return 0;
    }
}

