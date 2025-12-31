@extends('layouts.app')

@section('title', 'Product Sales Trends')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto space-y-8">
    
    <!-- Modern Header -->
    <div class="relative bg-gradient-to-r from-purple-600 to-pink-600 rounded-xl shadow-lg overflow-hidden">
        <div class="absolute inset-0 bg-white/10" style="background-image: radial-gradient(circle at 10% 20%, rgba(255,255,255,0.1) 0%, transparent 20%), radial-gradient(circle at 90% 80%, rgba(255,255,255,0.1) 0%, transparent 20%);"></div>
        <div class="relative p-8 flex flex-col md:flex-row items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-bold text-white tracking-tight flex items-center">
                    <i class="fas fa-chart-area mr-3 text-purple-200"></i> Sales Trend Analysis
                </h1>
                <p class="mt-2 text-purple-100 text-lg opacity-90 max-w-2xl">
                    Visualize sales patterns, identify peak periods, and track growth momentum.
                </p>
            </div>
            <div>
                <a href="{{ route('product-reports.index') }}" class="inline-flex items-center px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg transition-colors font-medium backdrop-blur-sm border border-white/10">
                    <i class="fas fa-arrow-left mr-2 opacity-80"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form method="GET" action="{{ route('product-reports.trends') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Start Date</label>
                <input type="date" name="start_date" value="{{ request('start_date', $dateRange['start_formatted']) }}" 
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500 text-sm">
            </div>
            
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">End Date</label>
                <input type="date" name="end_date" value="{{ request('end_date', $dateRange['end_formatted']) }}" 
                       class="w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500 text-sm">
            </div>
            
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Interval</label>
                <select name="group_by" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500 text-sm">
                    <option value="day" {{ $groupBy == 'day' ? 'selected' : '' }}>Daily</option>
                    <option value="week" {{ $groupBy == 'week' ? 'selected' : '' }}>Weekly</option>
                    <option value="month" {{ $groupBy == 'month' ? 'selected' : '' }}>Monthly</option>
                </select>
            </div>
            
            <div class="lg:col-span-1">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Product (Optional)</label>
                <select name="product_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-purple-500 focus:ring-purple-500 text-sm">
                    <option value="">All Products</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex items-end lg:col-span-1">
                <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2.5 rounded-lg transition-colors font-medium text-sm flex items-center justify-center">
                    <i class="fas fa-filter mr-2"></i> Update Chart
                </button>
            </div>
        </form>
    </div>

    <!-- Chart Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between mb-6">
             <h2 class="text-lg font-bold text-gray-800">
                Revenue vs Profit Trend
            </h2>
            <div class="flex items-center space-x-4">
                <div class="flex items-center">
                    <span class="w-3 h-3 rounded-full bg-purple-500 mr-2"></span>
                    <span class="text-sm text-gray-600">Revenue</span>
                </div>
                <div class="flex items-center">
                    <span class="w-3 h-3 rounded-full bg-emerald-500 mr-2"></span>
                    <span class="text-sm text-gray-600">Profit</span>
                </div>
            </div>
        </div>
        
        @if($trends->count() > 0)
            <div class="h-96 w-full">
                <canvas id="trendsChart"></canvas>
            </div>
        @else
            <div class="h-64 flex flex-col items-center justify-center text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-chart-line text-gray-300 text-2xl"></i>
                </div>
                <h3 class="font-bold text-gray-900">No trend data available</h3>
                <p class="text-gray-500 text-sm mt-1">Try selecting a different date range.</p>
            </div>
        @endif
    </div>

    <!-- Detailed Data Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/50">
            <h2 class="text-lg font-bold text-gray-800">
                Period Breakdown
            </h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Period</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Txns</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Qty Sold</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Revenue</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Profit</th>
                        <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Avg Ticket</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($trends as $trend)
                        <tr class="hover:bg-gray-50/80 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-bold text-gray-900 font-mono">{{ $trend->period }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="text-sm text-gray-700">{{ number_format($trend->transaction_count) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="text-sm font-semibold text-gray-900">{{ number_format($trend->total_quantity) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="text-sm font-bold text-purple-600">OH₵ {{ number_format($trend->total_revenue, 2) }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="text-sm font-bold {{ $trend->total_profit >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                    OH₵ {{ number_format($trend->total_profit, 2) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <span class="text-sm text-gray-600">
                                    OH₵ {{ $trend->transaction_count > 0 ? number_format($trend->total_revenue / $trend->transaction_count, 2) : '0.00' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center text-gray-500">
                                <p>No data found for table</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($trends->count() > 0)
                    <tfoot class="bg-gray-50 font-bold border-t border-gray-200">
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-900">Total</td>
                            <td class="px-6 py-4 text-sm text-gray-900 text-right">{{ number_format($trends->sum('transaction_count')) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 text-right">{{ number_format($trends->sum('total_quantity')) }}</td>
                            <td class="px-6 py-4 text-sm text-purple-700 text-right">GH₵ {{ number_format($trends->sum('total_revenue'), 2) }}</td>
                            <td class="px-6 py-4 text-sm text-emerald-700 text-right">GH₵ {{ number_format($trends->sum('total_profit'), 2) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500 text-right">-</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- Top Products Recap -->
    @if($topProducts->count() > 0)
        <div class="mt-8">
            <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-star text-yellow-500 mr-2"></i> Top Performers (This Period)
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($topProducts->take(6) as $index => $product)
                    <div class="bg-purple-50 rounded-xl p-4 border border-purple-100 flex items-start gap-4">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-sm">
                            #{{ $index + 1 }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-bold text-gray-900 truncate">{{ $product->name }}</h3>
                            <div class="flex justify-between items-end mt-2">
                                <div>
                                    <p class="text-xs text-gray-500">Revenue</p>
                                    <p class="text-sm font-bold text-purple-700">GH₵{{ number_format($product->total_revenue, 0) }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500">Vol</p>
                                    <p class="text-sm font-bold text-gray-700">{{ number_format($product->total_quantity) }}</p>
                                </div>
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
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('trendsChart').getContext('2d');
        
        // Gradient for Revenue
        const gradientRevenue = ctx.createLinearGradient(0, 0, 0, 400);
        gradientRevenue.addColorStop(0, 'rgba(147, 51, 234, 0.4)'); // Purple-600
        gradientRevenue.addColorStop(1, 'rgba(147, 51, 234, 0.0)');

        // Gradient for Profit
        const gradientProfit = ctx.createLinearGradient(0, 0, 0, 400);
        gradientProfit.addColorStop(0, 'rgba(16, 185, 129, 0.4)'); // Emerald-500
        gradientProfit.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($trends->pluck('period')->toArray()) !!},
                datasets: [{
                    label: 'Revenue (GH₵)',
                    data: {!! json_encode($trends->pluck('total_revenue')->toArray()) !!},
                    borderColor: 'rgb(147, 51, 234)',
                    backgroundColor: gradientRevenue,
                    borderWidth: 2,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: 'rgb(147, 51, 234)',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Profit (GH₵)',
                    data: {!! json_encode($trends->pluck('total_profit')->toArray()) !!},
                    borderColor: 'rgb(16, 185, 129)',
                    backgroundColor: gradientProfit,
                    borderWidth: 2,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: 'rgb(16, 185, 129)',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            boxWidth: 8,
                            padding: 20,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleColor: '#1f2937',
                        bodyColor: '#4b5563',
                        borderColor: '#e5e7eb',
                        borderWidth: 1,
                        padding: 12,
                        cornerRadius: 8,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('en-GH', { style: 'currency', currency: 'GHS' }).format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f3f4f6',
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            },
                            callback: function(value) {
                                return 'GH₵' + value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endif
@endsection
