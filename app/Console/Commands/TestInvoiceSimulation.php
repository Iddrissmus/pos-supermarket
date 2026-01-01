<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestInvoiceSimulation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:invoice-simulation';
    protected $description = 'Simulate scheduled and recurring invoice processing for testing.';

    public function handle()
    {
        $this->info("Starting Invoice Simulation Test...");
        $this->info("DEBUG: Config app.url is: " . config('app.url'));
        $this->info("DEBUG: Sample Route is: " . route('login'));
        
        // 1. Setup Test Data
        $email = 'iddriss@aykonsult.com';
        $customer = \App\Models\Customer::firstOrCreate(
            ['email' => $email],
            ['name' => 'Iddriss Test', 'phone' => '1234567890']
        );
        
        $branch = \App\Models\Branch::first();
        if (!$branch) {
            $this->error("No branch found. Please create a branch first.");
            return;
        }

        $user = \App\Models\User::first(); // Need a creator

        // --- Scenario 1: Scheduled Invoice (Now acts as Immediate Send + Reminder) ---
        $this->info("\n--- Scenario 1: Testing Scheduled Invoice (Immediate Send + Reminder) ---");
        
        // Cleanup previous test
        \App\Models\Invoice::where('customer_email', $email)
            ->where('notes', 'LIKE', '%TEST_SCHEDULED%')
            ->delete();

        $scheduledInvoice = \App\Models\Invoice::create([
            'customer_id' => $customer->id,
            'customer_email' => $email,
            'branch_id' => $branch->id,
            'created_by' => $user->id ?? 1,
            'invoice_date' => now(),
            'due_date' => now()->addDays(7),
            'status' => 'sent', // Controller now sets this to sent immediately
            'scheduled_send_date' => now()->subMinute(), // Scheduled reminder in past
            'notes' => 'TEST_SCHEDULED',
            'subtotal' => 100,
            'total_amount' => 100,
            'balance_due' => 100,
            'allow_partial_payment' => true,
        ]);
        
        $this->info("Created Scheduled Invoice #{$scheduledInvoice->invoice_number} (Status: {$scheduledInvoice->status}, Reminder Scheduled: {$scheduledInvoice->scheduled_send_date})");
        
        $this->info("Running 'invoices:send-scheduled' (Reminders) command...");
        \Illuminate\Support\Facades\Artisan::call('invoices:send-scheduled');
        $this->info(\Illuminate\Support\Facades\Artisan::output());
        
        $scheduledInvoice->refresh();
        if ($scheduledInvoice->scheduled_send_date === null) {
            $this->info("SUCCESS: Reminder processed and scheduled date cleared.");
        } else {
            $this->error("FAILURE: scheduled_send_date was not cleared. Value: {$scheduledInvoice->scheduled_send_date}");
        }


        // --- Scenario 2: Recurring Invoice ---
        $this->info("\n--- Scenario 2: Testing Recurring Invoice ---");
        
        \App\Models\Invoice::where('customer_email', $email)
           ->where('notes', 'LIKE', '%TEST_RECURRING%')
           ->delete();

        $recurringParent = \App\Models\Invoice::create([
            'customer_id' => $customer->id,
            'customer_email' => $email,
            'branch_id' => $branch->id,
            'created_by' => $user->id ?? 1,
            'invoice_date' => now()->subMonth(),
            'due_date' => now()->subMonth()->addDays(7),
            'status' => 'paid', // Parent usually paid before next one? Or just 'sent'.
            'is_recurring' => true,
            'recurring_frequency' => 'monthly',
            'recurring_next_date' => now()->subMinute(), 
            'notes' => 'TEST_RECURRING_PARENT',
            'subtotal' => 50,
            'total_amount' => 50,
            'balance_due' => 0, 
            'paid_amount' => 50,
        ]);

        $this->info("Created Parent Recurring Invoice #{$recurringParent->invoice_number} (Next Date: {$recurringParent->recurring_next_date})");
        
        $this->info("Running 'invoices:process-recurring' command...");
        \Illuminate\Support\Facades\Artisan::call('invoices:process-recurring');
        $this->info(\Illuminate\Support\Facades\Artisan::output());

        $childInvoice = \App\Models\Invoice::where('parent_invoice_id', $recurringParent->id)->latest()->first();
        if ($childInvoice) {
            $this->info("SUCCESS: Child Invoice #{$childInvoice->invoice_number} created (Status: {$childInvoice->status}).");
        } else {
            $this->error("FAILURE: No child invoice created for #{$recurringParent->invoice_number}.");
        }

        // --- Scenario 3: Partial Payment ---
        $this->info("\n--- Scenario 3: Testing Partial Payment ---");
        
        // Cleanup
        \App\Models\Invoice::where('customer_email', $email)
            ->where('notes', 'LIKE', '%TEST_PARTIAL%')
            ->delete();

        $partialInvoice = \App\Models\Invoice::create([
            'customer_id' => $customer->id,
            'customer_email' => $email,
            'branch_id' => $branch->id,
            'created_by' => $user->id ?? 1,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => 'sent',
            'notes' => 'TEST_PARTIAL',
            'subtotal' => 200,
            'total_amount' => 200,
            'balance_due' => 200,
            'allow_partial_payment' => true,
        ]);

        $this->info("Created Invoice #{$partialInvoice->invoice_number} for Partial Payment (Total: {$partialInvoice->total_amount})");
        
        $this->info("Making partial payment of 50.00...");
        $partialInvoice->markAsPaid(50.00);
        $partialInvoice->refresh();

        if ($partialInvoice->paid_amount == 50.00 && $partialInvoice->balance_due == 150.00) {
             $this->info("SUCCESS: Payment recorded.");
             $this->info("Paid: {$partialInvoice->paid_amount}, Balance: {$partialInvoice->balance_due}");
             
             // Send Payment Email
             try {
                 \Illuminate\Support\Facades\Mail::to($partialInvoice->customer_email)->send(new \App\Mail\InvoicePaid($partialInvoice));
                 $this->info("SUCCESS: Payment receipt sent to {$partialInvoice->customer_email}");
             } catch (\Exception $e) {
                 $this->error("FAILURE: Could not send email. " . $e->getMessage());
             }

             if ($partialInvoice->status_label === 'Partial') {
                 $this->info("SUCCESS: Status Label is 'Partial'.");
             }
        } else {
            $this->error("FAILURE: Balance calculation incorrect.");
        }

        // --- Scenario 4: Immediate Full Payment ---
        $this->info("\n--- Scenario 4: Testing Immediate Full Payment ---");
        
        // Cleanup
        \App\Models\Invoice::where('customer_email', $email)
            ->where('notes', 'LIKE', '%TEST_FULL%')
            ->delete();

        $fullInvoice = \App\Models\Invoice::create([
            'customer_id' => $customer->id,
            'customer_email' => $email,
            'branch_id' => $branch->id,
            'created_by' => $user->id ?? 1,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => 'sent',
            'notes' => 'TEST_FULL',
            'subtotal' => 300,
            'total_amount' => 300,
            'balance_due' => 300,
        ]);
        
        $this->info("Created Invoice #{$fullInvoice->invoice_number} for Full Payment (Total: {$fullInvoice->total_amount})");
        
        $this->info("Making full payment of 300.00...");
        $fullInvoice->markAsPaid(300.00);
        $fullInvoice->refresh();
        
        if ($fullInvoice->status === 'paid' && $fullInvoice->balance_due == 0) {
            $this->info("SUCCESS: Invoice marked as 'paid'.");
            
            // Send Payment Email
            try {
                 \Illuminate\Support\Facades\Mail::to($fullInvoice->customer_email)->send(new \App\Mail\InvoicePaid($fullInvoice));
                 $this->info("SUCCESS: Payment receipt sent to {$fullInvoice->customer_email}");
             } catch (\Exception $e) {
                 $this->error("FAILURE: Could not send email. " . $e->getMessage());
             }

        } else {
            $this->error("FAILURE: Invoice status is '{$fullInvoice->status}'.");
        }
        
        $this->info("\nTest Complete.");
    }
}
