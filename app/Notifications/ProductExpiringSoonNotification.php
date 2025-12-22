<?php

namespace App\Notifications;

use App\Models\BranchProduct;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductExpiringSoonNotification extends Notification
{
    use Queueable;

    public $branchProduct;
    public $daysUntilExpiry;

    public function __construct(BranchProduct $branchProduct, $daysUntilExpiry)
    {
        $this->branchProduct = $branchProduct->load(['product', 'branch']);
        $this->daysUntilExpiry = $daysUntilExpiry;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $urgency = $this->daysUntilExpiry <= 7 ? 'critical' : 'warning';
        $color = $this->daysUntilExpiry <= 7 ? 'red' : 'orange';
        
        return [
            'type' => 'product_expiring',
            'title' => 'Product Expiring Soon',
            'message' => "{$this->branchProduct->product->name} expires in {$this->daysUntilExpiry} days at {$this->branchProduct->branch->name}",
            'product_id' => $this->branchProduct->product_id,
            'product_name' => $this->branchProduct->product->name,
            'product_sku' => $this->branchProduct->product->sku,
            'branch_id' => $this->branchProduct->branch_id,
            'branch_name' => $this->branchProduct->branch->name,
            'expiry_date' => $this->branchProduct->expiry_date,
            'days_until_expiry' => $this->daysUntilExpiry,
            'current_stock' => $this->branchProduct->stock_quantity,
            'icon' => 'fa-exclamation-triangle',
            'color' => $color,
            'urgency' => $urgency,
            'action_url' => route('layouts.product', ['branch_id' => $this->branchProduct->branch_id]),
        ];
    }
}