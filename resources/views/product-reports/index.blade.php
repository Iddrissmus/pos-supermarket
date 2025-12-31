@extends('layouts.app')

@section('title', 'Product Analytics Dashboard')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto space-y-8">
    
    <!-- Modern Header -->
    <div class="relative bg-gradient-to-r from-violet-600 to-indigo-600 rounded-xl shadow-lg overflow-hidden">
        <div class="absolute inset-0 bg-white/10" style="background-image: radial-gradient(circle at 10% 20%, rgba(255,255,255,0.1) 0%, transparent 20%), radial-gradient(circle at 90% 80%, rgba(255,255,255,0.1) 0%, transparent 20%);"></div>
        <div class="relative p-8 flex flex-col md:flex-row items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-bold text-white tracking-tight flex items-center">
                    <i class="fas fa-chart-pie mr-3 text-violet-200"></i> Product Analytics
                </h1>
                <p class="mt-2 text-violet-100 text-lg opacity-90 max-w-2xl">
                    Comprehensive product performance insights and inventory health monitoring.
                </p>
            </div>
            <div class="text-right bg-white/10 p-4 rounded-lg backdrop-blur-sm border border-white/10">
                <p class="text-xs text-violet-200 uppercase tracking-widest font-semibold">Analysis Period</p>
                <p class="text-xl font-bold text-white mt-1">{{ $dateRange['start']->format('M d, Y') }} — {{ $dateRange['end']->format('M d, Y') }}</p>
            </div>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Products -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between hover:border-violet-200 transition-colors group">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Products</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <h3 class="text-3xl font-bold text-gray-900">{{ number_format($totalProducts) }}</h3>
                </div>
            </div>
            <div class="w-12 h-12 bg-gray-50 text-gray-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-boxes text-xl"></i>
            </div>
        </div>

        <!-- Active Products -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between hover:border-emerald-200 transition-colors group">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Active Portfolio</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <h3 class="text-3xl font-bold text-emerald-600">{{ number_format($activeProducts) }}</h3>
                </div>
                <p class="text-xs text-gray-400 mt-1">Sold in period</p>
            </div>
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
        </div>

        <!-- Average Margin -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between hover:border-blue-200 transition-colors group">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Avg Profit Margin</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <h3 class="text-3xl font-bold text-blue-600">{{ number_format($averageMargin, 1) }}%</h3>
                </div>
            </div>
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-percentage text-xl"></i>
            </div>
        </div>

        <!-- Low Stock Alerts -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between hover:border-red-200 transition-colors group">
             <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Low Stock Alerts</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <h3 class="text-3xl font-bold text-red-600">{{ $lowStockProducts->count() }}</h3>
                </div>
                <a href="{{ route('product-reports.inventory', ['status' => 'low']) }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium mt-1 inline-flex items-center">
                    View details <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            <div class="w-12 h-12 bg-red-50 text-red-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-exclamation-triangle text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Navigation Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Performance Report -->
        <a href="{{ route('product-reports.performance') }}" class="block bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md hover:border-blue-300 transition-all group h-full">
            <div class="flex items-center mb-4">
                <div class="bg-blue-50 p-3 rounded-lg mr-4 group-hover:bg-blue-100 transition-colors">
                    <i class="fas fa-chart-bar text-blue-600 text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-blue-700 transition-colors">Performance Report</h3>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Sales & Revenue</p>
                </div>
            </div>
            <p class="text-gray-600 text-sm">View detailed sales performance, revenue, and profit metrics for each product.</p>
        </a>

        <!-- Movement Report -->
        <a href="{{ route('product-reports.movement') }}" class="block bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md hover:border-teal-300 transition-all group h-full">
            <div class="flex items-center mb-4">
                <div class="bg-teal-50 p-3 rounded-lg mr-4 group-hover:bg-teal-100 transition-colors">
                    <i class="fas fa-exchange-alt text-teal-600 text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-teal-700 transition-colors">Movement Report</h3>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Stock Transactions</p>
                </div>
            </div>
            <p class="text-gray-600 text-sm">Track all product movements including receipts, sales, transfers, and adjustments.</p>
        </a>

        <!-- Profitability Analysis -->
        <a href="{{ route('product-reports.profitability') }}" class="block bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md hover:border-emerald-300 transition-all group h-full">
            <div class="flex items-center mb-4">
                <div class="bg-emerald-50 p-3 rounded-lg mr-4 group-hover:bg-emerald-100 transition-colors">
                    <i class="fas fa-chart-line text-emerald-600 text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-emerald-700 transition-colors">Profitability</h3>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Margin & ROI</p>
                </div>
            </div>
            <p class="text-gray-600 text-sm">Analyze profit margins, ROI, and cost efficiency for each product.</p>
        </a>

        <!-- Sales Trends -->
        <a href="{{ route('product-reports.trends') }}" class="block bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md hover:border-purple-300 transition-all group h-full">
            <div class="flex items-center mb-4">
                <div class="bg-purple-50 p-3 rounded-lg mr-4 group-hover:bg-purple-100 transition-colors">
                    <i class="fas fa-chart-area text-purple-600 text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-purple-700 transition-colors">Sales Trends</h3>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Time-based Patterns</p>
                </div>
            </div>
            <p class="text-gray-600 text-sm">Discover sales trends, patterns, and seasonality in product performance.</p>
        </a>

        <!-- Inventory Status -->
        <a href="{{ route('product-reports.inventory') }}" class="block bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md hover:border-amber-300 transition-all group h-full">
            <div class="flex items-center mb-4">
                <div class="bg-amber-50 p-3 rounded-lg mr-4 group-hover:bg-amber-100 transition-colors">
                    <i class="fas fa-warehouse text-amber-600 text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-amber-700 transition-colors">Inventory Status</h3>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wide">Current Levels</p>
                </div>
            </div>
            <p class="text-gray-600 text-sm">Monitor current stock levels, low stock alerts, and overstock warnings.</p>
        </a>
    </div>

    <!-- Top Performing Products -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-gray-50/50">
            <h2 class="text-lg font-bold text-gray-800 flex items-center">
                <i class="fas fa-trophy text-yellow-500 mr-2"></i> Top Performing Products
            </h2>
            <a href="{{ route('product-reports.performance') }}" class="text-sm font-bold text-violet-600 hover:text-violet-800 transition-colors">View Full Report →</a>
        </div>
        
        @if($topProducts->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-16">Rank</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Product Info</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Units Sold</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Revenue</th>
                            <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Profit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach($topProducts as $index => $product)
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center justify-center w-8 h-8 rounded-full {{ $index === 0 ? 'bg-yellow-100 text-yellow-700' : ($index === 1 ? 'bg-gray-100 text-gray-600' : ($index === 2 ? 'bg-orange-100 text-orange-700' : 'bg-gray-50 text-gray-500')) }} font-bold text-sm shadow-sm ring-1 ring-inset ring-gray-900/5">
                                        {{ $index + 1 }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-bold text-gray-900">{{ $product->name }}</div>
                                    <div class="text-xs text-gray-500 font-mono">{{ $product->barcode }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <span class="font-semibold text-gray-700">{{ number_format($product->total_quantity) }}</span>
                                    <span class="text-xs text-gray-400 ml-1">units</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <span class="font-bold text-emerald-600">₵{{ number_format($product->total_revenue, 2) }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <span class="font-bold text-violet-600">₵{{ number_format($product->total_profit, 2) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-12 px-6">
                <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-inbox text-gray-400 text-3xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-1">No performance data</h3>
                <p class="text-gray-500">No sales recorded for the selected period.</p>
            </div>
        @endif
    </div>

    <!-- Category & Stock Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Category Breakdown -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/50">
                <h2 class="text-lg font-bold text-gray-800 flex items-center">
                    <i class="fas fa-tags text-blue-500 mr-2"></i> Sales by Category
                </h2>
            </div>
            <div class="p-6">
                @if($categoryBreakdown->count() > 0)
                    <div class="space-y-5">
                        @foreach($categoryBreakdown->take(5) as $category)
                            @php
                                $totalRevenue = $categoryBreakdown->sum('total_revenue');
                                $percentage = $totalRevenue > 0 ? ($category->total_revenue / $totalRevenue) * 100 : 0;
                            @endphp
                            <div>
                                <div class="flex justify-between items-end mb-1">
                                    <span class="font-semibold text-gray-900 text-sm">{{ $category->category_name }}</span>
                                    <div class="text-right">
                                        <span class="font-bold text-gray-900 text-sm">₵{{ number_format($category->total_revenue, 2) }}</span>
                                        <span class="text-xs text-gray-500 ml-1">({{ number_format($percentage, 0) }}%)</span>
                                    </div>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $percentage }}%"></div>
                                </div>
                                <div class="flex justify-between text-[11px] text-gray-400 mt-1 uppercase font-semibold tracking-wider">
                                    <span>{{ number_format($category->total_quantity) }} units sold</span>
                                    <span>{{ $category->product_count }} products</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-chart-pie text-gray-300 text-4xl mb-3 block"></i>
                        <p>No category data available</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Stock Alerts Widget -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/50">
                <h2 class="text-lg font-bold text-gray-800 flex items-center">
                    <i class="fas fa-bell text-red-500 mr-2"></i> Inventory Alerts
                </h2>
            </div>
            <div class="p-6">
                <div class="space-y-6">
                    <!-- Low Stock Section -->
                    @if($lowStockProducts->count() > 0)
                        <div>
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 flex items-center">
                                <span class="w-2 h-2 rounded-full bg-red-500 mr-2"></span> Low Stock ({{ $lowStockProducts->count() }})
                            </h3>
                            <div class="space-y-3">
                                @foreach($lowStockProducts->take(3) as $item)
                                    <div class="flex items-center justify-between p-3 rounded-lg bg-red-50 border border-red-100 group hover:border-red-200 transition-colors">
                                        <div class="flex-1 min-w-0 mr-3">
                                            <p class="text-sm font-bold text-gray-900 truncate">{{ $item->product->name }}</p>
                                            <p class="text-xs text-gray-500 truncate">{{ $item->branch->display_label }}</p>
                                        </div>
                                        <div class="text-right whitespace-nowrap">
                                            <p class="text-sm font-bold text-red-600">{{ $item->stock_quantity }} left</p>
                                            <p class="text-xs text-red-400">Reorder at {{ $item->reorder_level }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Overstock Section -->
                    @if($overstockProducts->count() > 0)
                        <div>
                            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 flex items-center">
                                <span class="w-2 h-2 rounded-full bg-orange-500 mr-2"></span> Overstock ({{ $overstockProducts->count() }})
                            </h3>
                            <div class="space-y-3">
                                @foreach($overstockProducts->take(3) as $item)
                                    <div class="flex items-center justify-between p-3 rounded-lg bg-orange-50 border border-orange-100 group hover:border-orange-200 transition-colors">
                                        <div class="flex-1 min-w-0 mr-3">
                                            <p class="text-sm font-bold text-gray-900 truncate">{{ $item->product->name }}</p>
                                            <p class="text-xs text-gray-500 truncate">{{ $item->branch->display_label }}</p>
                                        </div>
                                        <div class="text-right whitespace-nowrap">
                                            <p class="text-sm font-bold text-orange-600">{{ $item->stock_quantity }} units</p>
                                            <p class="text-xs text-orange-400 text-opacity-80">Limit: {{ $item->reorder_level * 3 }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($lowStockProducts->count() === 0 && $overstockProducts->count() === 0)
                        <div class="h-full flex flex-col items-center justify-center text-center py-8">
                            <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mb-3">
                                <i class="fas fa-check text-green-500 text-2xl"></i>
                            </div>
                            <h3 class="text-gray-900 font-bold">Healthy Inventory</h3>
                            <p class="text-gray-500 text-sm mt-1">All stock levels are optimized.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
