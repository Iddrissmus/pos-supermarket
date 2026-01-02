<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BusinessType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($businessType) {
            if (empty($businessType->slug)) {
                $businessType->slug = Str::slug($businessType->name);
            }
        });

        static::updating(function ($businessType) {
            if ($businessType->isDirty('name') && !$businessType->isDirty('slug')) {
                $businessType->slug = Str::slug($businessType->name);
            }
        });
    }

    public function businesses()
    {
        return $this->hasMany(Business::class);
    }
}
