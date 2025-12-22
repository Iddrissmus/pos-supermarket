@extends('layouts.app')

@section('title', 'Product Movement Report')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-teal-600 to-cyan-600 rounded-lg shadow-lg p-6 mb-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">Product Movement Tracking</h1>
                <p class="text-teal-100">Monitor stock receipts, sales, transfers, and adjustments</p>
            </div>
            <a href="{{ route('product-reports.index') }}" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        @foreach($summary as $item)
            @php
                $colors = [
                    'receipt' => ['bg' => 'bg-green-50', 'text' => 'text-green-600', 'icon' => 'fa-truck-loading'],
                    'sale' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600', 'icon' => 'fa-shopping-cart'],
                    'transfer_in' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-600', 'icon' => 'fa-arrow-down'],
                    'transfer_out' => ['bg' => 'bg-orange-50', 'text' => 'text-orange-600', 'icon' => 'fa-arrow-up'],
                    'adjustment' => ['bg' => 'bg-yellow-50', 'text' => 'text-yellow-600', 'icon' => 'fa-adjust'],
                ];
                $color = $colors[$item->type] ?? ['bg' => 'bg-gray-50', 'text' => 'text-gray-600', 'icon' => 'fa-box'];
            @endphp
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">{{ ucfirst(str_replace('_', ' ', $item->type)) }}</p>
                        <p class="text-2xl font-bold {{ $color['text'] }}">{{ number_format($item->count) }}</p>
                        <p class="text-xs text-gray-500 mt-1">Qty: {{ number_format(abs($item->total_quantity)) }}</p>
                    </div>
                    <div class="{{ $color['bg'] }} rounded-full p-4">
                        <i class="fas {{ $color['icon'] }} {{ $color['text'] }} text-xl"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" action="{{ route('product-reports.movement') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                <input type="date" name="start_date" value="{{ request('start_date', $dateRange['start_formatted']) }}" 
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-500 focus:ring-teal-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                <input type="date" name="end_date" value="{{ request('end_date', $dateRange['end_formatted']) }}" 
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-500 focus:ring-teal-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Product</label>
                <select name="product_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-500 focus:ring-teal-500">
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
                    <label class="block text-sm font-medium text-gray-700 mb-2">Branch</label>
                    <select name="branch_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-500 focus:ring-teal-500">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
            
            <div class="flex items-end">
                <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-filter mr-2"></i>Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Movement Timeline -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">
                <i class="fas fa-history text-teal-500 mr-2"></i>Movement Timeline
            </h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Date & Time
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Type
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Product
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Branch
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Quantity
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" colspan="2">
                            Note
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($movements as $movement)
                        @php
                            $typeColors = [
                                'receipt' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'icon' => 'fa-truck-loading'],
                                'sale' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'icon' => 'fa-shopping-cart'],
                                'transfer_in' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'icon' => 'fa-arrow-down'],
                                'transfer_out' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'icon' => 'fa-arrow-up'],
                                'adjustment' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'icon' => 'fa-adjust'],
                            ];
                            $color = $typeColors[$movement->action] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'icon' => 'fa-box'];
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $movement->created_at->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $movement->created_at->format('h:i A') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $color['bg'] }} {{ $color['text'] }}">
                                    <i class="fas {{ $color['icon'] }} mr-1"></i>
                                    {{ ucfirst(str_replace('_', ' ', $movement->action)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $movement->product->name }}</div>
                                <div class="text-xs text-gray-500">{{ $movement->product->barcode }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $movement->branch->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="text-sm font-semibold text-blue-600">
                                    {{ number_format($movement->quantity) }}
                                </span>
                            </td>
                            <td class="px-6 py-4" colspan="2">
                                @if($movement->note)
                                    <div class="text-sm text-gray-600">{{ $movement->note }}</div>
                                @else
                                    <div class="text-sm text-gray-400">-</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-3 block text-gray-300"></i>
                                No movement records found for the selected period
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($movements->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $movements->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
