<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\BusinessType;
use Illuminate\Http\Request;

class BusinessTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $types = BusinessType::latest()->get();
        return view('superadmin.business-types.index', compact('types'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('superadmin.business-types.edit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:business_types,name',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        BusinessType::create($validated);

        return redirect()->route('superadmin.business-types.index')
            ->with('success', 'Business Type created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BusinessType $businessType)
    {
        return view('superadmin.business-types.edit', compact('businessType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BusinessType $businessType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:business_types,name,' . $businessType->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $businessType->update($validated);

        return redirect()->route('superadmin.business-types.index')
            ->with('success', 'Business Type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BusinessType $businessType)
    {
        // specific check if has businesses?
        if ($businessType->businesses()->count() > 0) {
            return back()->with('error', 'Cannot delete type associated with existing businesses.');
        }

        $businessType->delete();

        return back()->with('success', 'Business Type deleted successfully.');
    }
}
