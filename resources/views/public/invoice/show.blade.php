<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice #{{ $invoice->invoice_number }} - {{ $invoice->branch->business->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 text-slate-800 antialiased">

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    
    <!-- Status Messages -->
    @if(session('success'))
        <div class="mb-6 bg-emerald-100 border border-emerald-200 text-emerald-800 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif
    
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <!-- Header / Banner -->
        <div class="bg-slate-900 px-6 py-8 md:px-8 md:py-10 text-white">
            <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-6">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold">{{ $invoice->branch->business->name }}</h1>
                    <p class="text-slate-400 mt-1">{{ $invoice->branch->name }}</p>
                    <div class="text-sm text-slate-400 mt-4 max-w-xs leading-relaxed">
                        {{ $invoice->branch->address ?? 'Address not available' }}<br>
                        {{ $invoice->branch->phone ?? '' }}
                    </div>
                </div>
                <div class="text-left md:text-right">
                    <div class="inline-block px-4 py-1.5 rounded-full text-sm font-bold tracking-wide uppercase mb-4
                        {{ $invoice->status == 'paid' ? 'bg-emerald-500 text-white' : 'bg-amber-400 text-amber-900' }}">
                        {{ ucfirst($invoice->status) }}
                    </div>
                    <h2 class="text-xl font-mono opacity-80">#{{ $invoice->invoice_number }}</h2>
                    <p class="text-sm text-slate-400 mt-1">Due: {{ $invoice->due_date->format('M d, Y') }}</p>
                </div>
            </div>
        </div>

        <!-- Invoice Body -->
        <div class="p-6 md:p-8 relative overflow-hidden">
            @if($invoice->status === 'paid')
                <div class="hidden md:block absolute top-10 right-10 transform rotate-[-30deg] pointer-events-none z-0 opacity-10">
                    <span class="text-9xl font-black text-emerald-600 uppercase border-[12px] border-emerald-600 px-10 py-4 rounded-xl tracking-widest leading-none">PAID</span>
                </div>
                <div class="md:hidden absolute top-4 right-4 transform rotate-[-15deg] pointer-events-none z-10 opacity-80">
                    <span class="text-4xl font-black text-emerald-600 uppercase border-4 border-emerald-600 px-4 py-1 rounded-lg bg-emerald-50/90 backdrop-blur-sm tracking-widest">PAID</span>
                </div>
            @endif
            <!-- Customer Info -->
            <div class="mb-8 p-5 md:p-6 bg-slate-50 rounded-xl border border-slate-100">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Bill To</h3>
                <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                    <div>
                        <p class="text-lg font-bold text-slate-800">{{ $invoice->customer->name ?? $invoice->customer_email }}</p>
                        @if($invoice->customer_email)
                            <p class="text-sm text-slate-500">{{ $invoice->customer_email }}</p>
                        @endif
                        @if($invoice->customer_phone)
                            <p class="text-sm text-slate-500">{{ $invoice->customer_phone }}</p>
                        @endif
                    </div>
                    <div class="pt-4 md:pt-0 border-t md:border-t-0 border-slate-200 md:text-right">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Total Amount</p>
                        <p class="text-3xl font-bold text-slate-900">GH₵ {{ number_format($invoice->total_amount, 2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Items Section (Desktop Table / Mobile Stack) -->
            <div class="mb-8">
                <!-- Mobile List View (< md) -->
                <div class="md:hidden space-y-4">
                    @foreach($invoice->items as $item)
                    <div class="bg-white border border-slate-100 rounded-lg p-4 shadow-sm">
                        <div class="flex justify-between items-start mb-2">
                            <span class="font-bold text-slate-800 text-sm">{{ $item->product_name }}</span>
                            <span class="font-bold text-slate-800 text-sm">GH₵ {{ number_format($item->line_total, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-xs text-slate-500">
                            <span>{{ $item->quantity }} x GH₵ {{ number_format($item->unit_price, 2) }}</span>
                        </div>
                    </div>
                    @endforeach
                    
                    <!-- Mobile Totals -->
                    <div class="mt-4 space-y-2 pt-4 border-t border-slate-100">
                         <div class="flex justify-between text-sm text-slate-600">
                            <span>Subtotal</span>
                            <span>{{ number_format($invoice->subtotal, 2) }}</span>
                        </div>
                        @if($invoice->tax_amount > 0)
                        <div class="flex justify-between text-sm text-slate-600">
                            <span>Tax</span>
                            <span>{{ number_format($invoice->tax_amount, 2) }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between text-lg font-bold text-indigo-600 pt-2">
                            <span>Total Due</span>
                            <span>GH₵ {{ number_format($invoice->balance_due, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Desktop Table View (>= md) -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200">
                                <th class="py-3 text-left text-xs font-bold text-slate-500 uppercase">Item Description</th>
                                <th class="py-3 text-right text-xs font-bold text-slate-500 uppercase w-24">Qty</th>
                                <th class="py-3 text-right text-xs font-bold text-slate-500 uppercase w-32">Price</th>
                                <th class="py-3 text-right text-xs font-bold text-slate-500 uppercase w-32">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($invoice->items as $item)
                            <tr>
                                <td class="py-4 text-sm font-medium text-slate-800">
                                    {{ $item->product_name }}
                                </td>
                                <td class="py-4 text-sm text-slate-600 text-right">{{ $item->quantity }}</td>
                                <td class="py-4 text-sm text-slate-600 text-right">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="py-4 text-sm font-bold text-slate-800 text-right">{{ number_format($item->line_total, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t-2 border-slate-200">
                            <tr>
                                <td colspan="3" class="py-4 text-right text-sm font-medium text-slate-600">Subtotal</td>
                                <td class="py-4 text-right text-sm font-bold text-slate-800">{{ number_format($invoice->subtotal, 2) }}</td>
                            </tr>
                            @if($invoice->tax_amount > 0)
                            <tr>
                                <td colspan="3" class="py-2 text-right text-sm font-medium text-slate-600">Tax</td>
                                <td class="py-2 text-right text-sm font-bold text-slate-800">{{ number_format($invoice->tax_amount, 2) }}</td>
                            </tr>
                            @endif
                            <tr class="text-lg">
                                <td colspan="3" class="py-4 text-right font-bold text-indigo-600">Total Due</td>
                                <td class="py-4 text-right font-bold text-indigo-600">GH₵ {{ number_format($invoice->balance_due, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Payment Action -->
            @if($invoice->status !== 'paid' && $invoice->balance_due > 0)
                <div class="mt-8 border-t border-slate-100 pt-8 text-center">
                    <form action="{{ route('public.invoice.pay', $invoice->uuid) }}" method="POST" x-data="{ amount: {{ $invoice->balance_due }} }">
                        @csrf
                        
                        @if($invoice->allow_partial_payment)
                            <div class="mb-6 max-w-xs mx-auto text-left">
                                <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Amount to Pay (GH₵)</label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-gray-500 sm:text-sm">GH₵</span>
                                    </div>
                                    <input type="number" name="amount" id="amount" 
                                        class="block w-full rounded-md border-gray-300 pl-12 pr-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-3 font-semibold text-lg" 
                                        placeholder="0.00" 
                                        x-model="amount"
                                        min="{{ $invoice->balance_due / 2 }}" 
                                        max="{{ $invoice->balance_due }}"
                                        step="0.01">
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                        <span class="text-gray-500 sm:text-sm">GHS</span>
                                    </div>
                                </div>
                                <p class="mt-2 text-xs text-gray-500">
                                    Total Due: GH₵ {{ number_format($invoice->balance_due, 2) }}
                                </p>
                            </div>
                        @endif

                        <button type="submit" class="inline-flex items-center justify-center px-8 py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition transform hover:-translate-y-0.5 w-full sm:w-auto text-lg">
                            <i class="fas fa-lock mr-3"></i> 
                            Pay <span x-text="amount ? 'GH₵ ' + parseFloat(amount).toFixed(2) : 'Now'">Now</span>
                        </button>
                    </form>
                    <p class="text-xs text-slate-400 mt-4 flex items-center justify-center">
                        <i class="fas fa-shield-alt mr-1"></i> Secured by Paystack
                    </p>
                </div>
            @endif

            @if($invoice->status === 'paid')
                <div class="mt-8 border-t border-slate-100 pt-8 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-emerald-100 rounded-full mb-4">
                        <i class="fas fa-check text-2xl text-emerald-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-emerald-800">Payment Received</h3>
                    <p class="text-slate-500 mt-1">This invoice has been fully paid. Thank you for your business!</p>
                </div>
            @endif
        </div>
        
        <!-- Footer -->
        <div class="bg-gray-50 px-8 py-6 text-center border-t border-gray-100">
            <p class="text-xs text-slate-400">&copy; {{ date('Y') }} {{ $invoice->branch->business->name }}. All rights reserved.</p>
        </div>
    </div>
</div>

</body>
</html>
