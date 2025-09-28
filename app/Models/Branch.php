<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = ['business_id', 'manager_id', 'name', 'contact', 'latitude', 'longitude', 'address'];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function products() 
    {
        return $this->belongsToMany(Product::class, 'branch_products')
            ->withPivot(['price', 'cost_price', 'stock_quantity', 'reorder_level'])
            ->withTimestamps();
    }

    public function branchProducts()
    {
        return $this->hasMany(BranchProduct::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function stockLogs()
    {
        return $this->hasMany(StockLog::class);
    }
}
