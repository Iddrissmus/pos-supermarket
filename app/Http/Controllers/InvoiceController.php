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
        
        $query = Invoice::with(['customer', 'branch'])
            ->orderBy('created_at', 'desc');

        if ($user->role === 'business_admin') {
            $query->whereHas('branch', function($q) use ($user) {
                $q->where('business_id', $user->business_id);
            });
        } elseif ($user->role === 'manager') {
            $query->where('branch_id', $user->branch_id);
        }

        $invoices = $query->paginate(20);

        // Calculate Stats
        $stats = [
            'total_revenue' => Invoice::where('status', 'paid')->sum('total_amount'),
            'pending_amount' => Invoice::whereIn('status', ['sent', 'draft'])->sum('total_amount'),
            'overdue_amount' => Invoice::where('status', 'overdue')->sum('total_amount'),
            'total_invoices' => Invoice::count(),
        ];

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
            'send_now' => 'boolean', // Added for send_now functionality
        ]);

        try {
            DB::beginTransaction();

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
                'subtotal' => 0,
                'total_amount' => 0,
                'balance_due' => 0,
            ]);

            $subtotal = 0;

            // Create Invoice Items
            // Create Invoice Items
            foreach ($validated['items'] as $item) {
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
            $invoice->tax_amount = 0; // TODO: Implement tax logic same as POS
            $invoice->total_amount = $subtotal; // + tax
            $invoice->balance_due = $invoice->total_amount;
            $invoice->save();

            DB::commit();

            // Send immediately if requested
            if ($request->send_now) {
                // Call the updated send method
                $this->send($invoice->id);
                return response()->json([
                    'success' => true, 
                    'redirect_url' => route('invoices.index'),
                    'message' => 'Invoice created and sent successfully!'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully',
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
