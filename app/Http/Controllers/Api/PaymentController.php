<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PaystackService;
use App\Models\Sale;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $paystackService;

    public function __construct(PaystackService $paystackService)
    {
        $this->paystackService = $paystackService;
    }

    /**
     * Initiate a payment for a sale (or potential sale)
     */
    public function initiate(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'email' => 'required|email',
            'metadata' => 'nullable|array',
            'callback_url' => 'nullable|url'
        ]);

        $amount = $request->amount;
        $email = $request->email;
        $metadata = $request->metadata ?? [];
        $callbackUrl = $request->callback_url;

        // Optionally, if we passed a sale ID, verify it exists
        // $saleId = $metadata['sale_id'] ?? null;

        $response = $this->paystackService->initializeTransaction($email, $amount, $callbackUrl, $metadata);

        if ($response['status'] ?? false) {
            return response()->json([
                'success' => true,
                'data' => $response['data']
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $response['message'] ?? 'Failed to initiate payment'
        ], 400);
    }

    /**
     * Verify a payment reference
     */
    public function verify($reference)
    {
        if (!$reference) {
            return response()->json(['success' => false, 'message' => 'No reference provided'], 400);
        }

        $response = $this->paystackService->verifyTransaction($reference);

        if (($response['status'] ?? false) && ($response['data']['status'] ?? '') === 'success') {
            return response()->json([
                'success' => true,
                'data' => $response['data'],
                'message' => 'Payment successful'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Payment verification failed: ' . ($response['data']['gateway_response'] ?? 'Unknown error')
        ], 400);
    }
}
