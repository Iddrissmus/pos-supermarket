<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\TaxRate;
use Illuminate\Http\Request;

class TaxRateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $taxRates = TaxRate::orderBy('name')->get();
        return view('superadmin.tax-rates.index', compact('taxRates'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:tax_rates,name',
            'rate' => 'required|numeric|min:0',
            'type' => 'required|in:percentage,fixed',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        TaxRate::create($validated);

        return redirect()->route('superadmin.tax-rates.index')
            ->with('success', 'Tax rate created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TaxRate $taxRate)
    {
        return view('superadmin.tax-rates.edit', compact('taxRate'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TaxRate $taxRate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:tax_rates,name,' . $taxRate->id,
            'rate' => 'required|numeric|min:0',
            'type' => 'required|in:percentage,fixed',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $taxRate->update($validated);

        return redirect()->route('superadmin.tax-rates.index')
            ->with('success', 'Tax rate updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TaxRate $taxRate)
    {
        $taxRate->delete();
        return redirect()->route('superadmin.tax-rates.index')
            ->with('success', 'Tax rate deleted successfully.');
    }
}
