@extends('layouts.app')

@section('title', 'Invoice Details')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

    <!-- Page Header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 flex items-center">
                Invoice #{{ $invoice->invoice_number }}
                <span class="ml-3 text-sm px-2.5 py-0.5 rounded-full 
                    {{ $invoice->status == 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">
                    {{ ucfirst($invoice->status) }}
                </span>
            </h1>
        </div>
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <a href="{{ route('invoices.index') }}" class="btn bg-white border-gray-200 hover:border-gray-300 text-gray-600">
                Back to List
            </a>
            <a href="{{ route('public.invoice.show', $invoice->uuid) }}" target="_blank" class="btn bg-white border-gray-200 hover:border-gray-300 text-indigo-600">
                <i class="fas fa-external-link-alt mr-2"></i> View Public Page
            </a>
            @if($invoice->status !== 'paid')
                <form action="{{ route('invoices.send', $invoice->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn bg-indigo-500 hover:bg-indigo-600 text-white">
                        <i class="fas fa-paper-plane mr-2"></i> Send Payment Link
                    </button>
                </form>
            @endif
        </div>
    </div>
    
    @if(session('success'))
        <div class="mb-6 bg-emerald-100 border border-emerald-200 text-emerald-800 px-4 py-3 rounded relative">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-12 gap-6">

        <!-- Invoice Preview -->
        <div class="col-span-12 lg:col-span-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 relative overflow-hidden">
                @if($invoice->status === 'paid')
                    <div class="absolute top-12 right-12 transform rotate-45 pointer-events-none z-0 opacity-10">
                        <span class="text-8xl font-bold text-emerald-600 uppercase border-8 border-emerald-600 px-6 py-2 rounded-xl">PAID</span>
                    </div>
                @endif
                <!-- Invoice Header -->
                <div class="flex justify-between items-start mb-8 border-b border-gray-100 pb-8">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800 mb-1">{{ $invoice->branch->business->name }}</h2>
                        <div class="text-sm text-gray-500">
                            {{ $invoice->branch->name }}<br>
                            {{ $invoice->branch->phone }}
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-500 mb-1">Invoice Number</div>
                        <div class="text-lg font-bold text-gray-800">{{ $invoice->invoice_number }}</div>
                        <div class="text-sm text-gray-500 mt-2">Date: {{ $invoice->created_at->format('M d, Y') }}</div>
                        <div class="text-sm text-gray-500">Due: {{ $invoice->due_date->format('M d, Y') }}</div>
                    </div>
                </div>

                <!-- Bill To -->
                <div class="mb-8 p-6 bg-gray-50 rounded-lg">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Bill To</h3>
                    <div class="font-bold text-gray-800 text-lg">{{ $invoice->customer->name ?? 'Guest Client' }}</div>
                    <div class="text-gray-600">{{ $invoice->customer_email }}</div>
                    <div class="text-gray-600">{{ $invoice->customer_phone }}</div>
                </div>

                <!-- Items -->
                <div class="overflow-x-auto">
                    <table class="table-auto w-full mb-8">
                        <thead class="text-xs font-semibold uppercase text-gray-500 border-b border-gray-200">
                            <tr>
                                <th class="py-3 text-left">Description</th>
                                <th class="py-3 text-right">Qty</th>
                                <th class="py-3 text-right">Price</th>
                                <th class="py-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($invoice->items as $item)
                            <tr>
                                <td class="py-3 text-sm font-medium text-gray-800">{{ $item->product->name }}</td>
                                <td class="py-3 text-sm text-gray-600 text-right">{{ $item->quantity }}</td>
                                <td class="py-3 text-sm text-gray-600 text-right">{{ number_format($item->unit_price, 2) }}</td>
                                <td class="py-3 text-sm font-bold text-gray-800 text-right">{{ number_format($item->line_total, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Totals -->
                <div class="flex justify-end border-t border-gray-100 pt-8">
                    <div class="w-full max-w-xs space-y-3">
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Subtotal</span>
                            <span>{{ number_format($invoice->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-gray-600">
                            <span>Tax</span>
                            <span>{{ number_format($invoice->tax_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between font-bold text-gray-800 text-lg pt-3 border-t border-gray-200">
                            <span>Total</span>
                            <span>GH₵ {{ number_format($invoice->total_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between font-bold text-indigo-600 pt-1">
                            <span>Balance Due</span>
                            <span>GH₵ {{ number_format($invoice->balance_due, 2) }}</span>
                        </div>
                    </div>
                </div>
                
                @if($invoice->notes)
                <div class="mt-8 pt-8 border-t border-gray-100">
                    <h4 class="text-sm font-bold text-gray-800 mb-2">Notes</h4>
                    <p class="text-sm text-gray-600">{{ $invoice->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-span-12 lg:col-span-4 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="font-bold text-gray-800 mb-4">Invoice Activity</h3>
                <ol class="relative border-l border-gray-200 ml-3">
                    <li class="mb-6 ml-6">
                         <span class="absolute flex items-center justify-center w-6 h-6 bg-blue-100 rounded-full -left-3 ring-8 ring-white">
                            <i class="fas fa-file-invoice text-blue-600 text-xs"></i>
                        </span>
                        <h3 class="flex items-center mb-1 text-sm font-semibold text-gray-900">Created</h3>
                        <time class="block mb-2 text-xs font-normal leading-none text-gray-400">
                            {{ $invoice->created_at->format('M d, Y H:i') }}
                        </time>
                        <p class="text-xs text-gray-500">Invoice drafted by {{ $invoice->createdBy->name ?? 'System' }}.</p>
                    </li>
                    
                    @if($invoice->sent_at)
                    <li class="mb-6 ml-6">
                         <span class="absolute flex items-center justify-center w-6 h-6 bg-indigo-100 rounded-full -left-3 ring-8 ring-white">
                            <i class="fas fa-envelope text-indigo-600 text-xs"></i>
                        </span>
                        <h3 class="flex items-center mb-1 text-sm font-semibold text-gray-900">Sent</h3>
                        <time class="block mb-2 text-xs font-normal leading-none text-gray-400">
                            {{ $invoice->sent_at->format('M d, Y H:i') }}
                        </time>
                        <p class="text-xs text-gray-500">Payment link sent to customer.</p>
                    </li>
                    @endif
                    
                    @if($invoice->paid_at)
                    <li class="mb-6 ml-6">
                         <span class="absolute flex items-center justify-center w-6 h-6 bg-emerald-100 rounded-full -left-3 ring-8 ring-white">
                            <i class="fas fa-check text-emerald-600 text-xs"></i>
                        </span>
                        <h3 class="flex items-center mb-1 text-sm font-semibold text-gray-900">Paid</h3>
                        <time class="block mb-2 text-xs font-normal leading-none text-gray-400">
                            {{ $invoice->paid_at->format('M d, Y H:i') }}
                        </time>
                        <p class="text-xs text-gray-500">Payment successfully processed.</p>
                    </li>
                    @endif
                </ol>
            </div>
            
             <div class="bg-indigo-50 rounded-xl border border-indigo-100 p-6">
                <h3 class="font-bold text-indigo-900 mb-2">Payment Link</h3>
                <div class="flex">
                    <input type="text" readonly value="{{ route('public.invoice.show', $invoice->uuid) }}" class="flex-1 text-xs border-indigo-200 rounded-l-md bg-white text-gray-600 focus:ring-0">
                    <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 rounded-r-md text-xs font-bold" onclick="navigator.clipboard.writeText('{{ route('public.invoice.show', $invoice->uuid) }}'); alert('Copied!');">
                        Copy
                    </button>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
