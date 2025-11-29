<?php

namespace App\Notifications;

use App\Models\StockReceipt;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StockReceivedNotification extends Notification
{
    use Queueable;

    public $stockReceipt;
    public $itemCount;
    public $totalValue;

    public function __construct(StockReceipt $stockReceipt)
    {
        $this->stockReceipt = $stockReceipt->load(['branch', 'items.product']);
        $this->itemCount = $stockReceipt->items->count();
        $this->totalValue = $stockReceipt->items->sum(function($item) {
            return $item->quantity * $item->unit_cost;
        });
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'stock_received',
            'title' => 'New Stock Received',
            'message' => "{$this->itemCount} products received at {$this->stockReceipt->branch->name}",
            'stock_receipt_id' => $this->stockReceipt->id,
            'branch_id' => $this->stockReceipt->branch_id,
            'branch_name' => $this->stockReceipt->branch->name,
            'item_count' => $this->itemCount,
            'total_value' => number_format($this->totalValue, 2),
            'received_by' => $this->stockReceipt->received_by,
            'icon' => 'fa-box',
            'color' => 'green',
            'urgency' => 'normal',
            'action_url' => route('stock-receipts.show', $this->stockReceipt->id),
        ];
    }
}