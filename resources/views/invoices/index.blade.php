@extends('layouts.app')

@section('title', 'Invoices Dashboard')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

    <!-- Page Header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl font-bold text-slate-800">Invoices</h1>
            <p class="mt-1 text-sm text-slate-500">Manage your billing and payments.</p>
        </div>
            <a href="{{ route('invoices.create') }}" class="btn bg-indigo-500 hover:bg-indigo-600 text-white inline-flex items-center justify-center px-4 py-2 rounded-sm shadow-sm transition-colors duration-150 ease-in-out">
                <svg class="w-4 h-4 fill-current shrink-0 mr-2" viewBox="0 0 16 16">
                    <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                </svg>
                <span class="font-medium">Create Invoice</span>
            </a>
    </div>

    <!-- Dashboard Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Total Revenue -->
        <div class="bg-white border border-slate-200 rounded-lg p-5 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Total Paid</div>
                <div class="text-2xl font-bold text-slate-800">₵{{ number_format($stats['total_revenue'], 2) }}</div>
            </div>
            <div class="p-3 bg-emerald-50 rounded-full">
                <svg class="w-6 h-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <!-- Pending -->
        <div class="bg-white border border-slate-200 rounded-lg p-5 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Pending Amount</div>
                <div class="text-2xl font-bold text-slate-800">₵{{ number_format($stats['pending_amount'], 2) }}</div>
            </div>
            <div class="p-3 bg-blue-50 rounded-full">
                <svg class="w-6 h-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <!-- Overdue -->
        <div class="bg-white border border-slate-200 rounded-lg p-5 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Overdue</div>
                <div class="text-2xl font-bold text-slate-800">₵{{ number_format($stats['overdue_amount'], 2) }}</div>
            </div>
            <div class="p-3 bg-rose-50 rounded-full">
                <svg class="w-6 h-6 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 px-4 py-3 rounded border bg-emerald-50 border-emerald-200 text-emerald-600 flex items-center shadow-sm">
            <svg class="w-5 h-5 mr-3 fill-current" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zm3.707 7.293l-4 4a1 1 0 01-1.414 0l-2-2a1 1 0 111.414-1.414L9 11.586l3.293-3.293a1 1 0 111.414 1.414z"/></svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Main Card -->
    <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
        
        <!-- Toolbar -->
        <div class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="font-semibold text-slate-800 text-lg">Recent Invoices</div>
            <div class="relative">
                 <input type="text" class="form-input pl-9 text-sm w-full sm:w-64 border-slate-300 focus:border-indigo-300 rounded-md" placeholder="Search invoice or customer...">
                 <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                     <svg class="w-4 h-4 fill-current text-slate-400" viewBox="0 0 16 16">
                         <path d="M7 14c-3.86 0-7-3.14-7-7s3.14-7 7-7 7 3.14 7 7-3.14 7-7 7zM7 2C4.243 2 2 4.243 2 7s2.243 5 5 5 5-2.243 5-5-2.243-5-5-5z" />
                     </svg>
                 </div>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="table-auto w-full text-left">
                <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-4 whitespace-nowrap">
                            <div class="font-semibold">Details</div>
                        </th>
                        <th class="px-5 py-4 whitespace-nowrap">
                            <div class="font-semibold">Customer</div>
                        </th>
                        <th class="px-5 py-4 whitespace-nowrap text-right">
                           <div class="font-semibold">Amount</div>
                        </th>
                        <th class="px-5 py-4 whitespace-nowrap">
                            <div class="font-semibold">Status</div>
                        </th>
                        <th class="px-5 py-4 whitespace-nowrap text-right">
                             <div class="font-semibold">Actions</div>
                        </th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-slate-100">
                    @forelse($invoices as $invoice)
                        <tr class="hover:bg-slate-50 transition-colors duration-150 ease-in-out group">
                            <!-- Details -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                <a href="{{ route('invoices.show', $invoice->id) }}" class="text-indigo-600 hover:text-indigo-800 font-bold block">
                                    {{ $invoice->invoice_number }}
                                </a>
                                <div class="text-slate-500 text-xs mt-1">
                                    Issued: {{ $invoice->created_at->format('M d, Y') }}
                                </div>
                            </td>
                            
                            <!-- Customer -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-500 flex items-center justify-center font-bold text-xs mr-3">
                                        {{ substr($invoice->customer->name ?? 'G', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-slate-800">{{ $invoice->customer->name ?? 'Guest Customer' }}</div>
                                        <div class="text-xs text-slate-500">{{ $invoice->customer_email }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Amount -->
                            <td class="px-5 py-4 whitespace-nowrap text-right">
                                <div class="font-bold text-slate-800 text-base">₵{{ number_format($invoice->total_amount, 2) }}</div>
                                @if($invoice->balance_due > 0)
                                    <div class="text-xs text-rose-500 mt-0.5">Due: ₵{{ number_format($invoice->balance_due, 2) }}</div>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                @php
                                    $statusConfig = [
                                        'draft' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-500', 'icon' => 'fa-file'],
                                        'sent' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'icon' => 'fa-paper-plane'],
                                        'paid' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'icon' => 'fa-check-circle'],
                                        'overdue' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-600', 'icon' => 'fa-exclamation-circle'],
                                        'cancelled' => ['bg' => 'bg-slate-100', 'text' => 'text-slate-400', 'icon' => 'fa-ban'],
                                        'partially_paid' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'icon' => 'fa-adjust']
                                    ];
                                    $config = $statusConfig[$invoice->status] ?? $statusConfig['draft'];
                                @endphp
                                <div class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $config['bg'] }} {{ $config['text'] }} border border-transparent">
                                    <i class="fas {{ $config['icon'] }} mr-1.5 opacity-70"></i>
                                    {{ ucfirst(str_replace('_', ' ', $invoice->status)) }}
                                </div>
                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end space-x-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('public.invoice.show', $invoice->uuid) }}" target="_blank" class="text-slate-400 hover:text-slate-600" title="Public Link">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                    
                                    @if($invoice->status !== 'paid')
                                    <form action="{{ route('invoices.send', $invoice->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button class="text-indigo-400 hover:text-indigo-600" title="Send Email">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    </form>
                                    @endif

                                    <a href="{{ route('invoices.show', $invoice->id) }}" class="text-slate-400 hover:text-indigo-600" title="View Details">
                                        <i class="far fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="h-16 w-16 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                                        <svg class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-medium text-slate-900">No invoices found</h3>
                                    <p class="mt-1 text-sm text-slate-500">Get started by creating a new invoice.</p>
                                    <div class="mt-6">
                                        <a href="{{ route('invoices.create') }}" class="btn bg-indigo-500 hover:bg-indigo-600 text-white shadow-sm">
                                            <svg class="w-4 h-4 fill-current opacity-50 shrink-0 mr-2" viewBox="0 0 16 16">
                                                <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                                            </svg>
                                            Create Invoice
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
        <div class="px-5 py-3 border-t border-slate-200 bg-slate-50 rounded-b-lg">
            {{ $invoices->links() }}
        </div>
        @endif

    </div>
</div>
@endsection
