<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = ['branch_id', 'customer_id', 'cashier_id','subtotal','tax_rate','tax_amount','tax_components', 'total', 'payment_method'];

    // Default tax rate - can be configured per business needs
    const DEFAULT_TAX_RATE = 12.5; // 12.5% tax rate

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    protected $casts = [
        'tax_components' => 'array',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Calculate all totals including tax automatically
     */
    /**
     * Calculate all totals including tax automatically
     */
    public function calculateTotals()
    {
        $itemsTotal = $this->items->sum('total');
        $this->subtotal = $itemsTotal;

        // Fetch active tax rates
        $activeTaxes = \App\Models\TaxRate::where('is_active', true)->get();
        
        $totalTaxAmount = 0;
        $totalTaxRate = 0; // For legacy reference only
        $components = [];

        foreach ($activeTaxes as $tax) {
            $amount = 0;
            if ($tax->type === 'percentage') {
                $amount = ($this->subtotal * $tax->rate) / 100;
                $totalTaxRate += $tax->rate;
            } else {
                $amount = $tax->rate; // Fixed amount
            }
            
            $totalTaxAmount += $amount;
            
            $components[] = [
                'name' => $tax->name,
                'rate' => $tax->rate,
                'type' => $tax->type,
                'amount' => $amount
            ];
        }

        $this->tax_rate = $totalTaxRate; // Approximate percentage for display if needed
        $this->tax_amount = $totalTaxAmount;
        $this->total = $this->subtotal + $this->tax_amount;
        $this->tax_components = $components;

        $this->save(); 
        return $this;
    }

    /**
     * Add additional tax component (for special cases)
     */
    public function addTaxComponent($name, $rate, $amount)
    {
        $components = $this->tax_components ?? [];
        $components[] = [
            'name' => $name,
            'rate' => $rate,
            'amount' => $amount
        ];
        $this->tax_components = $components;
        
        // Recalculate total tax amount from all components
        $totalTaxAmount = 0;
        foreach ($components as $component) {
            $totalTaxAmount += $component['amount'];
        }
        
        $this->tax_amount = $totalTaxAmount;
        $this->total = $this->subtotal + $this->tax_amount;
        $this->save();
        
        return $this;
    }

    /**
     * Get tax breakdown for display
     */
    public function getTaxBreakdown()
    {
        return [
            'subtotal' => $this->subtotal,
            'tax_components' => $this->tax_components ?? [],
            'tax_amount' => $this->tax_amount,
            'total' => $this->total,
        ];
    }

    /**
     * Get margin and profit calculations
     */
    public function getProfitAnalysis()
    {
        $cogs = $this->items->sum('total_cost');
        $grossProfit = $this->subtotal - $cogs; // Profit before tax
        $netProfit = $this->total - $cogs; // Profit after tax (not applicable here, but for completeness)
        
        return [
            'subtotal' => $this->subtotal,
            'cogs' => $cogs,
            'gross_profit' => $grossProfit,
            'tax_amount' => $this->tax_amount,
            'total' => $this->total,
            'margin_percent' => $this->subtotal > 0 ? ($grossProfit / $this->subtotal) * 100 : 0,
        ];
    }

}
