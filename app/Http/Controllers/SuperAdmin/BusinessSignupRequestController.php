<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Business;
use App\Models\BusinessSignupRequest;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BusinessSignupRequestController extends Controller
{
    public function index()
    {
        $requests = BusinessSignupRequest::orderByDesc('created_at')->paginate(20);

        return view('superadmin.business-signup-requests.index', compact('requests'));
    }

    public function show(BusinessSignupRequest $businessSignupRequest)
    {
        return view('superadmin.business-signup-requests.show', [
            'request' => $businessSignupRequest,
        ]);
    }

    public function approve(Request $request, BusinessSignupRequest $businessSignupRequest)
    {
        if ($businessSignupRequest->status !== 'pending') {
            return back()->withErrors(['general' => 'This request has already been processed.']);
        }

        $data = $request->validate([
            'approval_note' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            DB::beginTransaction();

            // Create Business (active by default)
            $business = Business::create([
                'name' => $businessSignupRequest->business_name,
                'logo' => $businessSignupRequest->logo,
                'status' => 'active',
            ]);

            // Create first / main Branch
            $branch = Branch::create([
                'business_id' => $business->id,
                'name' => $businessSignupRequest->branch_name,
                'address' => $businessSignupRequest->address,
                'region' => $businessSignupRequest->region,
                'contact' => $businessSignupRequest->branch_contact ?: $businessSignupRequest->owner_phone,
                'latitude' => $businessSignupRequest->latitude,
                'longitude' => $businessSignupRequest->longitude,
                'manager_id' => null,
            ]);

            // Auto-generate password for Business Admin
            $plainPassword = Str::random(10);

            // Create Business Admin user
            $user = User::create([
                'name' => $businessSignupRequest->owner_name,
                'email' => $businessSignupRequest->owner_email,
                'phone' => $businessSignupRequest->owner_phone,
                'password' => Hash::make($plainPassword),
                'role' => User::ROLE_BUSINESS_ADMIN,
                'business_id' => $business->id,
                'branch_id' => $branch->id,
                'status' => 'active',
            ]);

            // Link business to its primary admin
            $business->update([
                'business_admin_id' => $user->id,
            ]);

            // Update request status
            $businessSignupRequest->update([
                'status' => 'approved',
                'approval_note' => $data['approval_note'] ?? null,
                'approved_by' => auth()->id(),
            ]);

            // Send credentials via SMS
            try {
                $smsService = new SmsService();
                $smsService->sendWelcomeSms(
                    $user->name,
                    $user->email,
                    $plainPassword,
                    $user->role,
                    $user->phone
                );
            } catch (\Throwable $e) {
                Log::error('Failed to send Business Admin welcome SMS: ' . $e->getMessage());
            }

            DB::commit();

            return redirect()
                ->route('superadmin.business-signup-requests.index')
                ->with('success', "Business '{$business->name}' and Business Admin '{$user->name}' created successfully.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Business signup approval failed: ' . $e->getMessage());

            return back()->withErrors(['general' => 'Failed to approve this request. Please check logs.']);
        }
    }

    public function reject(Request $request, BusinessSignupRequest $businessSignupRequest)
    {
        if ($businessSignupRequest->status !== 'pending') {
            return back()->withErrors(['general' => 'This request has already been processed.']);
        }

        $data = $request->validate([
            'approval_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $businessSignupRequest->update([
            'status' => 'rejected',
            'approval_note' => $data['approval_note'] ?? null,
            'approved_by' => auth()->id(),
        ]);

        return redirect()
            ->route('superadmin.business-signup-requests.index')
            ->with('success', 'Business signup request has been rejected.');
    }
}





