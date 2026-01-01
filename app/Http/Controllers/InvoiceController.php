<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\BranchProduct;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceCreated;
use App\Services\SmsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }
    /**
     * Display a listing of invoices.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $baseQuery = Invoice::query();

        // Scope by Role
        if ($user->role === 'business_admin') {
            $baseQuery->whereHas('branch', function($q) use ($user) {
                $q->where('business_id', $user->business_id);
            });
        } elseif ($user->role === 'manager') {
            $baseQuery->where('branch_id', $user->branch_id);
        }

        // Clone base query for stats to ensure they match the filtered list context
        // (Use clones to avoid polluting the original builder for subsequent queries)
        $stats = [
            'total_revenue' => (clone $baseQuery)->where('status', 'paid')->sum('total_amount'),
            'pending_amount' => (clone $baseQuery)->whereIn('status', ['sent', 'draft'])->sum('total_amount'),
            'overdue_amount' => (clone $baseQuery)->where('status', 'overdue')->sum('total_amount'),
            'total_invoices' => (clone $baseQuery)->count(),
        ];

        // Get paginated results
        $invoices = $baseQuery->with(['customer', 'branch'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('invoices.index', compact('invoices', 'stats'));
    }

    /**
     * Show the form for creating a new invoice.
     */
    public function create()
    {
        $user = Auth::user();
        
        // Get branches similar to SalesController logic
        if ($user->role === 'business_admin') {
            $branches = Branch::where('business_id', $user->business_id)->get();
        } else {
            $branches = Branch::where('id', $user->branch_id)->get();
        }
        
        $customers = Customer::orderBy('name')->get();

        // Get products available in stock
        $branchIds = $branches->pluck('id');
        $products = BranchProduct::with('product')
            ->whereIn('branch_id', $branchIds)
            ->where('stock_quantity', '>', 0)
            ->get()
            ->map(function($bp) {
                return [
                    'id' => $bp->product->id,
                    'name' => $bp->product->name,
                    'price' => $bp->price ?? $bp->selling_price,
                    'stock' => $bp->stock_quantity,
                    'branch_id' => $bp->branch_id
                ];
            });

        return view('invoices.create', compact('branches', 'customers', 'products'));
    }

    /**
     * Store a newly created invoice in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_email' => 'required_without:customer_id|nullable|email',
            'customer_phone' => 'nullable|string',
            'branch_id' => 'required|exists:branches,id',
            'due_date' => 'required|date|after_or_equal:today',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'send_now' => 'boolean',
            'delivery_type' => 'required|in:instant,scheduled,recurring',
            'scheduled_send_date' => 'nullable|required_if:delivery_type,scheduled|date|after:now',
            'is_recurring' => 'boolean',
            'recurring_frequency' => 'nullable|required_if:is_recurring,true|in:weekly,monthly,quarterly,yearly',
            'allow_partial_payment' => 'boolean',
        ]);

        try {
            DB::beginTransaction();
            
            // Logic for Recurring Date Calculation
            $nextRecurringDate = null;
            if (!empty($validated['is_recurring']) && $validated['is_recurring']) {
                 $start = \Carbon\Carbon::now();
                 // If scheduled, maybe start recurring after that? 
                 // For now simplicity: recurring starts from "now" or creation date.
                 $nextRecurringDate = \Carbon\Carbon::parse($validated['due_date']);
                 $start = \Carbon\Carbon::now();
                 
                 switch ($validated['recurring_frequency']) {
                    case 'weekly': $nextRecurringDate = $start->addWeek(); break;
                    case 'monthly': $nextRecurringDate = $start->addMonth(); break;
                    case 'quarterly': $nextRecurringDate = $start->addQuarter(); break;
                    case 'yearly': $nextRecurringDate = $start->addYear(); break;
                }
            }

            // Create Invoice Header
            $invoice = Invoice::create([
                'customer_id' => $validated['customer_id'],
                'customer_email' => $validated['customer_email'] ?? Customer::find($validated['customer_id'])->email,
                'customer_phone' => $validated['customer_phone'] ?? optional(Customer::find($validated['customer_id']))->phone,
                'branch_id' => $validated['branch_id'],
                'created_by' => Auth::id(),
                'invoice_date' => now(),
                'due_date' => $validated['due_date'],
                'status' => 'draft',
                'notes' => $validated['notes'],
                'subtotal' => 0, // Calculated below
                'total_amount' => 0,
                'balance_due' => 0,
                'is_recurring' => $validated['is_recurring'] ?? false,
                'recurring_frequency' => $validated['recurring_frequency'] ?? null,
                'recurring_next_date' => $nextRecurringDate,
                'allow_partial_payment' => $validated['allow_partial_payment'] ?? false,
                'scheduled_send_date' => ($validated['delivery_type'] === 'scheduled') ? $validated['scheduled_send_date'] : null,
            ]);

            // ... Items processing (unchanged) ...
            $subtotal = 0;
            foreach ($validated['items'] as $item) {
                // ... (logic same as before, see context)
                $product = Product::findOrFail($item['product_id']);
                $lineTotal = $item['quantity'] * $item['price'];
                $subtotal += $lineTotal;

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'line_total' => $lineTotal,
                ]);
            }

            // Calculate Totals
            $invoice->subtotal = $subtotal;
            $invoice->tax_amount = 0; 
            $invoice->total_amount = $subtotal;
            $invoice->balance_due = $invoice->total_amount;
            $invoice->save();

            DB::commit();

            // Handle Delivery
            if ($validated['delivery_type'] === 'instant' && $request->send_now) {
                $this->send($invoice->id);
                $message = 'Invoice created and sent successfully!';
            } elseif ($validated['delivery_type'] === 'scheduled') {
                // NEW LOGIC: Send immediately, set reminder for later
                $this->send($invoice->id);
                $date = \Carbon\Carbon::parse($validated['scheduled_send_date'])->format('M d, Y h:i A');
                $message = "Invoice sent! A reminder is scheduled for {$date} if unpaid.";
            } elseif ($validated['delivery_type'] === 'recurring') {
                // Recurring usually implies sending the first one now?
                // Requirements often say "Setup recurring". Usually first one is sent, then next one logic.
                // Or if just "Recurring", maybe first one is sent too.
                // Let's assume Recurring also sends the first one immediately if "send_now" equivalent is implied or strictly set.
                // But the UI sends delivery_type='recurring'.
                // If "send_now" was checked in previous UI, it meant "instant".
                // Here "Instant" is a type.
                // Let's send the first recurring one immediately as well to be standard.
                $this->send($invoice->id);
                $message = 'Invoice created, sent, and recurrence set up!';
            } else {
                $message = 'Invoice saved as draft.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect_url' => route('invoices.show', $invoice)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load(['items.product', 'customer', 'branch', 'createdBy']);
        return view('invoices.show', compact('invoice'));
    }
    /**
     * Send the invoice via Email and SMS.
     */
    public function send($id)
    {
        $invoice = ($id instanceof Invoice) ? $id : Invoice::findOrFail($id);
        
        // Generate secure public link (Always use APP_URL from .env for external links)
        $baseUrl = config('app.url');
        $paymentLink = rtrim($baseUrl, '/') . '/pay/' . $invoice->uuid;

        try {
            // Generate PDF
            $pdf = Pdf::loadView('invoices.pdf', compact('invoice', 'paymentLink'));

            // Send Email with Attachment
            if ($invoice->customer_email) {
                Mail::to($invoice->customer_email)->send(new InvoiceCreated($invoice, $pdf->output(), $paymentLink));
            }

            // Send SMS (Short link)
            if ($invoice->customer_phone) {
                // Shorten link logic could go here if needed
                $message = "Invoice #{$invoice->invoice_number} from " . ($invoice->branch->business->name ?? 'POS') . ". Pay here: {$paymentLink}";
                $this->smsService->sendSms($invoice->customer_phone, $message);
            }

            $invoice->update([
                'status' => 'sent', 
                'sent_at' => now(), 
                'payment_link_token' => $paymentLink // Store link if needed, though usually dynamic
            ]);

            return back()->with('success', 'Invoice sent successfully via Email ' . ($invoice->customer_phone ? '& SMS' : ''));
        } catch (\Exception $e) {
            Log::error('Error sending invoice: ' . $e->getMessage());
            return back()->with('error', 'Failed to send invoice: ' . $e->getMessage());
        }
    }

    public function downloadPdf($id)
    {
        $invoice = Invoice::findOrFail($id);
        $baseUrl = config('app.url');
        $paymentLink = rtrim($baseUrl, '/') . '/pay/' . $invoice->uuid;
        
        $pdf = Pdf::loadView('invoices.pdf', compact('invoice', 'paymentLink'));
        
        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }
}
