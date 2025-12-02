@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md mb-6">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-chart-line mr-3 text-blue-600"></i>Sales Dashboard
                    </h1>
                    <div class="flex space-x-4">
                        @if(auth()->user()->role === 'cashier')
                            <a href="{{ route('sales.terminal') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                                <i class="fas fa-cash-register mr-2"></i>POS Terminal
                            </a>
                        @endif
                        <a href="{{ route('sales.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-list mr-2"></i>All Sales
                        </a>
                    </div>
                </div>
            </div>

            <!-- Date Filter Form with Quick Filters -->
            <div class="p-6">
                <form method="GET" id="dateFilterForm" class="space-y-4">
                    <!-- Quick Date Filters -->
                    <div class="flex flex-wrap gap-2 mb-4">
                        <button type="button" onclick="setQuickDate('today')" class="px-4 py-2 text-sm font-medium bg-white border border-gray-300 hover:bg-blue-50 hover:border-blue-400 rounded-lg transition-colors duration-200">
                            <i class="fas fa-calendar-day mr-1"></i>Today
                        </button>
                        <button type="button" onclick="setQuickDate('week')" class="px-4 py-2 text-sm font-medium bg-white border border-gray-300 hover:bg-blue-50 hover:border-blue-400 rounded-lg transition-colors duration-200">
                            <i class="fas fa-calendar-week mr-1"></i>This Week
                        </button>
                        <button type="button" onclick="setQuickDate('month')" class="px-4 py-2 text-sm font-medium bg-white border border-gray-300 hover:bg-blue-50 hover:border-blue-400 rounded-lg transition-colors duration-200">
                            <i class="fas fa-calendar-alt mr-1"></i>This Month
                        </button>
                        <button type="button" onclick="setQuickDate('last_month')" class="px-4 py-2 text-sm font-medium bg-white border border-gray-300 hover:bg-blue-50 hover:border-blue-400 rounded-lg transition-colors duration-200">
                            <i class="fas fa-history mr-1"></i>Last Month
                        </button>
                        <button type="button" onclick="setQuickDate('year')" class="px-4 py-2 text-sm font-medium bg-white border border-gray-300 hover:bg-blue-50 hover:border-blue-400 rounded-lg transition-colors duration-200">
                            <i class="fas fa-calendar mr-1"></i>This Year
                        </button>
                    </div>

                    <div class="flex flex-wrap items-end gap-4">
                        <div class="flex-1 min-w-40">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                            <input type="date" id="start_date" name="start_date" value="{{ request('start_date', $startDate->format('Y-m-d')) }}" 
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="flex-1 min-w-40">
                            <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                            <input type="date" id="end_date" name="end_date" value="{{ request('end_date', $endDate->format('Y-m-d')) }}" 
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors duration-200">
                                <i class="fas fa-search mr-2"></i>Apply Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Period Comparison Banner -->
        @if(isset($periodComparison))
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg shadow-md p-5 mb-6 border border-blue-200">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex items-center gap-6 flex-wrap">
                    <div>
                        <p class="text-xs text-gray-600 mb-1 flex items-center">
                            <i class="fas fa-coins mr-1"></i>Revenue Change
                        </p>
                        @php
                            $revenueChange = $periodComparison['previous_revenue'] > 0 
                                ? (($summary['total_revenue'] - $periodComparison['previous_revenue']) / $periodComparison['previous_revenue']) * 100 
                                : ($summary['total_revenue'] > 0 ? 100 : 0);
                            $revenueDiff = $summary['total_revenue'] - $periodComparison['previous_revenue'];
                        @endphp
                        <p class="text-xl font-bold {{ $revenueChange >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            <i class="fas fa-arrow-{{ $revenueChange >= 0 ? 'up' : 'down' }} mr-1"></i>
                            {{ number_format(abs($revenueChange), 1) }}%
                        </p>
                        <p class="text-xs text-gray-600 mt-1">
                            {{ $revenueChange >= 0 ? '+' : '-' }}₵{{ number_format(abs($revenueDiff), 2) }}
                        </p>
                    </div>
                    <div class="border-l border-blue-300 pl-6">
                        <p class="text-xs text-gray-600 mb-1 flex items-center">
                            <i class="fas fa-chart-line mr-1"></i>Profit Change
                        </p>
                        @php
                            $profitChange = $periodComparison['previous_profit'] > 0 
                                ? (($summary['total_profit'] - $periodComparison['previous_profit']) / $periodComparison['previous_profit']) * 100 
                                : ($summary['total_profit'] > 0 ? 100 : 0);
                            $profitDiff = $summary['total_profit'] - $periodComparison['previous_profit'];
                        @endphp
                        <p class="text-xl font-bold {{ $profitChange >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            <i class="fas fa-arrow-{{ $profitChange >= 0 ? 'up' : 'down' }} mr-1"></i>
                            {{ number_format(abs($profitChange), 1) }}%
                        </p>
                        <p class="text-xs text-gray-600 mt-1">
                            {{ $profitChange >= 0 ? '+' : '-' }}₵{{ number_format(abs($profitDiff), 2) }}
                        </p>
                    </div>
                    <div class="border-l border-blue-300 pl-6">
                        <p class="text-xs text-gray-600 mb-1 flex items-center">
                            <i class="fas fa-shopping-cart mr-1"></i>Sales Volume
                        </p>
                        @php
                            $salesChange = $periodComparison['previous_sales_count'] > 0 
                                ? (($summary['total_sales'] - $periodComparison['previous_sales_count']) / $periodComparison['previous_sales_count']) * 100 
                                : ($summary['total_sales'] > 0 ? 100 : 0);
                        @endphp
                        <p class="text-xl font-bold {{ $salesChange >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            <i class="fas fa-arrow-{{ $salesChange >= 0 ? 'up' : 'down' }} mr-1"></i>
                            {{ number_format(abs($salesChange), 1) }}%
                        </p>
                        <p class="text-xs text-gray-600 mt-1">
                            {{ $summary['total_sales'] }} vs {{ $periodComparison['previous_sales_count'] }} sales
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Comparison Period</p>
                    <p class="text-sm font-medium text-gray-700">
                        {{ $periodComparison['previous_start']->format('M d') }} - {{ $periodComparison['previous_end']->format('M d, Y') }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">vs. current period</p>
                </div>
            </div>
        </div>
        @endif

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
                        <p class="text-xs text-gray-500">{{ number_format($summary['total_quantity_sold']) }} items</p>
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
                        <p class="text-xs text-gray-500">Avg: ₵{{ number_format($summary['average_transaction'], 2) }}</p>
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
                        <p class="text-xs text-gray-500">{{ number_format($summary['average_margin'], 1) }}% margin</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Branch Comparison (Only for multi-branch users) -->
        @if($branchComparison && count($branchComparison) > 0)
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-store mr-2 text-purple-600"></i>Branch Performance
                </h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Sales</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Revenue</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">COGS</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Profit</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Margin</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($branchComparison as $branch)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $branch['branch_name'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-900">{{ $branch['sales_count'] }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-900">₵{{ number_format($branch['revenue'], 2) }}</td>
                            <td class="px-4 py-3 text-sm text-right text-gray-600">₵{{ number_format($branch['cogs'], 2) }}</td>
                            <td class="px-4 py-3 text-sm text-right font-medium {{ $branch['profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                ₵{{ number_format($branch['profit'], 2) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-right">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                    {{ $branch['margin'] >= 30 ? 'bg-green-100 text-green-800' : ($branch['margin'] >= 15 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ number_format($branch['margin'], 1) }}%
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Supplier Breakdown -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-boxes mr-2 text-blue-600"></i>Supplier Breakdown
                </h2>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Local Suppliers -->
                <div class="border border-green-200 rounded-lg p-4 bg-green-50">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-md font-semibold text-green-800">
                            <i class="fas fa-leaf mr-2"></i>Local Suppliers
                        </h3>
                        <span class="text-xs text-green-600 font-medium">
                            {{ $supplierBreakdown['local']['products_count'] }} Products
                        </span>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Quantity Sold:</span>
                            <span class="text-sm font-semibold text-gray-900">{{ number_format($supplierBreakdown['local']['quantity_sold']) }} units</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Revenue:</span>
                            <span class="text-sm font-semibold text-green-700">₵{{ number_format($supplierBreakdown['local']['revenue'], 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Profit:</span>
                            <span class="text-sm font-semibold text-green-700">₵{{ number_format($supplierBreakdown['local']['profit'], 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t border-green-200">
                            <span class="text-sm text-gray-600">Margin:</span>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $supplierBreakdown['local']['margin'] >= 30 ? 'bg-green-100 text-green-800' : ($supplierBreakdown['local']['margin'] >= 15 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ number_format($supplierBreakdown['local']['margin'], 1) }}%
                            </span>
                        </div>
                        @if($supplierBreakdown['total_revenue'] > 0)
                        <div class="flex justify-between items-center pt-2">
                            <span class="text-sm text-gray-600">% of Total Revenue:</span>
                            <span class="text-sm font-semibold text-green-700">
                                {{ number_format(($supplierBreakdown['local']['revenue'] / $supplierBreakdown['total_revenue']) * 100, 1) }}%
                            </span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Central Suppliers -->
                <div class="border border-blue-200 rounded-lg p-4 bg-blue-50">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-md font-semibold text-blue-800">
                            <i class="fas fa-building mr-2"></i>Central Suppliers
                        </h3>
                        <span class="text-xs text-blue-600 font-medium">
                            {{ $supplierBreakdown['central']['products_count'] }} Products
                        </span>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Quantity Sold:</span>
                            <span class="text-sm font-semibold text-gray-900">{{ number_format($supplierBreakdown['central']['quantity_sold']) }} units</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Revenue:</span>
                            <span class="text-sm font-semibold text-blue-700">₵{{ number_format($supplierBreakdown['central']['revenue'], 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Profit:</span>
                            <span class="text-sm font-semibold text-blue-700">₵{{ number_format($supplierBreakdown['central']['profit'], 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t border-blue-200">
                            <span class="text-sm text-gray-600">Margin:</span>
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                {{ $supplierBreakdown['central']['margin'] >= 30 ? 'bg-green-100 text-green-800' : ($supplierBreakdown['central']['margin'] >= 15 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ number_format($supplierBreakdown['central']['margin'], 1) }}%
                            </span>
                        </div>
                        @if($supplierBreakdown['total_revenue'] > 0)
                        <div class="flex justify-between items-center pt-2">
                            <span class="text-sm text-gray-600">% of Total Revenue:</span>
                            <span class="text-sm font-semibold text-blue-700">
                                {{ number_format(($supplierBreakdown['central']['revenue'] / $supplierBreakdown['total_revenue']) * 100, 1) }}%
                            </span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Products and Cashier Performance Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Top Products -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-trophy mr-2 text-yellow-600"></i>Top Selling Products
                </h2>
                @if(count($topProducts) > 0)
                <div class="space-y-3">
                    @foreach(array_slice($topProducts, 0, 5) as $index => $product)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold text-sm">
                                {{ $index + 1 }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-medium text-gray-900">{{ $product['product_name'] }}</p>
                                    @if($product['is_local'])
                                        <span class="px-2 py-0.5 text-xs font-medium bg-green-100 text-green-800 rounded-full" title="Local Supplier Product">
                                            <i class="fas fa-leaf"></i> Local
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    <p class="text-xs text-gray-500">{{ $product['quantity_sold'] }} sold</p>
                                    @if($product['supplier_name'])
                                        <span class="text-xs text-gray-400">• {{ $product['supplier_name'] }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-gray-900">₵{{ number_format($product['revenue'], 2) }}</p>
                            <p class="text-xs text-green-600">+₵{{ number_format($product['profit'], 2) }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-center text-gray-500 py-4">No products sold in this period</p>
                @endif
            </div>

            <!-- Cashier Performance -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">
                    <i class="fas fa-user-tie mr-2 text-indigo-600"></i>Cashier Performance
                </h2>
                @if(count($cashierStats) > 0)
                <div class="space-y-3">
                    @foreach(array_slice($cashierStats, 0, 5) as $cashier)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ $cashier['cashier_name'] }}</p>
                            <div class="flex items-center gap-3 mt-1">
                                <p class="text-xs text-gray-500">
                                    <i class="fas fa-store text-gray-400 mr-1"></i>{{ $cashier['branch_name'] }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    <i class="fas fa-shopping-bag text-gray-400 mr-1"></i>{{ $cashier['sales_count'] }} sales
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-gray-900">₵{{ number_format($cashier['revenue'], 2) }}</p>
                            <p class="text-xs text-green-600">+₵{{ number_format($cashier['profit'], 2) }} profit</p>
                            <p class="text-xs text-gray-600">Avg: ₵{{ number_format($cashier['avg_transaction'], 2) }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-center text-gray-500 py-4">No cashier data available</p>
                @endif
            </div>
        </div>


        @php $hasChartData = isset($chartData['labels']) && count($chartData['labels']) > 0; @endphp

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">Revenue vs COGS</h2>
                    <span class="text-xs uppercase tracking-wide text-gray-400">Daily</span>
                </div>
                @if($hasChartData)
                    <canvas style="max-height: 240px" id="revenueChart" height="20"></canvas>
                @else
                    <div class="text-center py-10 text-gray-500">
                        <i class="fas fa-chart-line text-4xl mb-3"></i>
                        <p>No data for selected period</p>
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">Profit Margin Trend</h2>
                    <span class="text-xs uppercase tracking-wide text-gray-400">Daily %</span>
                </div>
                @if($hasChartData)
                    <canvas style="max-height: 240px" id="marginChart" height="20"></canvas>
                @else
                    <div class="text-center py-10 text-gray-500">
                        <i class="fas fa-percentage text-4xl mb-3"></i>
                        <p>Margin trends unavailable</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- P&L Statement - Redesigned -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6">
                <div class="flex items-center justify-between text-white">
                    <div>
                        <h2 class="text-xl font-bold flex items-center">
                            <i class="fas fa-file-invoice-dollar mr-3"></i>Profit & Loss Statement
                        </h2>
                        <p class="text-blue-100 text-sm mt-1">{{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-blue-100 text-xs uppercase tracking-wide">Net Profit Margin</p>
                        <p class="text-3xl font-bold">{{ number_format($summary['average_margin'], 1) }}%</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <!-- Revenue Section -->
                <div class="mb-6">
                    <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg border-l-4 border-green-500">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-coins text-white text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-green-800 uppercase tracking-wide">Total Revenue</p>
                                <p class="text-xs text-green-600 mt-1">{{ $summary['total_sales'] }} transactions</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-green-700">₵{{ number_format($summary['total_revenue'], 2) }}</p>
                            <p class="text-xs text-green-600 mt-1">₵{{ number_format($summary['average_transaction'], 2) }} avg</p>
                        </div>
                    </div>
                </div>

                <!-- Cost of Goods Sold -->
                <div class="mb-6">
                    <div class="flex items-center justify-between p-4 bg-red-50 rounded-lg border-l-4 border-red-500">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-red-500 rounded-lg flex items-center justify-center mr-4">
                                <i class="fas fa-boxes text-white text-xl"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-red-800 uppercase tracking-wide">Cost of Goods Sold</p>
                                <p class="text-xs text-red-600 mt-1">Direct product costs</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-red-700">₵{{ number_format($summary['total_cogs'], 2) }}</p>
                            <p class="text-xs text-red-600 mt-1">{{ number_format(($summary['total_cogs'] / max($summary['total_revenue'], 1)) * 100, 1) }}% of revenue</p>
                        </div>
                    </div>
                </div>

                <!-- Divider -->
                <div class="border-t-2 border-gray-300 my-6"></div>

                <!-- Gross Profit -->
                <div class="mb-6">
                    <div class="flex items-center justify-between p-5 bg-gradient-to-r from-emerald-50 to-green-50 rounded-lg border-2 border-emerald-400 shadow-sm">
                        <div class="flex items-center">
                            <div class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-green-600 rounded-lg flex items-center justify-center mr-4 shadow-md">
                                <i class="fas fa-chart-line text-white text-2xl"></i>
                            </div>
                            <div>
                                <p class="text-base font-bold text-emerald-900 uppercase tracking-wide">Gross Profit</p>
                                <p class="text-sm text-emerald-700 mt-1">Revenue - COGS</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-3xl font-extrabold text-emerald-700">₵{{ number_format($summary['total_profit'], 2) }}</p>
                            <p class="text-sm font-semibold text-emerald-600 mt-1">{{ number_format($summary['average_margin'], 2) }}% margin</p>
                        </div>
                    </div>
                </div>

                <!-- Operating Expenses Notice -->
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-r-lg">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle text-blue-600 text-lg"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-blue-800">Operating Expenses Not Tracked</p>
                            <p class="text-xs text-blue-700 mt-1">
                                This statement shows Gross Profit only. To calculate Net Profit, subtract operating expenses 
                                (rent, utilities, salaries, marketing, etc.) from the gross profit shown above.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Visual Chart -->
                @if($hasChartData)
                <div class="mt-8">
                    <h3 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wide">
                        <i class="fas fa-chart-bar mr-2"></i>Daily Profit & Loss Trend
                    </h3>
                    <div class="bg-gray-50 p-4 rounded-lg" style="height: 300px;">
                        <canvas id="pnlChart"></canvas>
                    </div>
                </div>
                @endif
            </div>
        </div>


        <!-- Detailed Sales Table -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">Sales Transaction Details</h2>
                <p class="text-sm text-gray-600">{{ $startDate->format('M d, Y') }} - {{ $endDate->format('M d, Y') }}</p>
            </div>

            <div class="p-6">
                @if($sales->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sale Details</th>
                                    @if(!$userBranchId)
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Branch</th>
                                    @endif
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">COGS</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Profit</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Margin %</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
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
                                        <div class="text-sm text-gray-500">{{ $sale->items->count() }} items, {{ $sale->items->sum('quantity') }} qty</div>
                                        <div class="text-sm text-gray-500">{{ $sale->cashier->name }}</div>
                                    </td>
                                    @if(!$userBranchId)
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ optional($sale->branch)->display_label ?? 'Unassigned' }}</div>
                                    </td>
                                    @endif
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">₵{{ number_format($sale->total, 2) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">₵{{ number_format($cogs, 2) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium {{ $profit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                            ₵{{ number_format($profit, 2) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                            {{ $margin >= 30 ? 'bg-green-100 text-green-800' : ($margin >= 15 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
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
                                <i class="fas fa-file-csv mr-2"></i>Export CSV
                            </a>
                            <a href="{{route('sales.export.pdf', request()->only(['start_date', 'end_date']))}}"
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm">
                                <i class="fas fa-file-pdf mr-2"></i>Export PDF
                            </a>
                        </div>
                    </div>
                @else
                    <div class="text-center py-12">
                        <i class="fas fa-chart-bar text-gray-400 text-6xl mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No sales data for selected period</h3>
                        <p class="text-gray-500 mb-6">Try adjusting your date range or create some sales.</p>
                        @if(auth()->user()->role === 'cashier')
                            <a href="{{ route('sales.terminal') }}" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg">
                                <i class="fas fa-cash-register mr-2"></i>Go to POS Terminal
                            </a>
                        @endif
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
    function setQuickDate(period) {
        const today = new Date();
        let startDate, endDate;

        switch(period) {
            case 'today':
                startDate = endDate = formatDate(today);
                break;
            case 'week':
                const weekStart = new Date(today);
                weekStart.setDate(today.getDate() - today.getDay());
                startDate = formatDate(weekStart);
                endDate = formatDate(today);
                break;
            case 'month':
                startDate = formatDate(new Date(today.getFullYear(), today.getMonth(), 1));
                endDate = formatDate(new Date(today.getFullYear(), today.getMonth() + 1, 0));
                break;
            case 'last_month':
                startDate = formatDate(new Date(today.getFullYear(), today.getMonth() - 1, 1));
                endDate = formatDate(new Date(today.getFullYear(), today.getMonth(), 0));
                break;
            case 'year':
                startDate = formatDate(new Date(today.getFullYear(), 0, 1));
                endDate = formatDate(today);
                break;
        }

        document.getElementById('start_date').value = startDate;
        document.getElementById('end_date').value = endDate;
        
        // Submit the form
        document.getElementById('dateFilterForm').submit();
    }

    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    document.addEventListener('DOMContentLoaded', () => {
        const chartData = @json($chartData);

        console.debug('[Sales Report] Chart data payload:', chartData);

        if (!chartData.labels || chartData.labels.length === 0) {
            console.warn('[Sales Report] No chart data available');
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
                    ticks: { color: '#4b5563', font: { size: 11 } },
                    grid: { display: false }
                },
                y: {
                    ticks: {
                        color: '#4b5563',
                        font: { size: 11 },
                        callback: (value) => '₵' + Number(value).toLocaleString()
                    },
                    grid: { color: 'rgba(209, 213, 219, 0.3)' }
                }
            },
            plugins: {
                legend: {
                    labels: { color: '#1f2937', usePointStyle: true, font: { size: 12 } }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: { size: 13, weight: 'bold' },
                    bodyFont: { size: 12 },
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
                            borderWidth: 3,
                            tension: 0.4,
                            fill: false,
                            yAxisID: 'y',
                            pointRadius: 4,
                            pointHoverRadius: 6,
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
                                text: 'Amount (₵)',
                                color: '#6b7280',
                                font: { size: 12, weight: 'bold' }
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
                            tension: 0.4,
                            fill: true,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#f59e0b',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            ticks: { color: '#4b5563', font: { size: 11 } },
                            grid: { display: false }
                        },
                        y: {
                            ticks: {
                                color: '#4b5563',
                                font: { size: 11 },
                                callback: (value) => `${value}%`
                            },
                            grid: { color: 'rgba(209, 213, 219, 0.3)' },
                            title: {
                                display: true,
                                text: 'Gross Margin %',
                                color: '#6b7280',
                                font: { size: 12, weight: 'bold' }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            labels: { color: '#1f2937', usePointStyle: true, font: { size: 12 } }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: { size: 13, weight: 'bold' },
                            bodyFont: { size: 12 },
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
            // Create a mixed chart showing profit as bars
            new Chart(ctxPnL, {
                type: 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        {
                            label: 'Daily Profit/Loss',
                            data: chartData.profit,
                            backgroundColor: chartData.profit.map(value => 
                                value >= 0 ? 'rgba(16, 185, 129, 0.8)' : 'rgba(239, 68, 68, 0.8)'
                            ),
                            borderColor: chartData.profit.map(value => 
                                value >= 0 ? 'rgba(16, 185, 129, 1)' : 'rgba(239, 68, 68, 1)'
                            ),
                            borderWidth: 1,
                            borderRadius: 8,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            ticks: { color: '#4b5563', font: { size: 11 } },
                            grid: { display: false }
                        },
                        y: {
                            ticks: {
                                color: '#4b5563',
                                font: { size: 11 },
                                callback: (value) => {
                                    const amount = Math.abs(Number(value)).toLocaleString(undefined, { minimumFractionDigits: 0 });
                                    return `${value < 0 ? '-₵' : '₵'}${amount}`;
                                }
                            },
                            grid: { 
                                color: 'rgba(209, 213, 219, 0.3)',
                                drawBorder: false
                            },
                            border: {
                                display: false
                            },
                            title: {
                                display: true,
                                text: 'Profit/Loss (₵)',
                                color: '#6b7280',
                                font: { size: 12, weight: 'bold' }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: { size: 13, weight: 'bold' },
                            bodyFont: { size: 12 },
                            callbacks: {
                                label: (context) => {
                                    const value = context.parsed.y;
                                    const formatted = Math.abs(value).toLocaleString(undefined, { minimumFractionDigits: 2 });
                                    const status = value >= 0 ? 'Profit' : 'Loss';
                                    return `${status}: ${value < 0 ? '-₵' : '₵'}${formatted}`;
                                },
                                afterLabel: (context) => {
                                    const dayIndex = context.dataIndex;
                                    const revenue = chartData.revenue[dayIndex];
                                    const cogs = chartData.cogs[dayIndex];
                                    const margin = chartData.margin[dayIndex];
                                    return [
                                        `Revenue: ₵${revenue.toLocaleString(undefined, { minimumFractionDigits: 2 })}`,
                                        `COGS: ₵${cogs.toLocaleString(undefined, { minimumFractionDigits: 2 })}`,
                                        `Margin: ${margin.toFixed(1)}%`
                                    ];
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