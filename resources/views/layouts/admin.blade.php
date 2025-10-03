@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="p-6">
    <!-- Green Header Bar -->
    <div class="bg-green-600 text-white px-6 py-4 rounded-t-lg mb-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-bold">Admin Dashboard</h1>
            <div class="flex space-x-3">
                <a href="{{ route('layouts.manage') }}" class="bg-green-700 hover:bg-green-800 px-4 py-2 rounded-lg font-medium transition-colors flex items-center">
                    <i class="fas fa-plus mr-2"></i> Manage Branch
                </a>
            </div>
        </div>
    </div>

    <!-- Welcome Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Welcome, {{ auth()->user()->name }}!</h2>
        <p class="text-gray-600">This is your admin dashboard. You have full system access and can manage all aspects of the application.</p>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-building text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Businesses</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_businesses'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-users text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Users</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_users'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-shopping-cart text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Sales</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_sales'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-chart-line text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">System Status</p>
                    <p class="text-2xl font-semibold text-gray-900">Active</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white rounded-lg shadow-md">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">Recent Sales</h2>
        </div>
        <div class="p-6">
            @if(isset($stats['recent_sales']) && $stats['recent_sales']->count() > 0)
                <div class="space-y-4">
                    @foreach($stats['recent_sales'] as $sale)
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-receipt text-green-600"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">Sale #{{ $sale->id }} - {{ $sale->cashier->name ?? 'Unknown Cashier' }}</p>
                            <p class="text-sm text-gray-500">{{ $sale->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900">${{ number_format($sale->total ?? 0, 2) }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-shopping-cart text-gray-400 text-4xl mb-4"></i>
                    <p class="text-gray-500">No recent sales found.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update the active sidebar item to "Dashboard"
    const sidebarItems = document.querySelectorAll('.sidebar-item');
    sidebarItems.forEach(item => {
        item.classList.remove('active');
        if (item.textContent.includes('Dashboard')) {
            item.classList.add('active');
        }
    });
});
</script>
@endsection 