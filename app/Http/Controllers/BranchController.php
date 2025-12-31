<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchRequest;
use App\Models\Business;
use App\Models\User;
use App\Notifications\BranchRequestCreated;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class BranchController extends Controller
{
    /**
     * Show the branch details for the authenticated user (Manager/Cashier)
     */
    public function myBranch()
    {
        $user = auth()->user();

        if (!$user->branch_id) {
            abort(404, 'You are not assigned to any branch.');
        }

        $branch = Branch::with(['business', 'manager'])->findOrFail($user->branch_id);

        // Reuse the show view or a specific one if needed. 
        // For now, let's look for a suitable view. 
        // If I haven't checked existing views, I might default to creating one or using 'branches.show' if it exists.
        // Assuming 'branches.show' exists based on typical patterns, or I'll check the list_dir output first in my head?
        // Actually, the previous tool call list_dir is running in parallel or I should wait.
        // But I can't wait in the same turn.
        // I will assume I need to create a view or use a generic one.
        // Let's safe bet: create a new view 'branches.my-branch' to be safe and specific.
        
        return view('branches.my-branch', compact('branch'));
    }

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
            'location' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'notes' => 'nullable|string|max:1000',
        ]);

        $business = Business::findOrFail($validated['business_id']);
        $user = Auth::user();

        // Check permission
        if ($user->role === 'business_admin' && $business->business_admin_id !== $user->id) {
            abort(403, 'You can only add branches to your own business.');
        }

        // Business admins create requests, superadmins create directly
        if ($user->role === 'business_admin') {
            // Create branch request for superadmin approval
            $branchRequest = BranchRequest::create([
                'business_id' => $validated['business_id'],
                'requested_by' => $user->id,
                'branch_name' => $validated['name'],
                'location' => $validated['location'],
                'address' => $validated['address'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending',
            ]);

            // Notify all superadmins
            $superadmins = User::where('role', 'superadmin')->get();
            Notification::send($superadmins, new BranchRequestCreated($branchRequest));

            // Log branch request activity
            ActivityLogger::logModel('create', $branchRequest, [], [
                'branch_name' => $branchRequest->branch_name,
                'location' => $branchRequest->location,
                'business_id' => $branchRequest->business_id,
            ]);

            return redirect()->route('businesses.index')
                ->with('success', 'Branch request submitted successfully! Waiting for superadmin approval.');
        } else {
            // Superadmin creates branch directly
            $branch = Branch::create([
                'business_id' => $validated['business_id'],
                'name' => $validated['name'],
                'address' => $validated['address'],
                'contact' => $validated['phone'],
                'region' => $validated['location'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
            ]);

            // Log branch creation activity
            ActivityLogger::logModel('create', $branch, [], [
                'name' => $branch->name,
                'location' => $branch->region,
                'business_id' => $branch->business_id,
            ]);

            return redirect()->route('businesses.index')
                ->with('success', 'Branch created successfully!');
        }
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
