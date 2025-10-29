<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashierController extends Controller
{
    /**
     * Display all managers and cashiers for business admin management
     */
    public function index()
    {
        $admin = Auth::user();
        
        if ($admin->role !== 'business_admin') {
            return redirect()->route('dashboard')
                ->with('error', 'Access denied. Business Admin privileges required.');
        }

        // Business admin must be assigned to a branch
        if (!$admin->branch_id) {
            return redirect()->route('dashboard')
                ->with('error', 'You have not been assigned to a branch yet. Please contact the superadmin.');
        }

        // Get all managers and cashiers in the business admin's assigned branch
        $managers = User::where('role', 'manager')
            ->where('branch_id', $admin->branch_id)
            ->with('branch.business')
            ->orderBy('name')
            ->get();
            
        $cashiers = User::where('role', 'cashier')
            ->where('branch_id', $admin->branch_id)
            ->with('branch.business')
            ->orderBy('name')
            ->get();

        // Get only the business admin's assigned branch for the dropdown
        $branches = Branch::where('id', $admin->branch_id)
            ->with('business')
            ->get();

        return view('admin.cashiers', compact('managers', 'cashiers', 'branches'));
    }

    /**
     * Create a new manager or cashier (business admin can assign to branches in their business)
     */
    public function create(Request $request)
    {
        $admin = Auth::user();
        
        if ($admin->role !== 'business_admin') {
            return back()->with('error', 'Access denied. Business Admin privileges required.');
        }

        // Business admin must be assigned to a branch
        if (!$admin->branch_id) {
            return back()->with('error', 'You have not been assigned to a branch yet. Please contact the superadmin.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:manager,cashier',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        // Verify branch is the business admin's assigned branch
        if ($validated['branch_id']) {
            if ($validated['branch_id'] != $admin->branch_id) {
                return back()->with('error', 'You can only assign staff to your assigned branch.');
            }
            
            $branch = Branch::find($validated['branch_id']);
            
            // Check if branch already has a user with this role
            $existingUser = User::where('role', $validated['role'])
                ->where('branch_id', $validated['branch_id'])
                ->first();

            if ($existingUser) {
                return back()->with('error', "Branch {$branch->display_label} already has a {$validated['role']} assigned: {$existingUser->name}. Each branch can only have one manager and one cashier.");
            }
        }

        // Create the user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => $validated['role'],
            'business_id' => $admin->business_id,
            'branch_id' => $validated['branch_id'],
        ]);

        $roleName = ucfirst($validated['role']);
        $message = "{$roleName} {$user->name} has been created";
        if ($user->branch_id) {
            $branch = Branch::find($user->branch_id);
            $message .= " and assigned to {$branch->display_label}";
        }
        $message .= ".";

        return back()->with('success', $message);
    }

    /**
     * Assign a manager or cashier to a branch
     */
    public function assign(Request $request)
    {
        $admin = Auth::user();
        
        if ($admin->role !== 'business_admin') {
            return back()->with('error', 'Access denied. Business Admin privileges required.');
        }

        // Business admin must be assigned to a branch
        if (!$admin->branch_id) {
            return back()->with('error', 'You have not been assigned to a branch yet. Please contact the superadmin.');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'branch_id' => 'required|exists:branches,id',
        ]);

        // Verify branch is the business admin's assigned branch
        if ($validated['branch_id'] != $admin->branch_id) {
            return back()->with('error', 'You can only assign staff to your assigned branch.');
        }

        $user = User::where('id', $validated['user_id'])
            ->whereIn('role', ['manager', 'cashier'])
            ->first();

        if (!$user) {
            return back()->with('error', 'Invalid user selected.');
        }
        
        // Verify user can only be managed if they're in the admin's branch or unassigned
        if ($user->branch_id && $user->branch_id != $admin->branch_id) {
            return back()->with('error', 'You can only manage staff in your assigned branch.');
        }
        
        $branch = Branch::find($validated['branch_id']);

        // Check if another user with same role is already assigned to this branch
        $existingUser = User::where('role', $user->role)
            ->where('branch_id', $validated['branch_id'])
            ->where('id', '!=', $user->id)
            ->first();

        if ($existingUser) {
            return back()->with('error', "Branch {$branch->display_label} already has a {$user->role} assigned: {$existingUser->name}. Each branch can only have one manager and one cashier.");
        }

        $user->update(['branch_id' => $validated['branch_id']]);

        return back()->with('success', ucfirst($user->role) . " {$user->name} has been assigned to {$branch->display_label}.");
    }

    /**
     * Unassign a manager or cashier from their branch
     */
    public function unassign(Request $request)
    {
        $admin = Auth::user();
        
        if ($admin->role !== 'business_admin') {
            return back()->with('error', 'Access denied. Business Admin privileges required.');
        }

        // Business admin must be assigned to a branch
        if (!$admin->branch_id) {
            return back()->with('error', 'You have not been assigned to a branch yet. Please contact the superadmin.');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::where('id', $validated['user_id'])
            ->whereIn('role', ['manager', 'cashier'])
            ->where('branch_id', $admin->branch_id)
            ->first();

        if (!$user) {
            return back()->with('error', 'Invalid user selected or user not in your assigned branch.');
        }

        $user->update(['branch_id' => null]);

        return back()->with('success', ucfirst($user->role) . " {$user->name} has been unassigned from their branch.");
    }

    /**
     * Delete a manager or cashier completely
     */
    public function delete(Request $request)
    {
        $admin = Auth::user();
        
        if ($admin->role !== 'business_admin') {
            return back()->with('error', 'Access denied. Business Admin privileges required.');
        }

        // Business admin must be assigned to a branch
        if (!$admin->branch_id) {
            return back()->with('error', 'You have not been assigned to a branch yet. Please contact the superadmin.');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::where('id', $validated['user_id'])
            ->whereIn('role', ['manager', 'cashier'])
            ->where('branch_id', $admin->branch_id)
            ->first();

        if (!$user) {
            return back()->with('error', 'Invalid user selected or user not in your assigned branch.');
        }

        $userName = $user->name;
        $userRole = ucfirst($user->role);
        $user->delete();

        return back()->with('success', "{$userRole} {$userName} has been permanently deleted from the system.");
    }
}
