<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SystemTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'business_id',
        'amount',
        'currency',
        'reference',
        'channel',
        'source_type',
        'source_id',
        'status',
        'payout_status',
    ];
    
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
    
    public function source()
    {
        return $this->morphTo();
    }
    
    // Scopes for easy filtering
    public function scopePendingPayout($query)
    {
        return $query->where('payout_status', 'pending');
    }
    
    public function scopeCollectedByBusiness($query)
    {
        return $query->where('payout_status', 'collected_by_business');
    }
}
