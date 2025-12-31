@extends('layouts.app')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto space-y-8">
    
    <!-- Modern Header -->
    <div class="relative bg-gradient-to-r from-emerald-600 to-green-700 rounded-xl shadow-lg overflow-hidden">
        <div class="absolute inset-0 bg-white/10" style="background-image: radial-gradient(circle at 10% 20%, rgba(255,255,255,0.1) 0%, transparent 20%), radial-gradient(circle at 90% 80%, rgba(255,255,255,0.1) 0%, transparent 20%);"></div>
        <div class="relative p-8 flex flex-col md:flex-row items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-bold text-white tracking-tight flex items-center">
                    <i class="fas fa-clipboard-list mr-3 text-emerald-200"></i> Stock Receipts
                </h1>
                <p class="mt-2 text-emerald-100 text-lg opacity-90 max-w-2xl">
                    Track and manage incoming inventory shipments from suppliers.
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                 <a href="{{ route('stock-receipts.create') }}" class="px-4 py-2 bg-white text-emerald-700 hover:bg-emerald-50 rounded-lg transition-colors font-bold shadow-sm flex items-center">
                    <i class="fas fa-plus mr-2"></i> Receive Stock
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        
        @if(session('success'))
            <div class="p-4 bg-green-50 border-b border-green-100 flex items-center gap-3">
                <i class="fas fa-check-circle text-green-500"></i>
                <p class="text-green-800 font-medium">{{ session('success') }}</p>
            </div>
        @endif

        @if($receipts->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Receipt Info</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Branch</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Supplier</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Amount</th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @foreach($receipts as $receipt)
                        <tr class="hover:bg-gray-50/80 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-emerald-100 rounded-lg flex items-center justify-center text-emerald-600 font-bold text-xs">
                                        <i class="fas fa-file-invoice"></i>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-bold text-gray-900">
                                            {{ $receipt->receipt_number }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $receipt->received_at->format('M d, Y') }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-700">
                                    {{ optional($receipt->branch)->display_label ?? 'Unassigned' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="text-sm font-medium text-gray-900">{{ $receipt->supplier->name }}</div>
                                    @if($receipt->supplier->is_central)
                                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase bg-blue-50 text-blue-600 rounded border border-blue-100">Central</span>
                                    @else
                                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase bg-green-50 text-green-600 rounded border border-green-100">Local</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm font-bold text-gray-900">
                                    ₵{{ number_format($receipt->total_amount, 2) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 border border-green-200">
                                    Completed
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('stock-receipts.show', $receipt) }}" 
                                   class="text-emerald-600 hover:text-emerald-900 inline-flex items-center gap-1 transition-colors">
                                    <span class="hidden group-hover:inline">View Details</span>
                                    <i class="fas fa-chevron-right ml-1"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($receipts->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $receipts->links() }}
                </div>
            @endif

        @else
            <!-- Empty State -->
            <div class="text-center py-16">
                <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                     <i class="fas fa-clipboard text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">No stock receipts found</h3>
                <p class="text-gray-500 mb-6">Start by receiving inventory from your suppliers.</p>
                <a href="{{ route('stock-receipts.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-lg transition-colors">
                    <i class="fas fa-plus mr-2"></i> Receive First Stock
                </a>
            </div>
        @endif
    </div>
</div>
@endsection