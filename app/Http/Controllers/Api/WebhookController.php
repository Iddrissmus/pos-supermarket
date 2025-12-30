<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;
use App\Models\Sale; // Assuming we will link payments to sales eventually

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        // 1. Verify Signature
        $secretKey = Setting::get('paystack_secret_key');
        
        if (!$secretKey) {
            Log::error('Paystack Webhook: Secret Key not found in settings');
            return response()->json(['status' => 'error', 'message' => 'Config error'], 500);
        }

        $signature = $request->header('x-paystack-signature');
        $payload = $request->getContent();

        if (!$signature || $signature !== hash_hmac('sha512', $payload, $secretKey)) {
            Log::warning('Paystack Webhook: Invalid Signature');
            return response()->json(['status' => 'error', 'message' => 'Invalid Header'], 400);
        }

        // 2. Parse Event
        $event = $request->input('event');
        $data = $request->input('data');

        Log::info('Paystack Webhook Received', ['event' => $event]);

        if ($event === 'charge.success') {
            $this->handleChargeSuccess($data);
        }

        return response()->json(['status' => 'success'], 200);
    }

    protected function handleChargeSuccess($data)
    {
        $reference = $data['reference'];
        $metadata = $data['metadata'] ?? [];
        
        // Example: If we stored our internal sale_id in metadata
        // $saleId = $metadata['sale_id'] ?? null;
        
        Log::info("Payment successful for reference: {$reference}", ['metadata' => $metadata]);
        
        // TODO: Update database status if we pre-created the record.
        // For POS, we often create the record AFTER payment confirmation on the frontend,
        // but robust systems should handle it here too.
        // For now, we just log it as the frontend handles the immediate "Success" UI.
    }
}
