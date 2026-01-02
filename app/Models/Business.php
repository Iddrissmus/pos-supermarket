<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    protected $fillable = [
        'name', 
        'logo', 
        'status', 
        'business_admin_id', 
        'current_plan_id', // Replaces plan_type
        'business_type_id', // New
        'subscription_status', 
        'subscription_expires_at', 
        'max_branches'
    ];

    protected $casts = [
        'subscription_expires_at' => 'datetime',
    ];
    
    public function businessType()
    {
        return $this->belongsTo(BusinessType::class);
    }

    public function currentPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'current_plan_id');
    }

    public function businessAdmin()
    {
        // Get the business admin(s) assigned to this business
        return $this->hasMany(User::class, 'business_id')->where('role', 'business_admin');
    }
    
    // Get the first/primary business admin
    public function primaryBusinessAdmin()
    {
        return $this->hasOne(User::class, 'business_id')->where('role', 'business_admin');
    }
    
    // Alias for backwards compatibility
    public function owner()
    {
        return $this->primaryBusinessAdmin();
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
    
    // Get the first branch (default branch)
    public function firstBranch()
    {
        return $this->hasOne(Branch::class)->oldest('id');
    }

    /**
     * Get the max branches allowed.
     * If no plan is assigned, default to unlimited (999) for now.
     */
    public function getMaxBranchesAttribute($value)
    {
        if (is_null($this->current_plan_id)) {
            return 999;
        }
        return $value;
    }
}
