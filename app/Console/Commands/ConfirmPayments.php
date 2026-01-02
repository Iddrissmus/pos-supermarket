<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Services\PaystackService;
use Illuminate\Support\Facades\Log;

class ConfirmPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:check-payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks for payment confirmations for all transactions';

    /**
     * Execute the console command.
     */
    public function handle(PaystackService $paystackService)
    {
        $this->info('Starting payment verification check...');

        // Find unpaid invoices that have a payment initiation reference
        $invoices = Invoice::whereIn('status', ['sent', 'overdue'])
            ->whereNotNull('payment_reference')
            ->get();

        if ($invoices->isEmpty()) {
            $this->info('No pending transactions to check.');
            return;
        }

        foreach ($invoices as $invoice) {
            $this->info("Checking payment for Invoice #{$invoice->invoice_number} (Ref: {$invoice->payment_reference})");

            try {
                $verification = $paystackService->verifyTransaction($invoice->payment_reference);

                if ($verification['status'] && $verification['data']['status'] === 'success') {
                    $amountPaid = $verification['data']['amount'] / 100;
                    
                    // Mark as paid
                    $invoice->markAsPaid($amountPaid);
                    
                    // Create system transaction if it doesn't exist
                    $exists = \App\Models\SystemTransaction::where('reference', $invoice->payment_reference)->exists();
                    if (!$exists) {
                         \App\Models\SystemTransaction::create([
                            'business_id' => $invoice->branch->business_id,
                            'amount' => $amountPaid,
                            'currency' => 'GHS',
                            'reference' => $invoice->payment_reference,
                            'channel' => 'paystack',
                            'source_type' => get_class($invoice),
                            'source_id' => $invoice->id,
                            'status' => 'success',
                            'payout_status' => 'pending',
                        ]);
                    }

                    $this->info("✅ Invoice #{$invoice->invoice_number} marked as paid.");
                } else {
                    $this->line("Invoice #{$invoice->invoice_number} payment status: " . ($verification['data']['status'] ?? 'unknown'));
                }
            } catch (\Exception $e) {
                $this->error("Error verifying payment for Invoice #{$invoice->invoice_number}: " . $e->getMessage());
                Log::error("Schedule Payment Error: " . $e->getMessage());
            }
        }

        $this->info('Payment verification check completed.');
    }
}
