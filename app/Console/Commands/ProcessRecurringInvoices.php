<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessRecurringInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:process-recurring';
    protected $description = 'Process recurring invoices that are due for generation.';

    public function handle()
    {
        $this->info('Checking for recurring invoices...');

        $invoices = \App\Models\Invoice::activeRecurring()
            ->whereDate('recurring_next_date', '<=', now())
            ->get();

        $count = 0;

        foreach ($invoices as $invoice) {
            DB::transaction(function () use ($invoice) {
                // Calculate next date based on frequency
                $nextDate = $this->calculateNextDate($invoice->recurring_next_date, $invoice->recurring_frequency);
                
                // Update parent next date immediately to prevent double processing if failure happens later (in a job)
                // but we are in transaction so it's fine.
                $invoice->update(['recurring_next_date' => $nextDate]);

                // Create new invoice
                $newInvoice = $invoice->replicate([
                    'uuid', 'invoice_number', 'status', 'created_at', 'updated_at', 
                    'sent_at', 'paid_at', 'payment_link_token', 'balance_due', 'paid_amount', 
                    'invoice_date', 'due_date', 'is_recurring', 'recurring_frequency', 
                    'recurring_end_date', 'recurring_next_date'
                ]);

                $newInvoice->invoice_number = \App\Models\Invoice::generateInvoiceNumber();
                $newInvoice->invoice_date = now();
                
                // Calculate new due date (keep same duration)
                $daysToDue = $invoice->invoice_date->diffInDays($invoice->due_date);
                $newInvoice->due_date = now()->addDays($daysToDue);
                
                $newInvoice->status = 'draft'; // Or 'sent' if we auto-send? Let's default to draft or sent based on pref. 
                // Let's set to 'sent' if we auto-email, but for now 'draft' is safer unless we want auto-billing.
                // Requirement asked for "Recurring Invoice -> ... reminder sent with payment link".
                // So we should probably mark as SENT and email it.
                $newInvoice->status = 'sent';
                $newInvoice->sent_at = now();
                $newInvoice->parent_invoice_id = $invoice->id;
                
                // Reset totals (re-calculate later just to be safe, but replicate copies them)
                // Paid amount 0 of course
                $newInvoice->paid_amount = 0;
                $newInvoice->balance_due = $newInvoice->total_amount;
                
                $newInvoice->save();

                // Replicate Items
                foreach ($invoice->items as $item) {
                    $newItem = $item->replicate(['id', 'invoice_id', 'created_at', 'updated_at']);
                    $newItem->invoice_id = $newInvoice->id;
                    $newItem->save();
                }

                // Send Email?
                // Dispatch logic from Controller
                if ($newInvoice->customer_email) {
                    try {
                        // We can reuse the controller's logic or dispatch event
                        // For simplicity, let's call the controller's send method logic effectively
                         $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.pdf', [
                             'invoice' => $newInvoice, 
                             'paymentLink' => route('public.invoice.show', $newInvoice->uuid) // Link to view/pay
                         ]);
                         
                         \Illuminate\Support\Facades\Mail::to($newInvoice->customer_email)->send(
                             new \App\Mail\InvoiceCreated($newInvoice, $pdf->output(), route('public.invoice.show', $newInvoice->uuid))
                         );
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("Failed to email recurring invoice {$newInvoice->invoice_number}: " . $e->getMessage());
                    }
                }
            });
            $count++;
        }

        $this->info("Processed {$count} recurring invoices.");
    }

    private function calculateNextDate($currentDate, $frequency)
    {
        $date = \Carbon\Carbon::parse($currentDate);
        return match($frequency) {
            'weekly' => $date->addWeek(),
            'monthly' => $date->addMonth(),
            'quarterly' => $date->addQuarter(),
            'yearly' => $date->addYear(),
            default => $date->addMonth(),
        };
    }
}
