@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md mb-6">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-chart-bar mr-3 text-blue-600"></i>Sales Report
                    </h1>
                    <div class="flex space-x-4">
                        <a href="{{ route('sales.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-plus mr-2"></i>New Sale
                        </a>
                        <a href="{{ route('sales.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-list mr-2"></i>All Sales
                        </a>
                    </div>
                </div>
            </div>

            <!-- Date Filter Form -->
            <div class="p-6">
                <form method="GET" class="flex flex-wrap items-end gap-4">
                    <div class="flex-1 min-w-40">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                        <input type="date" name="start_date" value="{{ request('start_date', $startDate->format('Y-m-d')) }}" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="flex-1 min-w-40">
                        <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                        <input type="date" name="end_date" value="{{ request('end_date', $endDate->format('Y-m-d')) }}" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 mr-4">
                        <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Sales</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $summary['total_sales'] }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 mr-4">
                        <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                        <p class="text-2xl font-bold text-gray-900">₵{{ number_format($summary['total_revenue'], 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-red-100 mr-4">
                        <i class="fas fa-box text-red-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total COGS</p>
                        <p class="text-2xl font-bold text-gray-900">₵{{ number_format($summary['total_cogs'], 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 mr-4">
                        <i class="fas fa-chart-line text-yellow-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600">Gross Profit</p>
                        <p class="text-2xl font-bold text-gray-900">₵{{ number_format($summary['total_profit'], 2) }}</p>
                        <p class="text-sm text-gray-500">{{ number_format($summary['average_margin'], 1) }}% margin</p>
                    </div>
                </div>
            </div>
        </div>


        @php $hasChartData = isset($chartData['labels']) && count($chartData['labels']) > 0; @endphp

        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-800">Revenue vs COGS Trend</h2>
                <span class="text-xs uppercase tracking-wide text-gray-400">Daily totals</span>
            </div>
            @if($hasChartData)
                <canvas style="max-height: 240px" id="revenueChart" height="20"></canvas>
            @else
                <div class="text-center py-10 text-gray-500">
                    <i class="fas fa-chart-line text-4xl mb-3"></i>
                    <p>No sales recorded for this period yet. Adjust the filters to see trends.</p>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-800">Profit Margin Trend</h2>
                <span class="text-xs uppercase tracking-wide text-gray-400">Daily %</span>
            </div>
            @if($hasChartData)
                <canvas style="max-height: 240px" id="marginChart" height="20"></canvas>
            @else
                <div class="text-center py-10 text-gray-500">
                    <i class="fas fa-percentage text-4xl mb-3"></i>
                    <p>Margin trends will appear once sales exist for this range.</p>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-800">Profit &amp; Loss Overview</h2>
                <span class="text-xs uppercase tracking-wide text-gray-400">Net by day</span>
            </div>
            @if($hasChartData)
                <canvas style="max-height: 240px" id="pnlChart" height="20"></canvas>
            @else
                <div class="text-center py-10 text-gray-500">
                    <i class="fas fa-balance-scale text-4xl mb-3"></i>
                    <p>Profit &amp; loss activity will show here once sales are recorded.</p>
                </div>
            @endif
        </div>
        
        

        <!-- Detailed Sales Table -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">Sales Details</h2>
                <p class="text-sm text-gray-600">{{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</p>
            </div>

            <div class="p-6">
                @if($sales->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Sale Details
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Branch
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Revenue
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        COGS
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Profit
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Margin %
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($sales as $sale)
                                @php
                                    $cogs = $sale->items->sum('total_cost');
                                    $profit = $sale->total - $cogs;
                                    $margin = $sale->total > 0 ? ($profit / $sale->total) * 100 : 0;
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">Sale #{{ $sale->id }}</div>
                                        <div class="text-sm text-gray-500">
                                            {{ $sale->items->count() }} items, {{ $sale->items->sum('quantity') }} qty
                                        </div>
                                        <div class="text-sm text-gray-500">{{ $sale->cashier->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ optional($sale->branch)->display_label ?? 'Unassigned branch' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">₵{{ number_format($sale->total, 2) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ number_format($cogs, 2) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium {{ $profit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            ₵{{ number_format($profit, 2) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $marginBadgeClass = 'bg-red-100 text-red-800';
                                            if ($margin >= 30) {
                                                $marginBadgeClass = 'bg-green-100 text-green-800';
                                            } elseif ($margin >= 15) {
                                                $marginBadgeClass = 'bg-yellow-100 text-yellow-800';
                                            }
                                        @endphp
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $marginBadgeClass }}">
                                            {{ number_format($margin, 1) }}%
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $sale->created_at->format('M d, Y') }}</div>
                                        <div class="text-sm text-gray-500">{{ $sale->created_at->format('h:i A') }}</div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Export Options -->
                    <div class="mt-6 flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            Showing {{ $sales->count() }} sales
                        </div>
                        <div class="space-x-2">
                            <a href="{{route('sales.export.csv', request()->only(['start_date', 'end_date']))}}"
                                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm">
                                <i class="fas fa-download mr-2"></i>Export CSV
                            </a>
                            <a href="{{route('sales.export.pdf', request()->only(['start_date', 'end_date']))}}"
                                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm">
                                <i class="fas fa-download mr-2"></i>Export PDF
                            </a>
                        </div>
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="fas fa-chart-bar text-gray-400 text-6xl mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No sales data for selected period</h3>
                        <p class="text-gray-500 mb-6">Try adjusting your date range or create some sales.</p>
                        <a href="{{ route('sales.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg">
                            <i class="fas fa-plus mr-2"></i>New Sale
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const chartData = @json($chartData);

        console.debug('[Sales Report] Chart data payload:', chartData);

        if (!chartData.labels || chartData.labels.length === 0) {
            return;
        }

    const ctxRevenue = document.getElementById('revenueChart');
    const ctxMargin = document.getElementById('marginChart');
    const ctxPnL = document.getElementById('pnlChart');

        const baseOptions = {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    ticks: { color: '#4b5563' },
                    grid: { display: false }
                },
                y: {
                    ticks: {
                        color: '#4b5563',
                        callback: (value) => '₵' + Number(value).toLocaleString()
                    },
                    grid: { color: 'rgba(209, 213, 219, 0.3)' }
                }
            },
            plugins: {
                legend: {
                    labels: { color: '#1f2937', usePointStyle: true }
                },
                tooltip: {
                    callbacks: {
                        label: (context) => {
                            const label = context.dataset.label || '';
                            const value = context.parsed.y ?? context.parsed;
                            return `${label}: ₵${Number(value).toLocaleString(undefined, { minimumFractionDigits: 2 })}`;
                        }
                    }
                }
            }
        };

        if (ctxRevenue) {
            new Chart(ctxRevenue, {
                type: 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        {
                            type: 'bar',
                            label: 'Revenue',
                            data: chartData.revenue,
                            backgroundColor: 'rgba(16, 185, 129, 0.7)',
                            borderRadius: 6,
                        },
                        {
                            type: 'bar',
                            label: 'COGS',
                            data: chartData.cogs,
                            backgroundColor: 'rgba(239, 68, 68, 0.6)',
                            borderRadius: 6,
                        },
                        {
                            type: 'line',
                            label: 'Profit',
                            data: chartData.profit,
                            borderColor: '#2563eb',
                            backgroundColor: 'rgba(37, 99, 235, 0.2)',
                            borderWidth: 2,
                            tension: 0.35,
                            fill: false,
                            yAxisID: 'y',
                        }
                    ]
                },
                options: {
                    ...baseOptions,
                    scales: {
                        ...baseOptions.scales,
                        y: {
                            ...baseOptions.scales.y,
                            title: {
                                display: true,
                                text: 'Amount (USD)',
                                color: '#6b7280',
                                font: { size: 12 }
                            }
                        }
                    }
                }
            });
        }

        if (ctxMargin) {
            new Chart(ctxMargin, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        {
                            label: 'Margin %',
                            data: chartData.margin,
                            borderColor: '#f59e0b',
                            backgroundColor: 'rgba(245, 158, 11, 0.2)',
                            borderWidth: 3,
                            tension: 0.35,
                            fill: true,
                            pointRadius: 3,
                            pointBackgroundColor: '#f59e0b',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            ticks: { color: '#4b5563' },
                            grid: { display: false }
                        },
                        y: {
                            ticks: {
                                color: '#4b5563',
                                callback: (value) => `${value}%`
                            },
                            grid: { color: 'rgba(209, 213, 219, 0.3)' },
                            title: {
                                display: true,
                                text: 'Gross Margin %',
                                color: '#6b7280',
                                font: { size: 12 }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            labels: { color: '#1f2937', usePointStyle: true }
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => {
                                    const value = context.parsed.y ?? context.parsed;
                                    return `Margin: ${Number(value).toFixed(2)}%`;
                                }
                            }
                        }
                    }
                }
            });
        }

        if (ctxPnL) {
            const profitSeries = chartData.profit.map(value => value > 0 ? value : 0);
            const lossSource = Array.isArray(chartData.loss)
                ? chartData.loss
                : chartData.profit.map(value => value < 0 ? Math.abs(value) : 0);
            const lossSeries = lossSource.map(value => value > 0 ? -value : 0);

            new Chart(ctxPnL, {
                type: 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        {
                            label: 'Profit',
                            data: profitSeries,
                            backgroundColor: 'rgba(22, 163, 74, 0.75)',
                            borderColor: 'rgba(22, 163, 74, 1)',
                            borderWidth: 1,
                            borderRadius: { topLeft: 6, topRight: 6 },
                            stack: 'pnl'
                        },
                        {
                            label: 'Loss',
                            data: lossSeries,
                            backgroundColor: 'rgba(248, 113, 113, 0.75)',
                            borderColor: 'rgba(248, 113, 113, 1)',
                            borderWidth: 1,
                            borderRadius: { bottomLeft: 6, bottomRight: 6 },
                            stack: 'pnl'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            ticks: { color: '#4b5563' },
                            grid: { display: false }
                        },
                        y: {
                            ticks: {
                                color: '#4b5563',
                                callback: (value) => {
                                    const amount = Math.abs(Number(value)).toLocaleString(undefined, { minimumFractionDigits: 2 });
                                    return `${value < 0 ? '-₵' : '₵'}${amount}`;
                                }
                            },
                            grid: { color: 'rgba(209, 213, 219, 0.3)' },
                            title: {
                                display: true,
                                text: 'Net Result (USD)',
                                color: '#6b7280',
                                font: { size: 12 }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            labels: { color: '#1f2937', usePointStyle: true }
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => {
                                    const label = context.dataset.label || '';
                                    const raw = context.parsed.y ?? context.parsed;
                                    const formatted = Math.abs(raw).toLocaleString(undefined, { minimumFractionDigits: 2 });
                                    return `${label}: ${raw < 0 ? '-₵' : '₵'}${formatted}`;
                                }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush