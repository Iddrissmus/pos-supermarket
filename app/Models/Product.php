<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'sku',
        'image',
        'business_id',
        'category_id',
        'primary_supplier_id',
        'is_local_supplier_product',
        'added_by',
    ];

    protected $casts = [
        'is_local_supplier_product' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($product) {
            if (empty($product->sku)) {
                $product->sku = self::generateSKU($product->name);
                
                // Ensure SKU is unique
                while (self::where('sku', $product->sku)->exists()) {
                    $product->sku = self::generateSKU($product->name);
                }
            }
        });
    }

    public static function generateSKU($name)
    {
        // Convert name to uppercase and remove spaces
        $namePart = strtoupper(str_replace(' ', '', $name));
        // Take first 3 characters
        $namePart = substr($namePart, 0, 3);
        // Add random numbers
        $randomPart = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        $sku = $namePart . $randomPart;
        
        // Check if SKU exists and regenerate if needed
        while (self::where('sku', $sku)->exists()) {
            $randomPart = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $sku = $namePart . $randomPart;
        }
        
        return $sku;
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function primarySupplier()
    {
        return $this->belongsTo(Supplier::class, 'primary_supplier_id');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function branchProducts()
    {
        return $this->hasMany(BranchProduct::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function branches() 
    {
        return $this->belongsToMany(Branch::class, 'branch_products')
            ->withPivot(['price', 'cost_price', 'stock_quantity', 'reorder_level'])
            ->withTimestamps();
    }
}
