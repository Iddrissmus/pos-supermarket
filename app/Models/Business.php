<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    protected $fillable = ['name', 'business_admin_id', 'logo'];

    
    public function businessAdmin()
    {
        return $this->belongsTo(User::class, 'business_admin_id');
    }
    
    // Alias for backwards compatibility
    public function owner()
    {
        return $this->businessAdmin();
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
}
