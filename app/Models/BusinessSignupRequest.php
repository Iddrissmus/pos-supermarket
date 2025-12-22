<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessSignupRequest extends Model
{
    protected $fillable = [
        'business_name',
        'logo',
        'owner_name',
        'owner_email',
        'owner_phone',
        'branch_name',
        'address',
        'region',
        'branch_contact',
        'latitude',
        'longitude',
        'status',
        'approval_note',
        'approved_by',
    ];

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}





