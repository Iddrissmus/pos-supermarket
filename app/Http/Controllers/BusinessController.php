<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Branch;
use App\Models\Business;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class BusinessController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Only superadmin can see all businesses list
        if (auth()->user()->role !== 'superadmin') {
            abort(403, 'Unauthorized access');
        }

        $businesses = Business::with('primaryBusinessAdmin')->paginate(15);
        return view('businesses.index', compact('businesses'));
    }

    /**
     * Show business admin's card view of their business
     */
    public function myBusiness()
    {
        $user = auth()->user();

        // Only business admin can access
        if ($user->role !== 'business_admin') {
            abort(403, 'Unauthorized access');
        }

        $business = Business::with(['primaryBusinessAdmin', 'branches.manager'])
            ->findOrFail($user->business_id);
        
        return view('businesses.business-admin-view', compact('business'));
    }

    /**
     * Update business details by business admin
     */
    public function updateMyBusiness(Request $request)
    {
        $user = auth()->user();

        if ($user->role !== 'business_admin') {
            abort(403, 'Unauthorized access');
        }

        $business = Business::findOrFail($user->business_id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|image|max:2048', // 2MB Max
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
        ]);

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($business->logo && \Storage::disk('public')->exists($business->logo)) {
                \Storage::disk('public')->delete($business->logo);
            }
            $path = $request->file('logo')->store('business-logos', 'public');
            $business->logo = $path;
        }

        $business->name = $validated['name'];
        // Update optional fields if they exist in the request/model
        
        $business->save();

        return redirect()->route('my-business')->with('success', 'Business details updated successfully');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // only superadmin can create business
        $user = auth()->user();
        if ($user->role !== 'superadmin') {
            abort(403, 'Only Super Admin can create a business.');
        }
            
        $businessTypes = \App\Models\BusinessType::where('is_active', true)->get();
        $plans = \App\Models\SubscriptionPlan::where('is_active', true)->get();

        return view('businesses.create', compact('businessTypes', 'plans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'business_type_id' => 'required|exists:business_types,id',
            'plan_id' => 'required|exists:subscription_plans,id',
            'owner_name' => 'required|string|max:255',
            'owner_email' => 'required|email|max:255|unique:users,email',
            'branch_name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'region' => 'required|string|max:100',
            'contact' => 'required|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'logo' => 'nullable|image|max:2048',
        ]);

        // Handle logo upload
        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        \DB::beginTransaction();

        try {
            // 1. Create Owner User
            $owner = User::create([
                'name' => $validated['owner_name'],
                'email' => $validated['owner_email'],
                'password' => \Hash::make(\Str::random(12)), // Temp password, they should reset or we send it? 
                // For now, let's assume they set it via "Forgot Password" or we don't send it? 
                // Better: We are sending an invoice link. After payment, maybe prompt to set password?
                // Or simply: It's an inactive user. 
                'role' => 'business_admin',
                'status' => 'active', // User is active, Business is inactive? Or User inactive?
                // Use 'active' user so they can theoretically log in if we gave them credentials, 
                // but business middleware would block them.
                'phone' => $validated['contact'],
            ]);

            // 2. Create Business (Inactive)
            $business = Business::create([
                'name' => $validated['name'],
                'logo' => $logoPath,
                'owner_id' => $owner->id,
                'business_type_id' => $validated['business_type_id'],
                'current_plan_id' => $validated['plan_id'],
                'status' => 'inactive',
                'subscription_status' => 'pending_payment',
            ]);

            // Assign business to owner
            $owner->update(['business_id' => $business->id]);

            // 3. Create Main Branch
            Branch::create([
                'business_id' => $business->id,
                'name' => $validated['branch_name'],
                'address' => $validated['address'],
                'region' => $validated['region'],
                'contact' => $validated['contact'],
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'manager_id' => $owner->id, // Assign owner as initial manager
            ]);

            // 4. Send Invoice Notification
            $plan = \App\Models\SubscriptionPlan::find($validated['plan_id']);
            $owner->notify(new \App\Notifications\SubscriptionInvoiceNotification($business, $plan));

            \DB::commit();
            
            return view('businesses.success', compact('business', 'owner', 'plan'));

        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('Business creation failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to create business: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = auth()->user();
        $business = Business::with('primaryBusinessAdmin', 'branches')->findOrFail($id);
        // Ensure that non-superadmin users can only view their own business
        if ($user->role === 'business_admin' && $business->id !== $user->business_id) {
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
        $business = Business::with('primaryBusinessAdmin', 'branches')->findOrFail($id);
        
        // Business admin can only edit their own business
        if ($user->role === 'business_admin' && $business->id !== $user->business_id) {
            abort(403, 'Unauthorized access');
        }
        
        // Only superadmin can change business admin assignment
        if ($user->role === 'superadmin') {
            // Show all business admins, with info about their current assignments
            $availableAdmins = User::where('role', 'business_admin')
                ->with('managedBusiness')
                ->orderByRaw('CASE WHEN business_id IS NULL THEN 0 WHEN business_id = ? THEN 1 ELSE 2 END', [$business->id])
                ->orderBy('name')
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
        if ($user->role === 'business_admin' && $business->id !== $user->business_id) {
            abort(403, 'Unauthorized access');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'business_admin_id' => 'nullable|exists:users,id',
            'logo' => 'nullable|image|max:2048',
        ]);
        
        // Handle logo upload if present
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
            $validated['logo'] = $logoPath;
        }

        // Only update name and logo
        $business->update([
            'name' => $validated['name'],
            'logo' => $validated['logo'] ?? $business->logo,
        ]);
        
        // Update the business admin's business assignment (only if changed by superadmin)
        if ($user->role === 'superadmin' && isset($validated['business_admin_id'])) {
            // Remove old admin assignment
            User::where('business_id', $business->id)
                ->where('role', 'business_admin')
                ->update(['business_id' => null]);
                
            // Assign new admin
            User::where('id', $validated['business_admin_id'])
                ->update(['business_id' => $business->id]);
        }
        
        return redirect()->route('businesses.index')
            ->with('success', "Business '{$business->name}' updated successfully.");
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
        $businessName = $business->name;
        $business->delete();
        
        return redirect()->route('businesses.index')
            ->with('success', "Business '{$businessName}' deleted successfully.");
    }
    
    /**
     * Show map view of all businesses and branches (SuperAdmin only)
     */
    public function map()
    {
        // Only superadmin can access
        if (auth()->user()->role !== 'superadmin') {
            abort(403, 'Unauthorized access');
        }
        
        // Get all businesses with their branches (with and without coordinates)
        $businesses = Business::with(['branches'])->get();
        
        // Format data for map markers (only branches with coordinates)
        $mapData = [];
        $branchesWithoutCoordinates = [];
        
        foreach ($businesses as $business) {
            foreach ($business->branches as $branch) {
                if ($branch->latitude && $branch->longitude) {
                    $mapData[] = [
                        'id' => $branch->id,
                        'business_name' => $business->name,
                        'business_logo' => $business->logo ? asset('storage/' . $business->logo) : null,
                        'branch_name' => $branch->name,
                        'address' => $branch->address,
                        'region' => $branch->region,
                        'contact' => $branch->contact,
                        'latitude' => (float) $branch->latitude,
                        'longitude' => (float) $branch->longitude,
                        'manager' => $branch->manager ? $branch->manager->name : 'Not assigned',
                    ];
                } else {
                    $branchesWithoutCoordinates[] = [
                        'id' => $branch->id,
                        'business_name' => $business->name,
                        'branch_name' => $branch->name,
                        'address' => $branch->address,
                        'region' => $branch->region,
                    ];
                }
            }
        }
        
        return view('businesses.map', compact('mapData', 'branchesWithoutCoordinates'));
    }
    
    /**
     * Show map view for business admin (only their business branches)
     */
    public function myMap()
    {
        $user = auth()->user();
        
        // Only business admin can access
        if ($user->role !== 'business_admin') {
            abort(403, 'Unauthorized access');
        }
        
        // Get the business admin's business with branches
        $business = Business::with(['branches'])->find($user->business_id);
        
        if (!$business) {
            abort(404, 'Business not found');
        }
        
        // Format data for map markers (only branches with coordinates)
        $mapData = [];
        $branchesWithoutCoordinates = [];
        
        foreach ($business->branches as $branch) {
            if ($branch->latitude && $branch->longitude) {
                $mapData[] = [
                    'id' => $branch->id,
                    'business_name' => $business->name,
                    'business_logo' => $business->logo ? asset('storage/' . $business->logo) : null,
                    'branch_name' => $branch->name,
                    'address' => $branch->address,
                    'region' => $branch->region,
                    'contact' => $branch->contact,
                    'latitude' => (float) $branch->latitude,
                    'longitude' => (float) $branch->longitude,
                    'manager' => $branch->manager ? $branch->manager->name : 'Not assigned',
                ];
            } else {
                $branchesWithoutCoordinates[] = [
                    'id' => $branch->id,
                    'business_name' => $business->name,
                    'branch_name' => $branch->name,
                    'address' => $branch->address,
                    'region' => $branch->region,
                ];
            }
        }
        
        return view('businesses.my-map', compact('mapData', 'branchesWithoutCoordinates', 'business'));
    }

    /**
     * Activate a business
     */
    public function activate(Business $business)
    {
        // Only superadmin can activate businesses
        if (auth()->user()->role !== 'superadmin') {
            abort(403, 'Only Super Admin can activate businesses.');
        }

        $business->update(['status' => 'active']);

        return redirect()->route('businesses.index')
            ->with('success', "Business '{$business->name}' has been activated successfully.");
    }

    /**
     * Disable a business
     */
    public function disable(Business $business)
    {
        // Only superadmin can disable businesses
        if (auth()->user()->role !== 'superadmin') {
            abort(403, 'Only Super Admin can disable businesses.');
        }

        $business->update(['status' => 'inactive']);

        return redirect()->route('businesses.index')
            ->with('success', "Business '{$business->name}' has been disabled.");
    }

    /**
     * Block a business
     */
    public function block(Business $business)
    {
        // Only superadmin can block businesses
        if (auth()->user()->role !== 'superadmin') {
            abort(403, 'Only Super Admin can block businesses.');
        }

        $business->update(['status' => 'blocked']);

        return redirect()->route('businesses.index')
            ->with('success', "Business '{$business->name}' has been blocked.");
    }
}
