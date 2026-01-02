<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\SystemTransaction;
use App\Services\PaystackService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscriptionPaymentController extends Controller
{
    protected $paystackService;

    public function __construct(PaystackService $paystackService)
    {
        $this->paystackService = $paystackService;
    }

    public function show($id)
    {
        // Find by UUID or ID
        $business = Business::where('uuid', $id)->orWhere('id', $id)->firstOrFail();

        if ($business->status === 'active' && $business->subscription_status === 'active') {
            return redirect('/login')->with('info', 'Your subscription is already active. Please login.');
        }

        $plan = $business->currentPlan; 
        // Ensure plan is loaded. If null, we have a problem.
        if (!$plan) {
             abort(404, 'Subscription plan not found.');
        }

        return view('public.subscription.pay', compact('business', 'plan'));
    }

    public function pay(Request $request, $id)
    {
        $business = Business::where('uuid', $id)->orWhere('id', $id)->firstOrFail();
        $plan = $business->currentPlan;
        
        $amountInKobo = round($plan->price * 100);
        $email = $business->owner->email;
        $callbackUrl = route('subscription.payment.callback', ['uuid' => $id]); // ID in storage, but let's use what we have

        try {
            $response = $this->paystackService->initializeTransaction(
                $email,
                $amountInKobo,
                $callbackUrl,
                ['business_id' => $business->id, 'plan_id' => $plan->id, 'type' => 'subscription']
            );

            if ($response['status'] && isset($response['data']['authorization_url'])) {
                return redirect($response['data']['authorization_url']);
            }

            return back()->with('error', 'Failed to initialize payment.');

        } catch (\Exception $e) {
            Log::error("Sub Pay Init Error: " . $e->getMessage());
            return back()->with('error', 'Payment error: ' . $e->getMessage());
        }
    }

    public function callback(Request $request)
    {
        $reference = $request->query('reference');
        if (!$reference) {
            return redirect('/')->with('error', 'No reference supplied');
        }

        $verification = $this->paystackService->verifyTransaction($reference);

        if ($verification['status'] && $verification['data']['status'] === 'success') {
            $metadata = $verification['data']['metadata'] ?? [];
            $businessId = $metadata['business_id'] ?? null;
            
            if (!$businessId) {
                // Fallback attempt to find context if metadata missing (unlikely)
                return redirect('/')->with('error', 'Invalid transaction data.');
            }

            $business = Business::find($businessId);
            $plan = $business->currentPlan;

            // Activate Business
            $business->status = 'active';
            $business->subscription_status = 'active';
            
            // Calculate Expiry
            $durationDays = $plan->duration_days ?? 30;
            $business->subscription_expires_at = Carbon::now()->addDays($durationDays);
            $business->save();

            // Record Transaction
            SystemTransaction::create([
                'business_id' => $business->id,
                'amount' => $plan->price,
                'currency' => 'GHS',
                'reference' => $reference,
                'channel' => 'paystack',
                'source_type' => get_class($plan), // Using Plan as source, or Business? Usually Invoice but we don't have one.
                'source_id' => $plan->id, 
                'status' => 'success',
                'payout_status' => 'pending', // Platform Revenue, but technically "Pending" usually means "Owed to Business".
                                              // WAIT. Subscription revenue belongs to YOU (Superadmin).
                                              // So Payout Status should be 'paid' (Collected) or 'platform_revenue'?
                                              // The current system_transactions table was built for "Payouts to Businesses".
                                              // If this is YOUR money, you shouldn't list it as "Pending Payout" to a business.
                                              // I should set it to 'collected_by_platform' or 'paid'.
                                              // Let's us 'paid' or a new status.
                                              // For now, I'll use 'paid' (Settled) because you don't owe it to anyone.
                'payout_status' => 'paid', 
            ]);

            return redirect('/login')->with('success', 'Subscription activated! You can now login.');
        }

        return redirect('/')->with('error', 'Payment verification failed.');
    }
}
