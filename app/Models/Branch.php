<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = ['business_id', 'manager_id', 'name', 'contact', 'latitude', 'longitude', 'address', 'region', 'is_main'];

    public static $ghanaRegions = [
        'Greater Accra',
        'Ashanti',
        'Western',
        'Eastern',
        'Central',
        'Northern',
        'Upper East',
        'Upper West',
        'Volta',
        'Brong-Ahafo',
        'Western North',
        'Bono East',
        'Ahafo',
        'Savannah',
        'North East',
        'Oti',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function manager()
    {
        return $this->hasOne(User::class)->where('role', 'manager');
    }

    public function cashier()
    {
        return $this->hasOne(User::class)->where('role', 'cashier');
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

    public function getDisplayLabelAttribute(): string
    {
        $this->loadMissing('business:id,name');

        if ($this->business && $this->business->name) {
            return sprintf('%s — %s', $this->name, $this->business->name);
        }

        return $this->name ?? 'Unnamed branch';
    }
}
