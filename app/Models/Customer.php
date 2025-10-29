<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'customer_number',
        'name',
        'company',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        'customer_type',
        'credit_limit',
        'outstanding_balance',
        'payment_terms',
        'notes',
        'is_active'
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($customer) {
            if (empty($customer->customer_number)) {
                $customer->customer_number = static::generateCustomerNumber();
            }
        });
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->company ? "{$this->name} ({$this->company})" : $this->name;
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->company ?: $this->name;
    }

    public function getFullAddressAttribute(): string
    {
        $address = [];
        if ($this->address) $address[] = $this->address;
        if ($this->city) $address[] = $this->city;
        if ($this->state) $address[] = $this->state;
        if ($this->country) $address[] = $this->country;
        if ($this->postal_code) $address[] = $this->postal_code;
        
        return implode(', ', $address);
    }

    public function getTotalPurchasesAttribute(): float
    {
        return $this->sales()->sum('total') + $this->invoices()->sum('total_amount');
    }

    public function getAvailableCreditAttribute(): float
    {
        return $this->credit_limit - $this->outstanding_balance;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('customer_type', $type);
    }

    public function scopeWithOutstandingBalance($query)
    {
        return $query->where('outstanding_balance', '>', 0);
    }

    public static function generateCustomerNumber(): string
    {
        $prefix = 'CUS';
        $date = now()->format('Ymd');
        $lastCustomer = static::whereDate('created_at', now())
            ->where('customer_number', 'like', $prefix . $date . '%')
            ->orderBy('customer_number', 'desc')
            ->first();

        if ($lastCustomer) {
            $lastNumber = (int) substr($lastCustomer->customer_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
