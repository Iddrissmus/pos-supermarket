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
        $user = auth()->user();

        if ($user->role === 'superadmin'){
            //they can see all businesses 
            $businesses = Business::with('primaryBusinessAdmin')->paginate(15);
        }
        else {
            //business admin sees only their business
            $businesses = Business::with('primaryBusinessAdmin')
                ->where('id', $user->business_id)
                ->paginate(15);
        }
        return view('businesses.index', compact('businesses'));
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
            
        return view('businesses.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'branch_name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'region' => 'required|string|max:100',
            'contact' => 'required|string|max:20',
            'logo' => 'nullable|image|max:2048', // Optional logo, max size 2MB
        ]);

        // Handle logo upload if present
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
            $validated['logo'] = $logoPath;
        }

        // Create the business
        $business = Business::create([
            'name' => $validated['name'],
            'logo' => $validated['logo'] ?? null,
        ]);
        
        // Automatically create the first branch with the business location
        Branch::create([
            'business_id' => $business->id,
            'name' => $validated['branch_name'],
            'address' => $validated['address'],
            'region' => $validated['region'],
            'contact' => $validated['contact'],
            'manager_id' => null, // Will be assigned by Business Admin later
        ]);
        
        // Send SMS notification to the business contact
        try {
            Log::info("Attempting to send SMS for business creation", [
                'business_name' => $business->name,
                'contact' => $validated['contact']
            ]);
            
            $smsService = new SmsService();
            $message = "Welcome! Your business '{$business->name}' has been successfully registered in our POS system. Main branch: {$validated['branch_name']}, Location: {$validated['address']}, {$validated['region']}. A Business Admin will be assigned soon.";
            
            $smsSent = $smsService->sendSms($validated['contact'], $message);
            
            if ($smsSent) {
                Log::info("Business creation SMS sent successfully to {$validated['contact']}");
                return redirect()->route('businesses.index')
                    ->with('success', "Business '{$business->name}' created successfully with main branch '{$validated['branch_name']}'! SMS notification sent to {$validated['contact']}.");
            } else {
                Log::warning("Business creation SMS sending returned false for {$validated['contact']}");
            }
        } catch (\Exception $e) {
            Log::error('Business creation SMS failed: ' . $e->getMessage());
        }
        
        return redirect()->route('businesses.index')
            ->with('success', "Business '{$business->name}' created successfully with main branch '{$validated['branch_name']}'! You can now create a Business Admin user and assign them to this business.");
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
