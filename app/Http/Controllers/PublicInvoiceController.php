<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PublicInvoiceController extends Controller
{
    protected $paystackService;

    public function __construct(PaystackService $paystackService)
    {
        $this->paystackService = $paystackService;
    }

    /**
     * Show the public invoice page.
     */
    public function show($uuid)
    {
        $invoice = Invoice::where('uuid', $uuid)->with(['items.product', 'branch.business'])->firstOrFail();
        
        return view('public.invoice.show', compact('invoice'));
    }

    /**
     * Initiate Paystack Payment for Invoice
     */
    public function pay(Request $request, $uuid)
    {
        $invoice = Invoice::where('uuid', $uuid)->firstOrFail();

        if ($invoice->status === 'paid') {
             return redirect()->route('public.invoice.show', $uuid)->with('info', 'This invoice is already paid.');
        }

        $amountInKobo = round($invoice->balance_due * 100);
        $email = $invoice->customer_email;
        $callbackUrl = route('public.invoice.callback', ['uuid' => $uuid]);

        try {
            // Initiate standard transaction
            $response = $this->paystackService->initializeTransaction(
                $email, 
                $amountInKobo, 
                $callbackUrl,
                ['invoice_id' => $invoice->id] // Metadata
            );

            if ($response['status'] && isset($response['data']['authorization_url'])) {
                return redirect($response['data']['authorization_url']);
            }

            return back()->with('error', 'Failed to initialize payment gateway.');

        } catch (\Exception $e) {
            Log::error("Paystack Init Error: " . $e->getMessage());
            return back()->with('error', 'Payment initialization error.');
        }
    }

    /**
     * Handle Paystack Callback
     */
    public function callback(Request $request, $uuid)
    {
        $reference = $request->query('reference');
        $invoice = Invoice::where('uuid', $uuid)->firstOrFail();

        if (!$reference) {
            return redirect()->route('public.invoice.show', $uuid)->with('error', 'No payment reference provided.');
        }

        $verification = $this->paystackService->verifyTransaction($reference);

        if ($verification['status'] && $verification['data']['status'] === 'success') {
            $amountPaid = $verification['data']['amount'] / 100;
            
            // Mark invoice as paid
            $invoice->markAsPaid($amountPaid); // Using model method
            
            // Check if fully paid or partial? 
            // Model method defaults to full balance if amount not passed, but here we pass explicitly.
            // Adjust logic if partial payments allowed. For now assume full.

            return redirect()->route('public.invoice.show', $uuid)->with('success', 'Payment successful! Thank you.');
        }

        return redirect()->route('public.invoice.show', $uuid)->with('error', 'Payment verification failed.');
    }
}
