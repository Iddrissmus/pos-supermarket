<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    protected $fillable = ['name', 'logo', 'status', 'business_admin_id'];

    
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
}
