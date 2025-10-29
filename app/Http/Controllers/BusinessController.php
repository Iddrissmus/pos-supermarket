<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Business;
use Illuminate\Http\Request;


class BusinessController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'superadmin'){
            //they can see all businesses 
            $businesses = Business::with('businessAdmin')->paginate(15);
        }
        else {
            //business admin sees only their business
            $businesses = Business::with('businessAdmin')
                ->where('business_admin_id', $user->id)
                ->paginate(15);
        }
        return view('businesses.index', compact('businesses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // only superamdin can create business
        $user = auth()->user();
        if ($user->role !== 'superadmin') {
            abort(403, 'Only Super Admin can create a business.');
        }
        // Get all business admin users
        $availableAdmins = User::where('role', 'business_admin')
            ->with('managedBusiness')
            ->orderBy('name')
            ->get();
            
        return view('businesses.create', compact('availableAdmins'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'business_admin_id' => 'required|exists:users,id',
            'logo' => 'nullable|image|max:2048', // Optional logo, max size 2MB
        ]);

        // Handle logo upload if present
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
            $validated['logo'] = $logoPath;
        }

        $business = Business::create($validated);
        
        // Assign the business to the business admin
        \App\Models\User::where('id', $validated['business_admin_id'])
            ->update(['business_id' => $business->id]);
        
        return redirect()->route('businesses.index')
            ->with('success', 'Business created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = auth()->user();
        $business = Business::with('businessAdmin', 'branches')->findOrFail($id);
        // Ensure that non-superadmin users can only view their own business
        if ($user->role === 'business_admin' && $business->business_admin_id !== $user->id) {
        abort(403, 'Unauthorized access');
        }
        return view('businesses.show', compact('business'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = auth()->user();
        $business = Business::with('businessAdmin', 'branches')->findOrFail($id);
        
        // Business admin can only edit their own business
        if ($user->role === 'business_admin' && $business->business_admin_id !== $user->id) {
            abort(403, 'Unauthorized access');
        }
        
        // Only superadmin can change business admin assignment
        if ($user->role === 'superadmin') {
            $availableAdmins = User::where('role', 'business_admin')
                ->where(function($q) use ($business) {
                    $q->whereNull('business_id')
                      ->orWhere('business_id', $business->id);
                })
                ->get();
        } else {
            // Business admin cannot change their assignment
            $availableAdmins = collect([$user]);
        }
            
        return view('businesses.edit', compact('business', 'availableAdmins'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = auth()->user();
        $business = Business::findOrFail($id);
        
        // Business admin can only update their own business
        if ($user->role === 'business_admin' && $business->business_admin_id !== $user->id) {
            abort(403, 'Unauthorized access');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'business_admin_id' => 'required|exists:users,id',
            'logo' => 'nullable|image|max:2048',
        ]);
        
        // Business admin cannot change their own assignment
        if ($user->role === 'business_admin' && $validated['business_admin_id'] !== $user->id) {
            abort(403, 'You cannot change business admin assignment');
        }

        // Handle logo upload if present
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
            $validated['logo'] = $logoPath;
        }

        $business->update($validated);
        
        // Update the business admin's business assignment (only if changed by superadmin)
        if ($user->role === 'superadmin') {
            User::where('id', $validated['business_admin_id'])
                ->update(['business_id' => $business->id]);
        }
        
        return redirect()->route('businesses.index')
            ->with('success', 'Business updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Only superadmin can delete businesses
        if (auth()->user()->role !== 'superadmin') {
            abort(403, 'Only SuperAdmin can delete businesses');
        }
        
        $business = Business::findOrFail($id);
        $business->delete();
        
        return redirect()->route('businesses.index')
            ->with('success', 'Business deleted successfully');
    }
}
