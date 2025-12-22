<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;

class BranchAssignmentController extends Controller
{
    /**
     * Display the assignment form and existing assignments.
     */
    public function index()
    {
        $admin = auth()->user();
        
        if ($admin->role === 'superadmin') {
            // Superadmin sees all branches and users
            $branches = Branch::with('business')->orderBy('name')->get();
            $users = User::whereIn('role', ['manager', 'cashier'])
                ->with(['branch.business'])
                ->orderBy('name')
                ->get();
        } elseif ($admin->role === 'business_admin') {
            // Business admin sees all branches in their business
            if (!$admin->business_id) {
                return redirect()->route('dashboard')
                    ->with('error', 'You have not been assigned to a business yet. Please contact the superadmin.');
            }
            
            $branches = Branch::where('business_id', $admin->business_id)
                ->with('business')
                ->orderBy('name')
                ->get();
            $users = User::whereIn('role', ['manager', 'cashier'])
                ->where('business_id', $admin->business_id)
                ->with(['branch.business'])
                ->orderBy('name')
                ->get();
        } else {
            return redirect()->route('dashboard')
                ->with('error', 'Access denied.');
        }

        return view('admin.branch-assignments', compact('branches', 'users'));
    }

    /**
     * Assign or unassign a manager/cashier to a branch.
     */
    public function store(Request $request)
    {
        $admin = auth()->user();
        
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $user = User::findOrFail($validated['user_id']);

        // Authorization checks based on admin role
        if ($admin->role === 'business_admin') {
            // Business admin must be assigned to a business
            if (!$admin->business_id) {
                return back()->with('error', 'You have not been assigned to a business yet. Please contact the superadmin.');
            }
            
            // Business admin can only assign users to branches in THEIR business
            if ($validated['branch_id']) {
                $branch = Branch::find($validated['branch_id']);
                if ($branch->business_id != $admin->business_id) {
                    return back()->with('error', 'You can only assign users to branches in your business.');
                }
            }
            
            // Business admin can only manage users in their business
            if ($user->business_id != $admin->business_id) {
                return back()->with('error', 'You can only manage users in your business.');
            }
        }

        // Handle unassignment (branch_id is null or empty)
        if (empty($validated['branch_id'])) {
            $user->update(['branch_id' => null]);
            return back()->with('success', "{$user->name} has been unassigned from all branches.");
        }

        // Handle assignment
        $branch = Branch::find($validated['branch_id']);

        // Check for duplicate role on the same branch (one-per-role rule)
        if (in_array($user->role, ['manager', 'cashier'])) {
            $existingUser = User::where('branch_id', $validated['branch_id'])
                ->where('role', $user->role)
                ->where('id', '!=', $user->id)
                ->first();

            if ($existingUser) {
                return back()->with('error', "Branch {$branch->display_label} already has a {$user->role} assigned: {$existingUser->name}. Each branch can only have one manager and one cashier.");
            }
        }

        $user->update(['branch_id' => $validated['branch_id']]);

        return back()->with('success', "{$user->name} has been assigned to {$branch->display_label}.");
    }
}
