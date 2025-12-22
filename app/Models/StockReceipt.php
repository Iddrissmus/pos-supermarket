<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class StockReceipt extends Model
{
    protected $fillable = [
        'branch_id',
        'supplier_id',
        'receipt_number',
        'type',
        'received_at',
        'notes',
        'created_by',
        'total_amount'
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'total_amount' => 'decimal:2'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($receipt) {
            if (empty($receipt->receipt_number)) {
                $receipt->receipt_number = static::generateReceiptNumber();
            }
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockReceiptItem::class);
    }

    public static function generateReceiptNumber(): string
    {
        $prefix = 'SR';
        $date = now()->format('Ymd');
        $random = Str::upper(Str::random(4));
        return $prefix . $date . $random;
    }

    public function calculateTotal(): void
    {
        $this->total_amount = $this->items()->sum('line_total');
        $this->save();
    }
}
