@extends('layouts.app')

@section('title', 'Cashier Dashboard')

@section('content')
<div class="p-6">
    @php
        $branch = Auth::user()->branch;
        $todaySales = \App\Models\Sale::where('cashier_id', Auth::id())
            ->whereDate('created_at', today())
            ->count();
        $todayRevenue = \App\Models\Sale::where('cashier_id', Auth::id())
            ->whereDate('created_at', today())
            ->sum('total');
    @endphp

    <!-- Welcome Header -->
    <div class="bg-gradient-to-r from-orange-600 to-amber-600 rounded-lg shadow-lg p-8 mb-8 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">Cashier Dashboard</h1>
                <p class="text-orange-100">Branch: <span class="font-semibold">{{ $branch->name ?? 'No Branch Assigned' }}</span></p>
                <p class="text-orange-100 text-sm mt-1">Welcome back, {{ Auth::user()->name }}!</p>
            </div>
            <div class="text-6xl opacity-50">
                <i class="fas fa-cash-register"></i>
            </div>
        </div>
    </div>

    @if(!$branch)
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-lg mb-8">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-yellow-400 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">No Branch Assigned</h3>
                    <p class="text-sm text-yellow-700 mt-1">
                        You are not currently assigned to any branch. Please contact your manager.
                    </p>
                </div>
            </div>
        </div>
    @else
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Today's Sales</p>
                        <p class="text-3xl font-bold text-orange-600 mt-2">{{ $todaySales }}</p>
                    </div>
                    <div class="bg-orange-100 rounded-full p-4">
                        <i class="fas fa-shopping-cart text-orange-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Today's Revenue</p>
                        <p class="text-3xl font-bold text-green-600 mt-2">₵{{ number_format($todayRevenue, 2) }}</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-4">
                        <i class="fas fa-cedi-sign text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Total Sales</p>
                        <p class="text-3xl font-bold text-blue-600 mt-2">{{ $stats['total_sales'] ?? 0 }}</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-4">
                        <i class="fas fa-receipt text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Branch</p>
                        <p class="text-lg font-bold text-purple-600 mt-2">{{ $branch->name }}</p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-4">
                        <i class="fas fa-store text-purple-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">
                <i class="fas fa-bolt text-yellow-500 mr-2"></i>Quick Actions
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="{{ route('sales.terminal') }}" class="flex items-center p-4 bg-orange-50 hover:bg-orange-100 rounded-lg transition-colors border-2 border-orange-200">
                    <div class="bg-orange-600 rounded-full p-3 mr-4">
                        <i class="fas fa-cart-shopping text-white"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">POS Terminal</p>
                        <p class="text-sm text-gray-600">Open point of sale</p>
                    </div>
                </a>

                <a href="{{ route('sales.index') }}" class="flex items-center p-4 bg-blue-50 hover:bg-blue-100 rounded-lg transition-colors border-2 border-blue-200">
                    <div class="bg-blue-600 rounded-full p-3 mr-4">
                        <i class="fas fa-receipt text-white"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800">My Sales</p>
                        <p class="text-sm text-gray-600">View sales history</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Recent Sales -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">
                <i class="fas fa-history text-orange-600 mr-2"></i>Recent Sales
            </h2>
            @php
                $recentSales = \App\Models\Sale::where('cashier_id', Auth::id())
                    ->orderBy('created_at', 'desc')
                    ->take(5)
                    ->get();
            @endphp
            
            @if($recentSales->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sale ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($recentSales as $sale)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#{{ $sale->id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $sale->created_at->format('M d, Y H:i') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₵{{ number_format($sale->total, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                            Completed
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-receipt text-4xl mb-3"></i>
                    <p>No sales yet today</p>
                    <a href="{{ route('sales.terminal') }}" class="text-orange-600 hover:text-orange-800 text-sm mt-2 inline-block">
                        Open POS Terminal
                    </a>
                </div>
            @endif
        </div>
    @endif
</div>
@endsection 