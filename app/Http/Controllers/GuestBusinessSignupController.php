<?php

namespace App\Http\Controllers;

use App\Models\BusinessSignupRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GuestBusinessSignupController extends Controller
{
    /**
     * Store a new business signup request from the public landing page.
     */
    /**
     * Store a new business signup request from the public landing page.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'owner_phone' => ['required', 'string', 'max:30'],
            'branch_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'region' => ['required', 'string', 'max:100'],
            'branch_contact' => ['nullable', 'string', 'max:30'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'plan_type' => ['required', 'string', 'in:starter,growth,enterprise'],
        ]);

        try {
            if ($request->hasFile('logo')) {
                $validated['logo'] = $request->file('logo')->store('logos', 'public');
            }

            // Get Plan Price
            $plans = config('plans');
            $plan = $plans[$validated['plan_type']] ?? null;
            if (!$plan) {
                return back()->withErrors(['general' => 'Invalid plan selected.']);
            }
            $amount = $plan['price'] * 100; // Paystack takes subunits (pesewas)

            // Create Pending Request
            $signupRequest = BusinessSignupRequest::create([
                'business_name' => $validated['business_name'],
                'logo' => $validated['logo'] ?? null,
                'owner_name' => $validated['owner_name'],
                'owner_email' => $validated['owner_email'],
                'owner_phone' => $validated['owner_phone'],
                'branch_name' => $validated['branch_name'],
                'address' => $validated['address'],
                'region' => $validated['region'],
                'branch_contact' => $validated['branch_contact'] ?? null,
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'status' => 'pending_payment',
                'plan_type' => $validated['plan_type'],
                'amount_paid' => $plan['price'],
            ]);

            // Initialize Paystack
            $paystack = app(\App\Services\PaystackService::class);
            $response = $paystack->initializeTransaction(
                $validated['owner_email'],
                $amount,
                route('business-signup.callback', ['request_id' => $signupRequest->id]),
                ['signup_id' => $signupRequest->id, 'plan' => $validated['plan_type']]
            );

            if ($response && isset($response['data']['authorization_url'])) {
                // Save reference if needed or just redirect
                return redirect($response['data']['authorization_url']);
            }

            return back()->withErrors(['general' => 'Failed to initialize payment. Please try again.']);

        } catch (\Throwable $e) {
            Log::error('Guest business signup failed: ' . $e->getMessage());
            return back()->withInput()->withErrors(['general' => 'Failed to submit request: ' . $e->getMessage()]);
        }
    }

    /**
     * Handle Paystack Callback
     */
    public function callback(Request $request)
    {
        $reference = $request->query('reference');
        $requestId = $request->query('request_id');

        if (!$reference || !$requestId) {
            return redirect('/')->withErrors(['general' => 'Invalid payment callback parameters.']);
        }

        $signupRequest = BusinessSignupRequest::find($requestId);
        if (!$signupRequest) {
             return redirect('/')->withErrors(['general' => 'Signup request not found.']);
        }
        
        // Check if already processed
        if ($signupRequest->status === 'approved') {
             return redirect('/login/business-admin')->with('success', 'Account already active. Please login.');
        }

        $paystack = app(\App\Services\PaystackService::class);
        $verification = $paystack->verifyTransaction($reference);

        if ($verification && isset($verification['data']['status']) && $verification['data']['status'] === 'success') {
            
            // Payment Successful: Create Business Logic
            try {
                \Illuminate\Support\Facades\DB::beginTransaction();

                $signupRequest->transaction_reference = $reference;
                $signupRequest->status = 'approved';
                $signupRequest->save();
                
                // Get Plan Limits
                $plans = config('plans');
                $planDetails = $plans[$signupRequest->plan_type] ?? $plans['starter'];

                // 1. Create Business
                $business = \App\Models\Business::create([
                    'name' => $signupRequest->business_name,
                    'logo' => $signupRequest->logo,
                    'status' => 'active',
                    'plan_type' => $signupRequest->plan_type,
                    'subscription_status' => 'active',
                    'subscription_expires_at' => now()->addMonth(), // Assuming monthly
                    'max_branches' => $planDetails['max_branches'] ?? 1,
                ]);

                // 2. Create Admin User
                $password = \Illuminate\Support\Str::random(10); // Generate simple password
                $admin = \App\Models\User::create([
                    'name' => $signupRequest->owner_name,
                    'email' => $signupRequest->owner_email,
                    'password' => \Illuminate\Support\Facades\Hash::make($password),
                    'role' => 'business_admin',
                    'business_id' => $business->id,
                    // 'phone' => $signupRequest->owner_phone // If user model has phone
                ]);

                // 3. Create Main Branch
                $branch = \App\Models\Branch::create([
                    'business_id' => $business->id,
                    'name' => $signupRequest->branch_name,
                    'address' => $signupRequest->address,
                    'location' => $signupRequest->region, // or separate region field
                    'phone' => $signupRequest->branch_contact ?? $signupRequest->owner_phone,
                    'is_main' => true,
                    'status' => 'active',
                    'latitude' => $signupRequest->latitude,
                    'longitude' => $signupRequest->longitude,
                ]);
                
                // Update Business admin ID ref? (Optional if logic requires it, usually hasMany is sufficient)
                // But specifically for business table business_admin_id column:
                $business->update(['business_admin_id' => $admin->id]);

                \Illuminate\Support\Facades\DB::commit();

                // 4. Send Credentials via Email
                try {
                    \Illuminate\Support\Facades\Mail::to($admin->email)->send(new \App\Mail\NewBusinessWelcome($admin, $password));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to send welcome email: " . $e->getMessage());
                }

                // Auto Login
                \Illuminate\Support\Facades\Auth::login($admin);

                return redirect()->route('dashboard.business-admin')
                    ->with('success', "Welcome! Your business is ready. We have sent your login credentials to your email.");

            } catch (\Exception $e) {
                \Illuminate\Support\Facades\DB::rollBack();
                Log::error("Failed to create business after payment: " . $e->getMessage());
                 return redirect('/')->withErrors(['general' => 'Payment received but account creation failed. Please contact support.']);
            }

        } else {
             return redirect('/')->withErrors(['general' => 'Payment verification failed.']);
        }
    }
}





