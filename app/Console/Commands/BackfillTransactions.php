<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sale;
use App\Models\Invoice;
use App\Models\SystemTransaction;
use Illuminate\Support\Str;

class BackfillTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:backfill-transactions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill system_transactions table from existing Sales and Invoices';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting backfill of system transactions...');
        $this->backfillSales();
        $this->backfillInvoices();
        $this->info('Backfill completed successfully.');
    }

    private function backfillSales()
    {
        $this->info('Processing Sales...');
        $sales = Sale::with('branch')->get();
        $count = 0;

        foreach ($sales as $sale) {
            // Only backfill online payments
            if (!in_array($sale->payment_method, ['card', 'mobile_money', 'paystack'])) {
                continue;
            }

            // Check if already exists
            if (SystemTransaction::where('source_type', get_class($sale))
                ->where('source_id', $sale->id)
                ->exists()) {
                continue;
            }

            SystemTransaction::create([
                'uuid' => (string) Str::uuid(),
                'business_id' => $sale->branch->business_id,
                'amount' => $sale->total,
                'currency' => 'GHS',
                'reference' => $sale->payment_reference ?? 'BACKFILL-SALE-' . $sale->id,
                'channel' => $sale->payment_method,
                'source_type' => get_class($sale),
                'source_id' => $sale->id,
                'status' => 'success',
                'payout_status' => 'pending',
                'created_at' => $sale->created_at, // Preserve original date
                'updated_at' => $sale->updated_at,
            ]);
            $count++;
        }
        $this->info("Processed {$count} new sales transactions (Online Only).");
    }

    private function backfillInvoices()
    {
        $this->info('Processing Invoices...');
        // Only paid invoices with Paystack reference usually. 
        // For backfill purposes on "paystack only", we should check if we store references.
        // We do: payment_link_token ?? 
        // Actually, without clear 'channel' column on invoices, it's hard to distinguish safely.
        // But user said "only tracking paystack".
        // I will assume for now that if an invoice is paid, and it wasn't manual status update (which we can't distinguish easily without activities),
        // we might skip them or look for specific flags.
        // FOR SAFETY: I will disable invoice backfilling for now to avoid polluting "Paystack Tracking" with manual cash payments.
        // Unless we check activity logs? Too complex.
        
        $this->info("Skipping Invoice backfill as historical payment method is ambiguous. Only new Paystack invoice payments are tracked.");
    }
}
