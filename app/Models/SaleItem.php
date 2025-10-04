<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'sale_id', 
        'product_id', 
        'quantity', 
        'price', 
        'total',
        'unit_cost',
        'total_cost',
        'gross_margin',
        'margin_percent'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'total' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'gross_margin' => 'decimal:2',
        'margin_percent' => 'decimal:2'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($item) {
            // Calculate totals and margins
            $revenue = $item->quantity * $item->price;
            $item->total = $revenue; // Keep existing total field
            $item->total_cost = $item->quantity * ($item->unit_cost ?? 0);
            $item->gross_margin = $revenue - $item->total_cost;
            
            if ($revenue > 0) {
                $item->margin_percent = ($item->gross_margin / $revenue) * 100;
            } else {
                $item->margin_percent = 0;
            }
        });
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getRevenueAttribute(): float
    {
        return $this->quantity * $this->price;
    }
}
