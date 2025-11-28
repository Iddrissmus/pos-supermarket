<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Business;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    /**
     * Show the form for creating a new branch
     */
    public function create()
    {
        $user = auth()->user();
        
        // Get the business for this user
        if ($user->role === 'superadmin') {
            // SuperAdmin can create branch for any business
            // This shouldn't normally be used, but just in case
            abort(403, 'Please create branches through the business edit page.');
        } elseif ($user->role === 'business_admin') {
            $business = Business::find($user->business_id);
            
            if (!$business) {
                abort(404, 'Business not found.');
            }
            
            return view('branches.create', compact('business'));
        } else {
            abort(403, 'Unauthorized access');
        }
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_id' => 'required|exists:businesses,id',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'contact' => 'nullable|string|max:50',
            'region' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $business = Business::findOrFail($validated['business_id']);
        $user = auth()->user();

        if ($user->role === 'business_admin' && $business->business_admin_id !== $user->id) {
            abort(403, 'You can only add branches to your own business.');
        }

        Branch::create($validated);

        return redirect()->route('businesses.index')->with('success', 'Branch created successfully!');
    }

    public function update(Request $request, string $id)
    {
        $branch = Branch::findOrFail($id);
        $user = auth()->user();

        if ($user->role === 'business_admin' && $branch->business->business_admin_id !== $user->id) {
            abort(403, 'You can only edit branches in your own business.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'contact' => 'nullable|string|max:50',
            'region' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $branch->update($validated);

        return redirect()->back()->with('success', 'Branch updated successfully!');
    }

    public function destroy(string $id)
    {
        $branch = Branch::findOrFail($id);
        $user = auth()->user();

        if ($user->role === 'business_admin' && $branch->business->business_admin_id !== $user->id) {
            abort(403, 'You can only delete branches in your own business.');
        }

        $staffCount = \App\Models\User::where('branch_id', $branch->id)
            ->whereIn('role', ['manager', 'cashier', 'business_admin'])
            ->count();

        if ($staffCount > 0) {
            return redirect()->back()->with('error', "Cannot delete branch. It has {$staffCount} assigned staff member(s). Please reassign them first.");
        }

        $branchName = $branch->name;
        $branch->delete();

        return redirect()->back()->with('success', "Branch '{$branchName}' deleted successfully!");
    }
}
