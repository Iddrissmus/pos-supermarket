<?php

namespace App\Notifications;

use App\Models\BranchProduct;
use App\Models\Product;
use App\Models\Branch;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    public $branchProduct;
    public $product;
    public $branch;
    public $quantityNeeded;

    /**
     * Create a new notification instance.
     */
    public function __construct(BranchProduct $branchProduct)
    {
        $this->branchProduct = $branchProduct;
        $this->product = $branchProduct->product;
        $this->branch = $branchProduct->branch;
        $this->quantityNeeded = max(0, $branchProduct->reorder_level - $branchProduct->stock_quantity);
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Low Stock Alert: ' . $this->product->name)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Stock is running low at ' . $this->branch->name . '.')
            ->line('**Product:** ' . $this->product->name)
            ->line('**Current Stock:** ' . $this->branchProduct->stock_quantity . ' units')
            ->line('**Reorder Level:** ' . $this->branchProduct->reorder_level . ' units')
            ->line('**Quantity Needed:** ' . $this->quantityNeeded . ' units')
            ->action('View Inventory', url('/products'))
            ->line('Please consider restocking this product soon to avoid running out of stock.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'low_stock',
            'title' => 'Low Stock Alert',
            'message' => "Stock is low for {$this->product->name} at {$this->branch->name}",
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'product_sku' => $this->product->sku,
            'branch_id' => $this->branch->id,
            'branch_name' => $this->branch->name,
            'current_stock' => $this->branchProduct->stock_quantity,
            'reorder_level' => $this->branchProduct->reorder_level,
            'quantity_needed' => $this->quantityNeeded,
            'urgency' => $this->branchProduct->stock_quantity == 0 ? 'critical' : 'warning',
            'icon' => 'fa-exclamation-triangle',
            'color' => $this->branchProduct->stock_quantity == 0 ? 'red' : 'yellow',
            'action_url' => route('layouts.product', ['branch_id' => $this->branch->id]),
        ];
    }
}
