<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashierAssignmentController extends Controller
{
    /**
     * Display the cashier assignment interface for the manager
     */
    public function index()
    {
        $manager = Auth::user();
        
        // Only allow managers who are assigned to a branch
        if ($manager->role !== 'manager' || !$manager->branch_id) {
            return redirect()->route('dashboard.manager')
                ->with('error', 'You must be assigned to a branch to manage cashiers.');
        }

        $managedBranch = $manager->branch()->with('business')->first();
        
        // Get all cashiers assigned to this branch
        $assignedCashiers = User::where('role', 'cashier')
            ->where('branch_id', $managedBranch->id)
            ->get();

        // Get unassigned cashiers (cashiers without a branch)
        $unassignedCashiers = User::where('role', 'cashier')
            ->whereNull('branch_id')
            ->get();

        return view('manager.cashiers', compact('managedBranch', 'assignedCashiers', 'unassignedCashiers'));
    }

    /**
     * Create a new cashier and assign to manager's branch
     */
    public function create(Request $request)
    {
        $manager = Auth::user();
        
        // Only allow managers who are assigned to a branch
        if ($manager->role !== 'manager' || !$manager->branch_id) {
            return back()->with('error', 'You are not authorized to create cashiers.');
        }

        // Check if a cashier already exists for this branch
        $existingCashier = User::where('role', 'cashier')
            ->where('branch_id', $manager->branch_id)
            ->first();

        if ($existingCashier) {
            return back()->with('error', 'A cashier is already assigned to your branch. Please unassign the current cashier first before creating a new one.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Create the cashier user
        $cashier = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => 'cashier',
            'business_id' => $manager->business_id,
            'branch_id' => $manager->branch_id,
        ]);

        return back()->with('success', "Cashier {$cashier->name} has been created and assigned to your branch.");
    }

    /**
     * Assign a cashier to the manager's branch
     */
    public function assign(Request $request)
    {
        $manager = Auth::user();
        
        if ($manager->role !== 'manager' || !$manager->branch_id) {
            return back()->with('error', 'You are not authorized to assign cashiers.');
        }

        // Check if a cashier already exists for this branch
        $existingCashier = User::where('role', 'cashier')
            ->where('branch_id', $manager->branch_id)
            ->first();

        if ($existingCashier) {
            return back()->with('error', 'A cashier is already assigned to your branch. Please unassign the current cashier first before assigning a new one.');
        }

        $validated = $request->validate([
            'cashier_id' => 'required|exists:users,id',
        ]);

        $cashier = User::where('id', $validated['cashier_id'])
            ->where('role', 'cashier')
            ->whereNull('branch_id')
            ->first();

        if (!$cashier) {
            return back()->with('error', 'Cashier not found or already assigned.');
        }

        $cashier->update(['branch_id' => $manager->branch_id]);

        return back()->with('success', "Cashier {$cashier->name} has been assigned to your branch.");
    }

    /**
     * Unassign (delete) a cashier from the manager's branch
     */
    public function unassign(Request $request)
    {
        $manager = Auth::user();
        
        if ($manager->role !== 'manager' || !$manager->branch_id) {
            return back()->with('error', 'You are not authorized to unassign cashiers.');
        }

        $validated = $request->validate([
            'cashier_id' => 'required|exists:users,id',
        ]);

        $cashier = User::where('id', $validated['cashier_id'])
            ->where('role', 'cashier')
            ->where('branch_id', $manager->branch_id)
            ->first();

        if (!$cashier) {
            return back()->with('error', 'Cashier not found in your branch.');
        }

        $cashierName = $cashier->name;
        
        // Delete the cashier completely
        $cashier->delete();

        return back()->with('success', "Cashier {$cashierName} has been removed from your branch and deleted from the system.");
    }
}