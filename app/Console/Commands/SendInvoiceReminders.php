<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendInvoiceReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:send-reminders';
    protected $description = 'Send reminders for due and overdue invoices.';

    public function handle()
    {
        $this->info('Sending invoice reminders...');

        // 1. Due Soon (3 days before)
        $dueSoon = \App\Models\Invoice::where('status', 'sent')
            ->whereDate('due_date', now()->addDays(3)->toDateString())
            ->where('balance_due', '>', 0)
            ->get();
            
        $this->processReminders($dueSoon, 'due_soon');

        // 2. Due Today
        $dueToday = \App\Models\Invoice::where('status', 'sent')
            ->whereDate('due_date', now()->toDateString())
            ->where('balance_due', '>', 0)
            ->get();
            
        $this->processReminders($dueToday, 'due_today');

        // 3. Overdue (3 days after)
        $overdue = \App\Models\Invoice::where('status', '!=', 'paid')
            ->whereDate('due_date', now()->subDays(3)->toDateString())
            ->where('balance_due', '>', 0)
            ->get();
            
        $this->processReminders($overdue, 'overdue');
    }

    private function processReminders($invoices, $type)
    {
        foreach ($invoices as $invoice) {
            if (!$invoice->customer_email) continue;

            $subject = match($type) {
                'due_soon' => "Payment Reminder: Invoice #{$invoice->invoice_number} is due soon",
                'due_today' => "Payment Due Today: Invoice #{$invoice->invoice_number}",
                'overdue' => "Overdue Notice: Invoice #{$invoice->invoice_number}",
            };
            
            $message = match($type) {
                'due_soon' => "This is a gentle reminder that your invoice is due on " . $invoice->due_date->format('M d, Y') . ".",
                'due_today' => "Your invoice is due today. Please make payment at your earliest convenience.",
                'overdue' => "We noticed that we haven't received payment for this invoice yet. It was due on " . $invoice->due_date->format('M d, Y') . ".",
            };

            try {
                 // Inline mailable logic or creation?
                 // Let's create a generic Notification mailable or reuse existing structure.
                 // For now, I'll assume we can use a raw generic email or creating a new mailable is best.
                 // I will create App\Mail\InvoiceReminder in a moment, assuming it exists here.
                 
                 \Illuminate\Support\Facades\Mail::to($invoice->customer_email)->send(
                     new \App\Mail\InvoiceReminder($invoice, $subject, $message)
                 );
                 
                 $this->info("Sent {$type} reminder for #{$invoice->invoice_number}");
                 
                 // If overdue, update status if not already
                 if ($type === 'overdue' && $invoice->status !== 'overdue') {
                     $invoice->update(['status' => 'overdue']);
                 }

            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to send reminder for {$invoice->invoice_number}: " . $e->getMessage());
            }
        }
    }
}
