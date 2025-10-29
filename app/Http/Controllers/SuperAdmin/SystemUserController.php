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
        $branches = Branch::with('business')->orderBy('name')->get();
        
        return view('superadmin.users.create', compact('businesses', 'branches'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => 'required|in:superadmin,business_admin,manager,cashier',
            'business_id' => 'nullable|exists:businesses,id',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        // Validate role-specific requirements
        if ($validated['role'] === 'business_admin' && !$validated['business_id']) {
            return back()->withErrors(['business_id' => 'Business Admin must be assigned to a business.'])->withInput();
        }
        
        if ($validated['role'] === 'business_admin' && !$validated['branch_id']) {
            return back()->withErrors(['branch_id' => 'Business Admin must be assigned to a branch.'])->withInput();
        }

        if ($validated['role'] === 'manager' && !$validated['branch_id']) {
            return back()->withErrors(['branch_id' => 'Manager must be assigned to a branch.'])->withInput();
        }
        
        // Check for duplicate role on the same branch (one-per-role rule)
        if ($validated['branch_id'] && in_array($validated['role'], ['manager', 'cashier', 'business_admin'])) {
            $existingUser = User::where('branch_id', $validated['branch_id'])
                ->where('role', $validated['role'])
                ->first();
                
            if ($existingUser) {
                $branch = Branch::find($validated['branch_id']);
                $roleName = ucfirst(str_replace('_', ' ', $validated['role']));
                return back()->withErrors([
                    'branch_id' => "Branch {$branch->display_label} already has a {$roleName} assigned: {$existingUser->name}. Each branch can only have one {$roleName}."
                ])->withInput();
            }
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'business_id' => $validated['business_id'],
            'branch_id' => $validated['branch_id'],
        ]);

        return redirect()->route('system-users.index')
            ->with('success', "User {$user->name} ({$user->role}) has been created successfully!");
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $systemUser)
    {
        $businesses = Business::orderBy('name')->get();
        $branches = Branch::with('business')->orderBy('name')->get();
        
        return view('superadmin.users.edit', [
            'user' => $systemUser,
            'businesses' => $businesses,
            'branches' => $branches
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
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => 'required|in:superadmin,business_admin,manager,cashier',
            'business_id' => 'nullable|exists:businesses,id',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        // Validate role-specific requirements
        if ($validated['role'] === 'business_admin' && !$validated['business_id']) {
            return back()->withErrors(['business_id' => 'Business Admin must be assigned to a business.'])->withInput();
        }
        
        if ($validated['role'] === 'business_admin' && !$validated['branch_id']) {
            return back()->withErrors(['branch_id' => 'Business Admin must be assigned to a branch.'])->withInput();
        }

        if ($validated['role'] === 'manager' && !$validated['branch_id']) {
            return back()->withErrors(['branch_id' => 'Manager must be assigned to a branch.'])->withInput();
        }
        
        // Check for duplicate role on the same branch (one-per-role rule)
        if ($validated['branch_id'] && in_array($validated['role'], ['manager', 'cashier', 'business_admin'])) {
            $existingUser = User::where('branch_id', $validated['branch_id'])
                ->where('role', $validated['role'])
                ->where('id', '!=', $systemUser->id)
                ->first();
                
            if ($existingUser) {
                $branch = Branch::find($validated['branch_id']);
                $roleName = ucfirst(str_replace('_', ' ', $validated['role']));
                return back()->withErrors([
                    'branch_id' => "Branch {$branch->display_label} already has a {$roleName} assigned: {$existingUser->name}. Each branch can only have one {$roleName}."
                ])->withInput();
            }
        }

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'business_id' => $validated['business_id'],
            'branch_id' => $validated['branch_id'],
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
