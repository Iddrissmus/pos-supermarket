<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;

class PaystackService
{
    protected $baseUrl = 'https://api.paystack.co';
    protected $secretKey;

    public function __construct()
    {
        $this->secretKey = Setting::get('paystack_secret_key');
    }

    /**
     * Initialize a transaction (Standard / Inline)
     */
    public function initializeTransaction($email, $amount, $callbackUrl = null, $metadata = [])
    {
        $url = $this->baseUrl . '/transaction/initialize';
        
        $fields = [
            'email' => $email,
            'amount' => $amount * 100, // Amount in kobo
            'metadata' => json_encode($metadata),
        ];

        if ($callbackUrl) {
            $fields['callback_url'] = $callbackUrl;
        }

        return $this->makeRequest('POST', $url, $fields);
    }

    /**
     * Verify a transaction
     */
    public function verifyTransaction($reference)
    {
        $url = $this->baseUrl . '/transaction/verify/' . rawurlencode($reference);
        return $this->makeRequest('GET', $url);
    }

    /**
     * Charge Mobile Money directly (Charge API)
     * Note: This requires the correct payload for MoMo (provider 'mtn', 'vodafone', 'tigo')
     */
    public function chargeMobileMoney($email, $amount, $phone, $provider)
    {
        $url = $this->baseUrl . '/charge';

        $fields = [
            'email' => $email,
            'amount' => $amount * 100,
            'mobile_money' => [
                'phone' => $phone,
                'provider' => $provider
            ],
            // 'metadata' => ...
        ];

        return $this->makeRequest('POST', $url, $fields);
    }

    protected function makeRequest($method, $url, $data = [])
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
                'Cache-Control' => 'no-cache',
            ])->$method($url, $data);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Paystack Request Failed', [
                'url' => $url,
                'method' => $method,
                'data' => $data,
                'response' => $response->body()
            ]);

            return [
                'status' => false,
                'message' => 'Paystack API Error: ' . $response->body()
            ];

        } catch (\Exception $e) {
            Log::error('Paystack Connection Error', ['error' => $e->getMessage()]);
            return [
                'status' => false,
                'message' => 'Connection Error: ' . $e->getMessage()
            ];
        }
    }
}
