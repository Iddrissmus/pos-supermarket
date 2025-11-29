<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchRequest extends Model
{
    protected $fillable = [
        'business_id',
        'requested_by',
        'branch_name',
        'location',
        'address',
        'phone',
        'email',
        'latitude',
        'longitude',
        'status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
