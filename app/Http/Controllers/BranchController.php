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

        $branchId = $user->branch_id;

        // If Business Admin has no specific branch assigned, try to find their main branch
        if (!$branchId && $user->role === 'business_admin') {
            $mainBranch = Branch::where('business_id', $user->business_id)
                ->where('is_main', true)
                ->first();
                
            $branchId = $mainBranch ? $mainBranch->id : null;
            
            // Fallback: any branch
            if (!$branchId) {
                $anyBranch = Branch::where('business_id', $user->business_id)->first();
                $branchId = $anyBranch ? $anyBranch->id : null;
            }
        }

        if (!$branchId) {
            // detailed error for business admin
             if ($user->role === 'business_admin') {
                 return redirect()->route('branches.create')->with('error', 'You have no branches yet. Please create one.');
             }
            abort(404, 'You are not assigned to any branch.');
        }

        $branch = Branch::with(['business', 'manager'])->findOrFail($branchId);

        // Reuse the show view or a specific one if needed. 
        // We can just use the 'show' view now that we have it!
        return view('branches.show', compact('branch'));
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

        // Check Branch Limit for Business Admin
        if ($user->role === 'business_admin') {
            $currentBranches = $business->branches()->count();
            $pendingRequests = BranchRequest::where('business_id', $business->id)->where('status', 'pending')->count();
            
            if (($currentBranches + $pendingRequests) >= $business->max_branches) {
                return back()->withInput()->with('error', "You have reached your plan limit of {$business->max_branches} branch(es). Please upgrade your subscription to add more.");
            }
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

    public function show(string $id)
    {
        $branch = Branch::with(['business', 'manager'])->findOrFail($id);
        $user = auth()->user();

        // Check permission
        if ($user->role === 'business_admin' && $branch->business->business_admin_id !== $user->id) {
            abort(403, 'You can only view branches in your own business.');
        }

        return view('branches.show', compact('branch'));
    }

    public function edit(string $id)
    {
        $branch = Branch::findOrFail($id);
        $user = auth()->user();

        // Check permission
        if ($user->role === 'business_admin' && $branch->business->business_admin_id !== $user->id) {
            abort(403, 'You can only edit branches in your own business.');
        }

        return view('branches.edit', compact('branch'));
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

        return redirect()->route('branches.show', $branch->id)->with('success', 'Branch updated successfully!');
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
