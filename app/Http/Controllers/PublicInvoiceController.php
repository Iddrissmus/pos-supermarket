<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoicePaid;

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

        $amount = $invoice->balance_due; // Default to full balance

        if ($request->has('amount') && $invoice->allow_partial_payment) {
            $minAmount = $invoice->balance_due / 2;
            $request->validate([
                'amount' => 'required|numeric|min:' . $minAmount . '|max:' . $invoice->balance_due
            ], [
                'amount.min' => 'Partial payment must be at least half of the amount due (GH₵ ' . number_format($minAmount, 2) . ').'
            ]);
            $amount = $request->input('amount');
        }

        $amountInKobo = round($amount * 100);
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
                // Store the transaction reference for later verification
                $invoice->update(['payment_reference' => $response['data']['reference']]);
                
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
            
            // Record System Transaction (Revenue Tracking)
            \App\Models\SystemTransaction::create([
                'business_id' => $invoice->branch->business_id,
                'amount' => $amountPaid,
                'currency' => 'GHS',
                'reference' => $reference,
                'channel' => 'paystack', // Or 'online'
                'source_type' => get_class($invoice),
                'source_id' => $invoice->id,
                'status' => 'success',
                'payout_status' => 'pending', // Online payment needs payout
            ]);

            // Check if fully paid or partial? 
            // Model method defaults to full balance if amount not passed, but here we pass explicitly.
            // Adjust logic if partial payments allowed. For now assume full.

            // Send Paid Invoice Email
            if ($invoice->customer_email) {
                try {
                    Mail::to($invoice->customer_email)->send(new InvoicePaid($invoice));
                } catch (\Exception $e) {
                    Log::error("Failed to send invoice paid email: " . $e->getMessage());
                    // Don't fail the request just because email failed
                }
            }

            return redirect()->route('public.invoice.show', $uuid)->with('success', 'Payment successful! Thank you.');
        }

        return redirect()->route('public.invoice.show', $uuid)->with('error', 'Payment verification failed.');
    }
}
