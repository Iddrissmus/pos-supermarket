<?php

namespace App\Notifications;

use App\Models\Sale;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SaleCompletedNotification extends Notification
{
    use Queueable;

    public $sale;

    public function __construct(Sale $sale)
    {
        $this->sale = $sale->load(['branch', 'cashier']);
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'sale_completed',
            'title' => 'New Sale',
            'message' => "New sale of GHS " . number_format($this->sale->total, 2) . " by " . $this->sale->cashier->name,
            'sale_id' => $this->sale->id,
            'branch_id' => $this->sale->branch_id,
            'branch_name' => $this->sale->branch->name,
            'total_amount' => number_format($this->sale->total, 2),
            'cashier_name' => $this->sale->cashier->name,
            'payment_method' => $this->sale->payment_method,
            'icon' => 'fa-receipt',
            'color' => 'blue',
            'urgency' => 'normal',
            'action_url' => route('sales.show', $this->sale->id),
        ];
    }
}
