<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\BranchRequest;
use App\Models\Branch;
use App\Models\User;
use App\Notifications\BranchRequestApproved;
use App\Notifications\BranchRequestRejected;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\ActivityLogger;

class BranchRequestController extends Controller
{
    /**
     * Display a listing of branch requests.
     */
    public function index(Request $request)
    {
        $query = BranchRequest::with(['business', 'requestedBy', 'reviewedBy']);

        // Filter by status if provided
        if ($request->has('status') && in_array($request->status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $request->status);
        }

        $requests = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get counts for each status
        $pendingCount = BranchRequest::where('status', 'pending')->count();
        $approvedCount = BranchRequest::where('status', 'approved')->count();
        $rejectedCount = BranchRequest::where('status', 'rejected')->count();
        $totalCount = BranchRequest::count();

        return view('superadmin.branch-requests.index', compact(
            'requests', 
            'pendingCount', 
            'approvedCount', 
            'rejectedCount', 
            'totalCount'
        ));
    }

    /**
     * Display the specified branch request.
     */
    public function show(BranchRequest $branchRequest)
    {
        $branchRequest->load(['business', 'requestedBy', 'reviewedBy']);
        
        return view('superadmin.branch-requests.show', compact('branchRequest'));
    }

    /**
     * Approve a branch request.
     */
    public function approve(BranchRequest $branchRequest)
    {
        if ($branchRequest->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'This request has already been processed.');
        }

        DB::beginTransaction();
        
        try {
            // Create the branch
            $branch = Branch::create([
                'business_id' => $branchRequest->business_id,
                'name' => $branchRequest->branch_name,
                'address' => $branchRequest->address,
                'contact' => $branchRequest->phone,
                'region' => $branchRequest->location,
                'latitude' => $branchRequest->latitude,
                'longitude' => $branchRequest->longitude,
            ]);

            // Update the request
            $branchRequest->update([
                'status' => 'approved',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            // Notify the requester
            $requester = $branchRequest->requestedBy;
            if ($requester) {
                $requester->notify(new BranchRequestApproved($branchRequest, $branch));
            }

            // Log activity
            ActivityLogger::logModel('approve', $branchRequest, [], [
                'business_id' => $branchRequest->business_id,
                'branch_name' => $branchRequest->branch_name
            ]);

            DB::commit();

            return redirect()->route('superadmin.branch-requests.index')
                ->with('success', 'Branch request approved and branch created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Failed to approve request: ' . $e->getMessage());
        }
    }

    /**
     * Reject a branch request.
     */
    public function reject(Request $request, BranchRequest $branchRequest)
    {
        if ($branchRequest->status !== 'pending') {
            return redirect()->back()
                ->with('error', 'This request has already been processed.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        DB::beginTransaction();
        
        try {
            // Update the request
            $branchRequest->update([
                'status' => 'rejected',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'rejection_reason' => $validated['rejection_reason'],
            ]);

            // Notify the requester
            $requester = $branchRequest->requestedBy;
            if ($requester) {
                $requester->notify(new BranchRequestRejected($branchRequest));
            }

            // Log activity
            ActivityLogger::logModel('reject', $branchRequest, [], [
                'business_id' => $branchRequest->business_id,
                'branch_name' => $branchRequest->branch_name,
                'reason' => $validated['rejection_reason']
            ]);

            DB::commit();

            return redirect()->route('superadmin.branch-requests.index')
                ->with('success', 'Branch request rejected successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->back()
                ->with('error', 'Failed to reject request: ' . $e->getMessage());
        }
    }
}
