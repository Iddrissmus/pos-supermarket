<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     */
    public function index(Request $request)
    {
        $customers = Customer::query()
            ->where('business_id', auth()->user()->business_id)
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('company', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('customer_number', 'like', "%{$search}%");
                });
            })
            ->when($request->type, function ($query, $type) {
                return $query->where('customer_type', $type);
            })
            ->when($request->status, function ($query, $status) {
                if ($status === 'active') {
                    return $query->where('is_active', true);
                } elseif ($status === 'inactive') {
                    return $query->where('is_active', false);
                }
            })
            ->withCount(['sales', 'invoices'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created customer in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:customers,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'customer_type' => 'required|in:individual,business',
            'payment_terms' => 'required|in:immediate,next_15,next_30,next_60',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['business_id'] = Auth::user()->business_id;
        $customer = Customer::create($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully',
                'customer' => $customer
            ]);
        }

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer created successfully! Customer #' . $customer->customer_number);
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer)
    {
        $customer->load(['sales.items.product', 'sales.branch', 'sales.cashier']);
        
        $recentSales = $customer->sales()
            ->with(['items.product', 'branch', 'cashier'])
            ->latest()
            ->take(10)
            ->get();

        $salesSummary = [
            'total_sales' => $customer->sales()->count(),
            'total_amount' => $customer->sales()->sum('total'),
            'average_order' => $customer->sales()->avg('total') ?? 0,
            'last_purchase' => $customer->sales()->latest()->first()?->created_at,
        ];

        return view('customers.show', compact('customer', 'recentSales', 'salesSummary'));
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:customers,email,' . $customer->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'customer_type' => 'required|in:individual,business',
            'payment_terms' => 'required|in:immediate,next_15,next_30,next_60',
            'notes' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $customer->update($validated);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer updated successfully!');
    }

    /**
     * Remove the specified customer from storage.
     */
    public function destroy(Customer $customer)
    {
        // Check if customer has any sales or invoices
        if ($customer->sales()->count() > 0 || $customer->invoices()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete customer with existing sales or invoices. Consider deactivating instead.']);
        }

        $customerNumber = $customer->customer_number;
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', "Customer #{$customerNumber} deleted successfully!");
    }

    /**
     * Toggle customer active status.
     */
    public function toggleStatus(Customer $customer)
    {
        $customer->update(['is_active' => !$customer->is_active]);
        
        $status = $customer->is_active ? 'activated' : 'deactivated';
        
        return back()->with('success', "Customer {$status} successfully!");
    }

    /**
     * Get customer data for AJAX requests.
     */
    public function getCustomerData(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
        ]);

        $customer = Customer::find($request->customer_id);

        return response()->json([
            'id' => $customer->id,
            'name' => $customer->name,
            'company' => $customer->company,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'full_address' => $customer->full_address,
            'payment_terms' => $customer->payment_terms,
            'available_credit' => 0, // Removed feature
        ]);
    }
}
