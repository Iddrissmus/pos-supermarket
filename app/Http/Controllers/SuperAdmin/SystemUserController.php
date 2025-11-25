<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Business;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SystemUserController extends Controller
{
    /**
     * Display a listing of system users.
     */
    public function index()
    {
        $users = User::with(['managedBusiness', 'branch.business'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('superadmin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $businesses = Business::orderBy('name')->get();
        
        return view('superadmin.users.create', compact('businesses'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => 'required|in:superadmin,business_admin,manager,cashier',
            'business_id' => 'nullable|exists:businesses,id',
        ]);

        // Validate role-specific requirements
        // Business Admin, Manager, and Cashier must be assigned to a business
        if (in_array($validated['role'], ['business_admin', 'manager', 'cashier']) && !$validated['business_id']) {
            return back()->withErrors(['business_id' => ucfirst(str_replace('_', ' ', $validated['role'])) . ' must be assigned to a business.'])->withInput();
        }

        // Store plain password before hashing for SMS
        $plainPassword = $validated['password'];

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'business_id' => $validated['business_id'],
            'branch_id' => null, // Branch assignment handled by Business Admin later
        ]);

        // Send SMS with credentials
        try {
            \Illuminate\Support\Facades\Log::info("Attempting to send SMS to user", [
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
                'role' => $user->role
            ]);
            
            $smsService = new \App\Services\SmsService();
            $smsSent = $smsService->sendWelcomeSms(
                $user->name,
                $user->email,
                $plainPassword,
                $user->role,
                $user->phone
            );

            if ($smsSent) {
                \Illuminate\Support\Facades\Log::info("SMS sent successfully to {$user->phone}");
                return redirect()->route('system-users.index')
                    ->with('success', "User {$user->name} ({$user->role}) has been created successfully! Login credentials sent via SMS to {$user->phone}.");
            } else {
                \Illuminate\Support\Facades\Log::warning("SMS sending returned false for {$user->phone}");
            }
        } catch (\Exception $e) {
            // Log error but don't fail the user creation
            \Illuminate\Support\Facades\Log::error('SMS sending failed: ' . $e->getMessage());
        }

        return redirect()->route('system-users.index')
            ->with('success', "User {$user->name} ({$user->role}) has been created successfully! Note: SMS could not be sent. Please share credentials manually.");
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $systemUser)
    {
        $businesses = Business::orderBy('name')->get();
        
        return view('superadmin.users.edit', [
            'user' => $systemUser,
            'businesses' => $businesses
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $systemUser)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $systemUser->id,
            'phone' => 'required|string|max:20',
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => 'required|in:superadmin,business_admin,manager,cashier',
            'business_id' => 'nullable|exists:businesses,id',
        ]);

        // Validate role-specific requirements
        if (in_array($validated['role'], ['business_admin', 'manager', 'cashier']) && !$validated['business_id']) {
            return back()->withErrors(['business_id' => ucfirst(str_replace('_', ' ', $validated['role'])) . ' must be assigned to a business.'])->withInput();
        }

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'business_id' => $validated['business_id'],
        ];

        // Only update password if provided
        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $systemUser->update($updateData);

        return redirect()->route('system-users.index')
            ->with('success', "User {$systemUser->name} has been updated successfully!");
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $systemUser)
    {
        // Prevent deleting yourself
        if ($systemUser->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account!');
        }

        $userName = $systemUser->name;
        $systemUser->delete();

        return redirect()->route('system-users.index')
            ->with('success', "User {$userName} has been deleted successfully!");
    }
}
