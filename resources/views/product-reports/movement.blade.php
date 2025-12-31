@extends('layouts.app')

@section('title', 'Product Movement Report')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto space-y-8">
    
    <!-- Modern Header -->
    <div class="relative bg-gradient-to-r from-teal-600 to-cyan-700 rounded-xl shadow-lg overflow-hidden">
        <div class="absolute inset-0 bg-white/10" style="background-image: radial-gradient(circle at 10% 20%, rgba(255,255,255,0.1) 0%, transparent 20%), radial-gradient(circle at 90% 80%, rgba(255,255,255,0.1) 0%, transparent 20%);"></div>
        <div class="relative p-8 flex flex-col md:flex-row items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-bold text-white tracking-tight flex items-center">
                    <i class="fas fa-exchange-alt mr-3 text-teal-200"></i> Stock Movement
                </h1>
                <p class="mt-2 text-teal-100 text-lg opacity-90 max-w-2xl">
                    Full audit trail of stock receipts, sales, transfers, and inventory adjustments.
                </p>
            </div>
            <div>
                <a href="{{ route('product-reports.index') }}" class="inline-flex items-center px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg transition-colors font-medium backdrop-blur-sm border border-white/10">
                    <i class="fas fa-arrow-left mr-2 opacity-80"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        @foreach($summary as $item)
            @php
                $colors = [
                    'receipt' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'border' => 'border-emerald-100', 'icon' => 'fa-truck-loading'],
                    'sale' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'border' => 'border-blue-100', 'icon' => 'fa-shopping-cart'],
                    'transfer_in' => ['bg' => 'bg-violet-50', 'text' => 'text-violet-600', 'border' => 'border-violet-100', 'icon' => 'fa-sign-in-alt'],
                    'transfer_out' => ['bg' => 'bg-orange-50', 'text' => 'text-orange-600', 'border' => 'border-orange-100', 'icon' => 'fa-sign-out-alt'],
                    'adjustment' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600', 'border' => 'border-amber-100', 'icon' => 'fa-sliders-h'],
                ];
                $color = $colors[$item->type] ?? ['bg' => 'bg-gray-50', 'text' => 'text-gray-600', 'border' => 'border-gray-100', 'icon' => 'fa-box'];
            @endphp
            <div class="bg-white rounded-xl shadow-sm border {{ $color['border'] }} p-5 flex items-center justify-between">
                <div>
                     <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ ucfirst(str_replace('_', ' ', $item->type)) }}</p>
                     <p class="text-2xl font-bold {{ $color['text'] }} mt-1">{{ number_format($item->count) }}</p>
                     <p class="text-xs text-gray-500 font-medium">Vol: {{ number_format(abs($item->total_quantity)) }} items</p>
                </div>
                <div class="{{ $color['bg'] }} w-10 h-10 rounded-lg flex items-center justify-center">
                    <i class="fas {{ $color['icon'] }} {{ $color['text'] }} text-lg"></i>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="GET" action="{{ route('product-reports.movement') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ request('start_date', $dateRange['start_formatted']) }}" 
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm">
            </div>
            
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ request('end_date', $dateRange['end_formatted']) }}" 
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm">
            </div>
            
            <div class="lg:col-span-1">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Product</label>
                <select name="product_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm">
                    <option value="">All Products</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            @if(auth()->user()->role === 'business_admin' && $branches->count() > 0)
                <div>
                     <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Branch</label>
                    <select name="branch_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
            
            <div class="flex items-end lg:col-span-{{ auth()->user()->role === 'business_admin' ? '1' : '2' }}">
                <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white px-4 py-2.5 rounded-lg transition-colors font-medium text-sm flex items-center justify-center">
                    <i class="fas fa-filter mr-2"></i> Filter Records
                </button>
            </div>
        </form>
    </div>

    <!-- Movement Timeline Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/50">
            <h2 class="text-lg font-bold text-gray-800">
                Movement History
            </h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-40">Timestamp</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-32">Action</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Product ID</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Source</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider w-24">Change</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Reference / Note</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($movements as $movement)
                        @php
                            $typeColors = [
                                'receipt' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-800', 'icon' => 'fa-truck-loading'],
                                'sale' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'icon' => 'fa-shopping-cart'],
                                'transfer_in' => ['bg' => 'bg-violet-100', 'text' => 'text-violet-800', 'icon' => 'fa-sign-in-alt'],
                                'transfer_out' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'icon' => 'fa-sign-out-alt'],
                                'adjustment' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-800', 'icon' => 'fa-wrench'],
                            ];
                            $color = $typeColors[$movement->action] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'icon' => 'fa-circle'];
                        @endphp
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-gray-900 font-medium">{{ $movement->created_at->format('M d, Y') }}</span>
                                <span class="text-xs text-gray-500 block">{{ $movement->created_at->format('h:i A') }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wide {{ $color['bg'] }} {{ $color['text'] }}">
                                    <i class="fas {{ $color['icon'] }} mr-1.5 opacity-75"></i>
                                    {{ ucfirst(str_replace('_', ' ', $movement->action)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-gray-900">{{ $movement->product->name }}</div>
                                <div class="text-xs text-gray-500 font-mono">{{ $movement->product->barcode }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-700">{{ $movement->branch->display_label }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="text-sm font-bold {{ $movement->quantity > 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $movement->quantity > 0 ? '+' : '' }}{{ number_format($movement->quantity) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($movement->note)
                                    <span class="text-sm text-gray-600">{{ $movement->note }}</span>
                                @else
                                    <span class="text-xs text-gray-400 italic">No notes recorded</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center text-gray-500">
                                <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-history text-gray-300 text-2xl"></i>
                                </div>
                                <p class="font-medium">No movement history found</p>
                                <p class="text-sm mt-1">Try changing the date range or filters.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($movements->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $movements->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
