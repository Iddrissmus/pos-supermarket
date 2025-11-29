<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DailySalesSummaryNotification extends Notification
{
    use Queueable;

    public $branchId;
    public $branchName;
    public $date;
    public $totalSales;
    public $totalRevenue;
    public $topProducts;

    public function __construct($branchId, $branchName, $date, $totalSales, $totalRevenue, $topProducts)
    {
        $this->branchId = $branchId;
        $this->branchName = $branchName;
        $this->date = $date;
        $this->totalSales = $totalSales;
        $this->totalRevenue = $totalRevenue;
        $this->topProducts = $topProducts;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $performance = $this->totalRevenue > 10000 ? 'excellent' : ($this->totalRevenue > 5000 ? 'good' : 'normal');
        
        return [
            'type' => 'daily_sales_summary',
            'title' => 'Daily Sales Summary',
            'message' => "{$this->branchName}: GHS " . number_format($this->totalRevenue, 2) . " revenue from {$this->totalSales} sales",
            'branch_id' => $this->branchId,
            'branch_name' => $this->branchName,
            'date' => $this->date,
            'total_sales' => $this->totalSales,
            'total_revenue' => number_format($this->totalRevenue, 2),
            'performance' => $performance,
            'top_products' => $this->topProducts,
            'icon' => 'fa-chart-line',
            'color' => $performance === 'excellent' ? 'green' : ($performance === 'good' ? 'blue' : 'gray'),
            'urgency' => 'normal',
            'action_url' => route('sales.index', ['branch_id' => $this->branchId, 'date' => $this->date]),
        ];
    }
}