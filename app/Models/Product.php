<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'image',
        'business_id',
        'category_id',
        'primary_supplier_id',
        'is_local_supplier_product',
        'added_by',
        'barcode',
        'qr_code_path',
        'quantity_per_box',
        'total_boxes',
        'total_units',
        'assigned_units',
    ];

    protected $casts = [
        'is_local_supplier_product' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($product) {
            // Auto-generate barcode if not provided
            if (empty($product->barcode)) {
                $barcodeService = new \App\Services\BarcodeService();
                $product->barcode = $barcodeService->generateBarcodeNumber();
            }
        });
        
        // Generate QR code after product is created (needs product ID)
        static::created(function ($product) {
            if (empty($product->qr_code_path)) {
                $barcodeService = new \App\Services\BarcodeService();
                $qrPath = $barcodeService->generateQRCode($product);
                $product->update(['qr_code_path' => $qrPath]);
            }
        });
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

    public function getQrCodeUrlAttribute()
    {
        $path = 'qrcodes/product_' . $this->id . '.svg';
        
        if (file_exists(storage_path('app/public/' . $path))) {
            return asset('storage/' . $path);
        }
        
        // Return a default placeholder or generate on-the-fly
        return null;
    }

    /**
     * Get available units for assignment (computed attribute)
     */
    public function getAvailableUnitsAttribute()
    {
        return $this->total_units - $this->assigned_units;
    }

    /**
     * Check if product has enough units available for assignment
     */
    public function hasAvailableUnits($requestedUnits)
    {
        return $this->getAvailableUnitsAttribute() >= $requestedUnits;
    }

    /**
     * Assign units to a branch (deduct from available)
     */
    public function assignUnits($units)
    {
        if (!$this->hasAvailableUnits($units)) {
            throw new \Exception("Not enough units available. Available: {$this->getAvailableUnitsAttribute()}, Requested: {$units}");
        }

        $this->assigned_units += $units;
        $this->save();
    }

    /**
     * Unassign units from a branch (return to available)
     */
    public function unassignUnits($units)
    {
        $this->assigned_units = max(0, $this->assigned_units - $units);
        $this->save();
    }
}
