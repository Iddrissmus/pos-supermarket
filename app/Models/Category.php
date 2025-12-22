<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'business_id', 
        'name',
        'parent_id',
        'icon',
        'color',
        'description',
        'is_active',
        'display_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',    
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function subcategories()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // Get all subcategories recursively
    public function allSubcategories()
    {
        return $this->subcategories()->with('allSubcategories');
    }

    // Check if category is a parent
    public function isParent()
    {
        return $this->subcategories()->count() > 0;
    }

    // Check if category is a subcategory
    public function isSubcategory()
    {
        return !is_null($this->parent_id);
    }

    // Get full category path (Parent > Child)
    public function getFullNameAttribute()
    {
        if ($this->parent) {
            return $this->parent->name . ' > ' . $this->name;
        }
        return $this->name;
    }

    // Scope: Get only parent categories
    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }
    // Scope: Get only active categories
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope: Get categories for a specific business
    public function scopeForBusiness($query, $businessId)
    {
        return $query->where('business_id', $businessId);
    }
}
