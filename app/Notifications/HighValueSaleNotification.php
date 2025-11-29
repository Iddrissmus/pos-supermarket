<?php

namespace App\Notifications;

use App\Models\Sale;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class HighValueSaleNotification extends Notification
{
    use Queueable;

    public $sale;
    public $itemCount;

    public function __construct(Sale $sale)
    {
        $this->sale = $sale->load(['branch', 'cashier', 'items']);
        $this->itemCount = $sale->items->count();
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'high_value_sale',
            'title' => 'High Value Sale',
            'message' => "GHS " . number_format($this->sale->total_amount, 2) . " sale completed at {$this->sale->branch->name}",
            'sale_id' => $this->sale->id,
            'branch_id' => $this->sale->branch_id,
            'branch_name' => $this->sale->branch->name,
            'total_amount' => number_format($this->sale->total_amount, 2),
            'item_count' => $this->itemCount,
            'cashier_name' => $this->sale->cashier->name,
            'payment_method' => $this->sale->payment_method,
            'icon' => 'fa-dollar-sign',
            'color' => 'purple',
            'urgency' => 'normal',
            'action_url' => route('sales.show', $this->sale->id),
        ];
    }
}