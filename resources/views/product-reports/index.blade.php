@extends('layouts.app')

@section('title', 'Product Analytics Dashboard')

@section('content')
<div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-lg shadow-lg mb-6 text-white">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold flex items-center">
                            <i class="fas fa-chart-pie mr-3"></i>Product Analytics Dashboard
                        </h1>
                        <p class="text-purple-100 mt-2">Comprehensive product performance insights</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-purple-100">Period</p>
                        <p class="text-lg font-semibold">{{ $dateRange['start']->format('M d, Y') }} - {{ $dateRange['end']->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Total Products -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Total Products</p>
                        <p class="text-3xl font-bold text-gray-800">{{ number_format($totalProducts) }}</p>
                    </div>
                    <div class="bg-blue-100 p-4 rounded-full">
                        <i class="fas fa-boxes text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Active Products -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Active Products</p>
                        <p class="text-3xl font-bold text-green-600">{{ number_format($activeProducts) }}</p>
                        <p class="text-xs text-gray-500 mt-1">Sold in period</p>
                    </div>
                    <div class="bg-green-100 p-4 rounded-full">
                        <i class="fas fa-shopping-cart text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Average Margin -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Avg Profit Margin</p>
                        <p class="text-3xl font-bold text-purple-600">{{ number_format($averageMargin, 1) }}%</p>
                    </div>
                    <div class="bg-purple-100 p-4 rounded-full">
                        <i class="fas fa-percentage text-purple-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <!-- Low Stock Alerts -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Low Stock Alerts</p>
                        <p class="text-3xl font-bold text-red-600">{{ $lowStockProducts->count() }}</p>
                        <a href="{{ route('product-reports.inventory', ['status' => 'low']) }}" class="text-xs text-blue-600 hover:underline mt-1 inline-block">View details →</a>
                    </div>
                    <div class="bg-red-100 p-4 rounded-full">
                        <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            <!-- Performance Report -->
            <a href="{{ route('product-reports.performance') }}" class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow p-6 block">
                <div class="flex items-center mb-4">
                    <div class="bg-blue-100 p-3 rounded-lg mr-4">
                        <i class="fas fa-chart-bar text-blue-600 text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Performance Report</h3>
                        <p class="text-sm text-gray-600">Sales & revenue analysis</p>
                    </div>
                </div>
                <p class="text-gray-600 text-sm">View detailed sales performance, revenue, and profit metrics for each product.</p>
            </a>

            <!-- Movement Report -->
            <a href="{{ route('product-reports.movement') }}" class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow p-6 block">
                <div class="flex items-center mb-4">
                    <div class="bg-green-100 p-3 rounded-lg mr-4">
                        <i class="fas fa-exchange-alt text-green-600 text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Movement Report</h3>
                        <p class="text-sm text-gray-600">Stock transactions</p>
                    </div>
                </div>
                <p class="text-gray-600 text-sm">Track all product movements including receipts, sales, transfers, and adjustments.</p>
            </a>

            <!-- Profitability Analysis -->
            <a href="{{ route('product-reports.profitability') }}" class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow p-6 block">
                <div class="flex items-center mb-4">
                    <div class="bg-purple-100 p-3 rounded-lg mr-4">
                        <i class="fas fa-chart-line text-purple-600 text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Profitability</h3>
                        <p class="text-sm text-gray-600">Margin & ROI analysis</p>
                    </div>
                </div>
                <p class="text-gray-600 text-sm">Analyze profit margins, ROI, and cost efficiency for each product.</p>
            </a>

            <!-- Sales Trends -->
            <a href="{{ route('product-reports.trends') }}" class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow p-6 block">
                <div class="flex items-center mb-4">
                    <div class="bg-indigo-100 p-3 rounded-lg mr-4">
                        <i class="fas fa-chart-area text-indigo-600 text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Sales Trends</h3>
                        <p class="text-sm text-gray-600">Time-based patterns</p>
                    </div>
                </div>
                <p class="text-gray-600 text-sm">Discover sales trends, patterns, and seasonality in product performance.</p>
            </a>

            <!-- Inventory Status -->
            <a href="{{ route('product-reports.inventory') }}" class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow p-6 block">
                <div class="flex items-center mb-4">
                    <div class="bg-yellow-100 p-3 rounded-lg mr-4">
                        <i class="fas fa-warehouse text-yellow-600 text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Inventory Status</h3>
                        <p class="text-sm text-gray-600">Current stock levels</p>
                    </div>
                </div>
                <p class="text-gray-600 text-sm">Monitor current stock levels, low stock alerts, and overstock warnings.</p>
            </a>
        </div>

        <!-- Top Performing Products -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="border-b border-gray-200 px-6 py-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-800">
                        <i class="fas fa-trophy text-yellow-500 mr-2"></i>Top Performing Products
                    </h2>
                    <a href="{{ route('product-reports.performance') }}" class="text-blue-600 hover:underline text-sm">View All →</a>
                </div>
            </div>
            <div class="p-6">
                @if($topProducts->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rank</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Quantity Sold</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Revenue</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Profit</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($topProducts as $index => $product)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-center w-8 h-8 rounded-full {{ $index === 0 ? 'bg-yellow-100 text-yellow-600' : ($index === 1 ? 'bg-gray-100 text-gray-600' : ($index === 2 ? 'bg-orange-100 text-orange-600' : 'bg-blue-50 text-blue-600')) }} font-bold">
                                                {{ $index + 1 }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-gray-900">{{ $product->name }}</div>
                                            <div class="text-xs text-gray-500">{{ $product->barcode }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <span class="font-medium text-gray-900">{{ number_format($product->total_quantity) }}</span>
                                            <span class="text-xs text-gray-500">units</span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <span class="font-medium text-green-600">₵{{ number_format($product->total_revenue, 2) }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <span class="font-medium text-purple-600">₵{{ number_format($product->total_profit, 2) }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-3"></i>
                        <p>No sales data available for the selected period</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Category Breakdown & Stock Alerts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Category Breakdown -->
            <div class="bg-white rounded-lg shadow">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-xl font-bold text-gray-800">
                        <i class="fas fa-tags text-blue-500 mr-2"></i>Sales by Category
                    </h2>
                </div>
                <div class="p-6">
                    @if($categoryBreakdown->count() > 0)
                        <div class="space-y-4">
                            @foreach($categoryBreakdown as $category)
                                @php
                                    $totalRevenue = $categoryBreakdown->sum('total_revenue');
                                    $percentage = $totalRevenue > 0 ? ($category->total_revenue / $totalRevenue) * 100 : 0;
                                @endphp
                                <div>
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="font-medium text-gray-700">{{ $category->category_name }}</span>
                                        <span class="text-sm text-gray-600">₵{{ number_format($category->total_revenue, 2) }} ({{ number_format($percentage, 1) }}%)</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                                    </div>
                                    <div class="flex justify-between text-xs text-gray-500 mt-1">
                                        <span>{{ number_format($category->total_quantity) }} units</span>
                                        <span>{{ $category->product_count }} products</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-3"></i>
                            <p>No category data available</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Stock Alerts -->
            <div class="bg-white rounded-lg shadow">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-xl font-bold text-gray-800">
                        <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>Stock Alerts
                    </h2>
                </div>
                <div class="p-6">
                    <!-- Low Stock -->
                    @if($lowStockProducts->count() > 0)
                        <div class="mb-6">
                            <h3 class="text-sm font-semibold text-red-600 mb-3 flex items-center">
                                <i class="fas fa-arrow-down mr-2"></i>Low Stock ({{ $lowStockProducts->count() }})
                            </h3>
                            <div class="space-y-2">
                                @foreach($lowStockProducts->take(5) as $item)
                                    <div class="flex justify-between items-center p-2 bg-red-50 rounded">
                                        <div>
                                            <p class="text-sm font-medium text-gray-800">{{ $item->product->name }}</p>
                                            <p class="text-xs text-gray-600">{{ $item->branch->display_label }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-bold text-red-600">{{ $item->stock_quantity }}</p>
                                            <p class="text-xs text-gray-500">Reorder: {{ $item->reorder_level }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Overstock -->
                    @if($overstockProducts->count() > 0)
                        <div>
                            <h3 class="text-sm font-semibold text-yellow-600 mb-3 flex items-center">
                                <i class="fas fa-arrow-up mr-2"></i>Overstock ({{ $overstockProducts->count() }})
                            </h3>
                            <div class="space-y-2">
                                @foreach($overstockProducts->take(5) as $item)
                                    <div class="flex justify-between items-center p-2 bg-yellow-50 rounded">
                                        <div>
                                            <p class="text-sm font-medium text-gray-800">{{ $item->product->name }}</p>
                                            <p class="text-xs text-gray-600">{{ $item->branch->display_label }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm font-bold text-yellow-600">{{ $item->stock_quantity }}</p>
                                            <p class="text-xs text-gray-500">Reorder: {{ $item->reorder_level }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($lowStockProducts->count() === 0 && $overstockProducts->count() === 0)
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-check-circle text-4xl text-green-500 mb-3"></i>
                            <p>All inventory levels are healthy</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
