<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendScheduledInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:send-scheduled';
    protected $description = 'Send invoices that were scheduled for future delivery.';

    public function handle()
    {
        $this->info('Checking for scheduled reminders...');

        // Find invoices that have a scheduled reminder date in the past
        // And are NOT paid.
        // And are NOT draft (they should have been sent already).
        $invoices = \App\Models\Invoice::whereNotNull('scheduled_send_date')
            ->where('scheduled_send_date', '<=', now())
            ->whereIn('status', ['sent', 'overdue']) 
            ->where('balance_due', '>', 0)
            ->get();

        $count = 0;

        foreach ($invoices as $invoice) {
            $this->info("Sending reminder for invoice #{$invoice->invoice_number}...");
            
            try {
                if ($invoice->customer_email) {
                    $subject = "Payment Reminder: Invoice #{$invoice->invoice_number}";
                    $message = "You requested a reminder for this invoice. The balance due is " . number_format($invoice->balance_due, 2);
                    
                    \Illuminate\Support\Facades\Mail::to($invoice->customer_email)
                        ->send(new \App\Mail\InvoiceReminder($invoice, $subject, $message));
                    
                    $this->info("Reminder sent to {$invoice->customer_email}");
                }

                // Clear the scheduled date so we don't send again
                $invoice->update([
                    'scheduled_send_date' => null
                ]);

                $count++;

            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to send reminder #{$invoice->invoice_number}: " . $e->getMessage());
                $this->error("Failed to send reminder #{$invoice->invoice_number}");
            }
        }

        $this->info("Processed {$count} reminders.");
    }
}
