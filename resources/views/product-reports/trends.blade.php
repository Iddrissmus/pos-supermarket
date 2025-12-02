@extends('layouts.app')

@section('title', 'Product Sales Trends')

@section('content')
<div class="p-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg shadow-lg p-6 mb-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">Sales Trends Analysis</h1>
                <p class="text-purple-100">Track sales patterns over time - daily, weekly, or monthly</p>
            </div>
            <a href="{{ route('product-reports.index') }}" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="GET" action="{{ route('product-reports.trends') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                <input type="date" name="start_date" value="{{ request('start_date', $dateRange['start_formatted']) }}" 
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                <input type="date" name="end_date" value="{{ request('end_date', $dateRange['end_formatted']) }}" 
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Group By</label>
                <select name="group_by" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500">
                    <option value="day" {{ $groupBy == 'day' ? 'selected' : '' }}>Daily</option>
                    <option value="week" {{ $groupBy == 'week' ? 'selected' : '' }}>Weekly</option>
                    <option value="month" {{ $groupBy == 'month' ? 'selected' : '' }}>Monthly</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Product (Optional)</label>
                <select name="product_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500">
                    <option value="">All Products</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors">
                    <i class="fas fa-filter mr-2"></i>Apply Filters
                </button>
            </div>
        </form>
    </div>

    <!-- Trends Chart -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">
            <i class="fas fa-chart-area text-purple-500 mr-2"></i>Sales Trend Over Time
        </h2>
        
        @if($trends->count() > 0)
            <div class="h-96">
                <canvas id="trendsChart"></canvas>
            </div>
        @else
            <div class="text-center py-12 text-gray-500">
                <i class="fas fa-chart-line text-6xl mb-4 text-gray-300"></i>
                <p>No trend data available for the selected period</p>
            </div>
        @endif
    </div>

    <!-- Trends Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">
                <i class="fas fa-table text-purple-500 mr-2"></i>Detailed Trends Data
            </h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Period
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Transactions
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Quantity Sold
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Revenue
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Profit
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Avg Transaction
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($trends as $trend)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $trend->period }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm text-gray-900">{{ number_format($trend->transaction_count) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm font-semibold text-gray-900">{{ number_format($trend->total_quantity) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm font-semibold text-green-600">
                                    GH₵ {{ number_format($trend->total_revenue, 2) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm font-semibold {{ $trend->total_profit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    GH₵ {{ number_format($trend->total_profit, 2) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm text-gray-600">
                                    GH₵ {{ $trend->transaction_count > 0 ? number_format($trend->total_revenue / $trend->transaction_count, 2) : '0.00' }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-3 block text-gray-300"></i>
                                No trend data found for the selected period
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($trends->count() > 0)
                    <tfoot class="bg-gray-50 font-semibold">
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-900">Total / Average</td>
                            <td class="px-6 py-4 text-sm text-gray-900 text-right">
                                {{ number_format($trends->sum('transaction_count')) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 text-right">
                                {{ number_format($trends->sum('total_quantity')) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-green-600 text-right">
                                GH₵ {{ number_format($trends->sum('total_revenue'), 2) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-green-600 text-right">
                                GH₵ {{ number_format($trends->sum('total_profit'), 2) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 text-right">
                                GH₵ {{ $trends->count() > 0 ? number_format($trends->sum('total_revenue') / $trends->count(), 2) : '0.00' }}
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- Top Performing Products -->
    @if($topProducts->count() > 0)
        <div class="bg-white rounded-lg shadow-md p-6 mt-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">
                <i class="fas fa-star text-yellow-500 mr-2"></i>Top Performing Products in This Period
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($topProducts->take(6) as $index => $product)
                    <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-lg p-4 border border-purple-200">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-900 text-sm">{{ $product->name }}</h3>
                                <p class="text-xs text-gray-500">{{ $product->barcode }}</p>
                            </div>
                            <span class="text-2xl">{{ $index == 0 ? '🥇' : ($index == 1 ? '🥈' : ($index == 2 ? '🥉' : '⭐')) }}</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mt-3">
                            <div>
                                <p class="text-xs text-gray-500">Quantity</p>
                                <p class="text-sm font-bold text-purple-600">{{ number_format($product->total_quantity) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Revenue</p>
                                <p class="text-sm font-bold text-green-600">GH₵ {{ number_format($product->total_revenue, 0) }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

@if($trends->count() > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('trendsChart').getContext('2d');
    const trendsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($trends->pluck('period')->toArray()) !!},
            datasets: [{
                label: 'Revenue (GH₵)',
                data: {!! json_encode($trends->pluck('total_revenue')->toArray()) !!},
                borderColor: 'rgb(147, 51, 234)',
                backgroundColor: 'rgba(147, 51, 234, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Profit (GH₵)',
                data: {!! json_encode($trends->pluck('total_profit')->toArray()) !!},
                borderColor: 'rgb(16, 185, 129)',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'GH₵ ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
</script>
@endif
@endsection
