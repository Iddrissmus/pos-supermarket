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
                <button type="button" onclick="document.getElementById('send-invoice-modal').classList.remove('hidden')" class="btn bg-indigo-500 hover:bg-indigo-600 text-white shadow-lg shadow-indigo-500/20 transition-all">
                    <i class="fas fa-paper-plane mr-2"></i> Send Invoice
                </button>
            @endif
        </div>
    </div>

    <!-- Send Invoice Modal -->
    <div id="send-invoice-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="document.getElementById('send-invoice-modal').classList.add('hidden')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-middle bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-gray-100">
                <form action="{{ route('invoices.send', $invoice->id) }}" method="POST">
                    @csrf
                    <div class="bg-white px-6 pt-6 pb-4 sm:p-8 sm:pb-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-bold text-gray-900" id="modal-title">Send Payment Link</h3>
                            <button type="button" onclick="document.getElementById('send-invoice-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-500">
                                <i class="fas fa-times text-lg"></i>
                            </button>
                        </div>
                        
                        <div class="space-y-6">
                            <p class="text-sm text-gray-500">How would you like to deliver this invoice to <span class="font-semibold text-gray-900">{{ $invoice->customer->name ?? $invoice->customer_name }}</span>?</p>
                            
                            <div class="grid grid-cols-1 gap-4">
                                <label class="relative flex items-center p-4 rounded-xl border-2 border-gray-100 cursor-pointer hover:bg-indigo-50 hover:border-indigo-200 transition-all group">
                                    <input type="checkbox" name="channels[]" value="email" checked class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                    <div class="ml-4 flex items-center gap-3">
                                        <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-envelope"></i>
                                        </div>
                                        <div>
                                            <span class="block text-sm font-bold text-gray-900 uppercase tracking-tight">Email</span>
                                            <span class="block text-xs text-gray-500">{{ $invoice->customer_email }}</span>
                                        </div>
                                    </div>
                                </label>

                                <label class="relative flex items-center p-4 rounded-xl border-2 border-gray-100 cursor-pointer hover:bg-emerald-50 hover:border-emerald-200 transition-all group">
                                    <input type="checkbox" name="channels[]" value="sms" @if($invoice->delivery_type === 'instant') checked @endif class="w-5 h-5 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                                    <div class="ml-4 flex items-center gap-3">
                                        <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-sms"></i>
                                        </div>
                                        <div>
                                            <span class="block text-sm font-bold text-gray-900 uppercase tracking-tight">SMS Notification</span>
                                            <span class="block text-xs text-gray-500">{{ $invoice->customer_phone ?? 'Phone number missing' }}</span>
                                        </div>
                                    </div>
                                </label>
                            </div>

                            @if($invoice->delivery_type === 'scheduled')
                            <div class="p-3 bg-amber-50 border border-amber-100 rounded-lg flex gap-3 italic text-amber-800 text-xs">
                                <i class="fas fa-clock mt-0.5"></i>
                                <span>Note: This invoice is scheduled for {{ $invoice->scheduled_send_date->format('M d, Y') }}. Sending now will override the schedule.</span>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:gap-3">
                        <button type="submit" class="w-full inline-flex justify-center rounded-xl px-6 py-3 bg-indigo-600 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 hover:bg-indigo-700 transition-all outline-none">
                            <i class="fas fa-paper-plane mr-2 mt-0.5"></i> Send Now
                        </button>
                        <button type="button" onclick="document.getElementById('send-invoice-modal').classList.add('hidden')" class="mt-3 sm:mt-0 w-full inline-flex justify-center rounded-xl px-6 py-3 bg-white border-2 border-gray-200 text-sm font-bold text-gray-600 hover:bg-gray-50 transition-all outline-none">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
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
                                <td class="py-3 text-sm font-medium text-gray-800">{{ $item->product_name }}</td>
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
                <div class="flex mb-4">
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
