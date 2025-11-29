<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    protected $fillable = [
        'from_branch_id', 
        'to_branch_id', 
        'product_id', 
        'quantity', 
        'quantity_of_boxes',
        'quantity_per_box',
        'status',
        'reason',
        'requested_by',
        'requested_at',
        'approved_by',
        'approved_at',
        'approval_note',
        'cancelled_at',
        // Pricing fields
        'price',
        'cost_price',
        'price_per_kilo',
        'price_per_box',
        'weight_unit',
        'price_per_unit_weight',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'price_per_kilo' => 'decimal:2',
        'price_per_box' => 'decimal:2',
        'price_per_unit_weight' => 'decimal:2',
    ];

    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function requestedByUser()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
