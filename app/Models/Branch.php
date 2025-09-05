<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = ['business_id', 'name', 'location', 'contact'];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function products() 
    {
        return $this->belongsToMany(Product::class)
            ->using(BranchProduct::class)
            ->withPivot(['price', 'stock', 'reorder_level'])
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
