<?php

namespace App\Notifications;

use App\Models\StockTransfer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StockTransferCompletedNotification extends Notification
{
    use Queueable;

    public $transfer;
    public $isRecipient;

    public function __construct(StockTransfer $transfer, $isRecipient = false)
    {
        $this->transfer = $transfer->load(['fromBranch', 'toBranch', 'product']);
        $this->isRecipient = $isRecipient;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $message = $this->isRecipient 
            ? "Received {$this->transfer->quantity} units of {$this->transfer->product->name} from {$this->transfer->fromBranch->name}"
            : "Transferred {$this->transfer->quantity} units of {$this->transfer->product->name} to {$this->transfer->toBranch->name}";
            
        return [
            'type' => 'stock_transfer',
            'title' => $this->isRecipient ? 'Stock Transfer Received' : 'Stock Transfer Completed',
            'message' => $message,
            'transfer_id' => $this->transfer->id,
            'product_id' => $this->transfer->product_id,
            'product_name' => $this->transfer->product->name,
            'quantity' => $this->transfer->quantity,
            'from_branch' => $this->transfer->fromBranch->name,
            'to_branch' => $this->transfer->toBranch->name,
            'is_recipient' => $this->isRecipient,
            'icon' => 'fa-exchange-alt',
            'color' => 'blue',
            'urgency' => 'normal',
            'action_url' => route('products.index'),
        ];
    }
}