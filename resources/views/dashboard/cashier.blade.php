@extends('layouts.app')

@section('title', 'Cashier Dashboard')

@section('content')
<div class="p-6 max-w-7xl mx-auto space-y-8">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Dashboard</h1>
            <p class="text-gray-500 mt-1">Welcome back, <span class="bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent font-bold">{{ $user->name }}</span></p>
        </div>
        <div class="flex items-center gap-3">
            <div class="px-4 py-2 bg-white border border-gray-200 rounded-full shadow-sm text-sm font-medium text-gray-700 flex items-center gap-2">
                <i class="fas fa-store text-indigo-500"></i>
                {{ $user->branch->name ?? 'No Branch' }}
            </div>
            <div class="px-4 py-2 bg-white border border-gray-200 rounded-full shadow-sm text-sm font-medium text-gray-700 flex items-center gap-2">
                <i class="far fa-calendar text-indigo-500"></i>
                {{ now()->format('D, M d') }}
            </div>
        </div>
    </div>

    @if(!$user->branch)
        <div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-r-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-amber-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-amber-700">
                        You are not currently assigned to any branch. Please contact your manager to start selling.
                    </p>
                </div>
            </div>
        </div>
    @else
        <!-- Main Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Shift Status -->
            <div class="relative overflow-hidden rounded-2xl p-6 {{ $currentSession ? 'bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-200' : 'bg-white border border-gray-200 text-gray-600' }}">
                <div class="relative z-10 h-full flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-sm font-medium {{ $currentSession ? 'text-emerald-100' : 'text-gray-500' }}">Shift Status</span>
                            <span class="h-8 w-8 rounded-full flex items-center justify-center {{ $currentSession ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-400' }}">
                                <i class="fas {{ $currentSession ? 'fa-lock-open' : 'fa-lock' }} text-sm"></i>
                            </span>
                        </div>
                        <h3 class="text-2xl font-bold {{ $currentSession ? 'text-white' : 'text-gray-900' }}">
                            {{ $currentSession ? 'Active' : 'Closed' }}
                        </h3>
                        @if($currentSession)
                            <p class="text-sm text-emerald-100 mt-1">
                                Started {{ \Carbon\Carbon::parse($currentSession->opened_at)->format('H:i') }}
                            </p>
                        @endif
                    </div>
                    
                    <div class="mt-6">
                        @if($currentSession)
                            <a href="{{ route('sales.terminal') }}" class="block w-full py-2.5 px-4 bg-white text-teal-700 hover:bg-emerald-50 rounded-xl text-sm font-bold text-center transition-colors shadow-sm">
                                Go to Terminal <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        @else
                            <a href="{{ route('sales.terminal') }}" class="block w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold text-center transition-colors shadow-lg shadow-indigo-100">
                                Start Shift
                            </a>
                        @endif
                    </div>
                </div>
                
                <!-- Decorative Circle -->
                <div class="absolute -bottom-6 -right-6 h-24 w-24 rounded-full {{ $currentSession ? 'bg-white/10' : 'bg-gray-50' }}"></div>
            </div>

            <!-- Revenue -->
            <div class="relative overflow-hidden rounded-2xl bg-white p-6 border border-gray-100 shadow-[0_2px_12px_-4px_rgba(6,81,237,0.1)] group hover:shadow-[0_8px_24px_-4px_rgba(6,81,237,0.12)] transition-shadow">
                <div class="relative z-10">
                    <p class="text-sm font-medium text-gray-500 mb-1">Total Revenue</p>
                    <h3 class="text-3xl font-bold text-gray-900 tracking-tight">₵{{ number_format($todayStats['revenue'], 2) }}</h3>
                    <div class="flex items-center gap-2 mt-4 text-sm">
                        <span class="px-2 py-0.5 rounded-md bg-green-50 text-green-700 font-medium text-xs">Today</span>
                        <span class="text-gray-400 text-xs">Collected</span>
                    </div>
                </div>
                <div class="absolute top-4 right-4 text-indigo-50 opacity-50 group-hover:opacity-100 group-hover:scale-110 transition-all duration-300">
                    <i class="fas fa-wallet text-5xl"></i>
                </div>
            </div>

            <!-- Transactions -->
            <div class="relative overflow-hidden rounded-2xl bg-white p-6 border border-gray-100 shadow-[0_2px_12px_-4px_rgba(6,81,237,0.1)] group hover:shadow-[0_8px_24px_-4px_rgba(6,81,237,0.12)] transition-shadow">
                <div class="relative z-10">
                    <p class="text-sm font-medium text-gray-500 mb-1">Transactions</p>
                    <h3 class="text-3xl font-bold text-gray-900 tracking-tight">{{ number_format($todayStats['count']) }}</h3>
                    <div class="flex items-center gap-2 mt-4 text-sm">
                        <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 font-medium text-xs">Count</span>
                        <span class="text-gray-400 text-xs">Successful Sales</span>
                    </div>
                </div>
                <div class="absolute top-4 right-4 text-blue-50 opacity-50 group-hover:opacity-100 group-hover:scale-110 transition-all duration-300">
                    <i class="fas fa-receipt text-5xl"></i>
                </div>
            </div>

            <!-- Avg Ticket -->
            <div class="relative overflow-hidden rounded-2xl bg-white p-6 border border-gray-100 shadow-[0_2px_12px_-4px_rgba(6,81,237,0.1)] group hover:shadow-[0_8px_24px_-4px_rgba(6,81,237,0.12)] transition-shadow">
                <div class="relative z-10">
                    <p class="text-sm font-medium text-gray-500 mb-1">Avg Ticket</p>
                    <h3 class="text-3xl font-bold text-gray-900 tracking-tight">₵{{ number_format($todayStats['avg_ticket'], 2) }}</h3>
                    <div class="flex items-center gap-2 mt-4 text-sm">
                        <span class="px-2 py-0.5 rounded-md bg-orange-50 text-orange-700 font-medium text-xs">KPI</span>
                        <span class="text-gray-400 text-xs">Per Customer</span>
                    </div>
                </div>
                <div class="absolute top-4 right-4 text-orange-50 opacity-50 group-hover:opacity-100 group-hover:scale-110 transition-all duration-300">
                    <i class="fas fa-chart-line text-5xl"></i>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Sales Chart -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-900">Revenue Trend</h3>
                    <span class="text-xs font-medium text-gray-500 bg-gray-50 px-3 py-1 rounded-full">Last 7 Days</span>
                </div>
                <div class="h-[300px] w-full">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <!-- Top Products -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-col">
                <h3 class="text-lg font-bold text-gray-900 mb-6">Top Sellers Today</h3>
                <div class="flex-1 space-y-5">
                    @forelse($topProducts as $index => $item)
                        @php
                            $maxQty = $topProducts->first()->total_qty;
                            $percent = $maxQty > 0 ? ($item->total_qty / $maxQty) * 100 : 0;
                        @endphp
                        <div class="group">
                            <div class="flex items-center justify-between mb-1.5">
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-bold w-4 text-gray-400 group-hover:text-indigo-600 transition-colors">{{ $index + 1 }}</span>
                                    <p class="text-sm font-medium text-gray-800 truncate w-32 group-hover:text-indigo-600 transition-colors">{{ $item->product->name }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold text-gray-900">₵{{ number_format($item->total_rev, 2) }}</p>
                                </div>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-indigo-500 h-1.5 rounded-full transition-all duration-500 ease-out" style="width: {{ $percent }}%"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1 text-right">{{ $item->total_qty }} units sold</p>
                        </div>
                    @empty
                        <div class="h-full flex flex-col items-center justify-center text-center text-gray-400 py-10">
                            <i class="fas fa-box-open text-4xl mb-3 opacity-30"></i>
                            <p class="text-sm">No items sold today yet.</p>
                        </div>
                    @endforelse
                </div>
                
                @if($topProducts->count() > 0)
                <div class="mt-6 pt-6 border-t border-gray-50">
                     <div class="flex justify-between items-center text-xs text-gray-500">
                        <span>Monthly Total</span>
                        <span class="font-bold text-gray-900 text-sm">₵{{ number_format($monthStats['revenue'], 2) }}</span>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="text-lg font-bold text-gray-900">Recent Transactions</h3>
                <a href="{{ route('sales.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-bold hover:underline">View All Sales</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-semibold tracking-wider">
                        <tr>
                            <th class="px-6 py-4 text-left">Sale #</th>
                            <th class="px-6 py-4 text-left">Time</th>
                            <th class="px-6 py-4 text-left">Payment</th>
                            <th class="px-6 py-4 text-right">Amount</th>
                            <th class="px-6 py-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-50">
                        @forelse($recent_sales as $sale)
                            <tr class="hover:bg-indigo-50/30 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-600">
                                    <a href="{{ route('sales.show', $sale) }}">#{{ $sale->id }}</a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 group-hover:text-gray-700">
                                    {{ $sale->created_at->format('H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 capitalize">
                                    <span class="inline-flex items-center gap-2 px-2.5 py-1 rounded-md bg-gray-100 border border-gray-200 text-xs font-semibold">
                                        @if($sale->payment_method === 'cash') <i class="fas fa-money-bill text-emerald-500"></i>
                                        @elseif($sale->payment_method === 'card') <i class="fas fa-credit-card text-blue-500"></i>
                                        @else <i class="fas fa-mobile-alt text-amber-500"></i>
                                        @endif
                                        {{ str_replace('_', ' ', $sale->payment_method) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 text-right">
                                    ₵{{ number_format($sale->total, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Completed
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-400">
                                    <i class="far fa-clipboard text-3xl mb-3 block opacity-30"></i>
                                    No recent transactions found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Chart.js Configuration
        const ctx = document.getElementById('salesChart');
        if (ctx) {
            // Create gradient
            const chartCanvas = ctx.getContext('2d');
            const gradient = chartCanvas.createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(79, 70, 229, 0.4)'); // Indigo start
            gradient.addColorStop(1, 'rgba(79, 70, 229, 0.0)'); // Transparent end

            new Chart(ctx, {
                type: 'line', // Switch to line/area
                data: {
                    labels: @json($chartData['labels']),
                    datasets: [{
                        label: 'Daily Revenue',
                        data: @json($chartData['data']),
                        backgroundColor: gradient,
                        borderColor: '#4f46e5', // Indigo-600
                        borderWidth: 2,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#4f46e5',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.4 // Smooth curves
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#1f2937',
                            titleColor: '#f9fafb',
                            bodyColor: '#f9fafb',
                            padding: 10,
                            cornerRadius: 8,
                            displayColors: false,
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
                                color: '#f3f4f6', // Very light grid
                                borderDash: [4, 4]
                            },
                            ticks: {
                                color: '#9ca3af',
                                font: {
                                    size: 11
                                },
                                callback: function(value) {
                                    if (value >= 1000) {
                                        return '₵' + (value/1000) + 'k';
                                    }
                                    return '₵' + value;
                                }
                            },
                            border: {
                                display: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#9ca3af',
                                font: {
                                    size: 11
                                }
                            },
                            border: {
                                display: false
                            }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    },
                }
            });
        }
    });
</script>
@endpush
@endsection 