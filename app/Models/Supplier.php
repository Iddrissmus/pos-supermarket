<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'type',
        'address',
        'phone',
        'email',
        'contact_person',
        'notes',
        'is_active',
        'is_central'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_central' => 'boolean',
    ];

    public function stockReceipts(): HasMany
    {
        return $this->hasMany(StockReceipt::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeCentral($query)
    {
        return $query->where('is_central', true);
    }

    public function scopeLocal($query)
    {
        return $query->where('is_central', false);
    }
}
