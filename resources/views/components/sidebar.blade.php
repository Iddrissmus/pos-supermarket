<div class="sidebar bg-gray-100 border-r border-gray-200">
    <div class="flex flex-col h-full">
        <div class="flex-1 py-4">
            <div class="px-4">
                <div class="sidebar-item active flex items-center px-3 py-2 rounded-lg mb-1 cursor-pointer">
                    <i class="fas fa-home mr-3 text-gray-600"></i>
                    <a href="{{route('layouts.productman')}}" class="text-gray-700">Home</a>
                </div>

                <div class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 cursor-pointer">
                    <i class="fas fa-th-large mr-3 text-gray-600"></i>
                    <a href="{{route('dashboard')}}" class="text-gray-700">Dashboard</a>
                </div>

                <div class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 cursor-pointer">
                    <i class="fas fa-box mr-3 text-gray-600"></i>
                    <a href="{{route('layouts.product')}}" class="text-gray-700">Products</a>
                </div>

                <div class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 cursor-pointer">
                    <i class="fas fa-plus-circle mr-3 text-gray-600"></i>
                    <a href="{{ route('product.create') }}" class="text-gray-700">Add Product</a>
                </div>

                <div class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 cursor-pointer">
                    <i class="fas fa-truck mr-3 text-gray-600"></i>
                    <a href="{{ route('stock-receipts.index') }}" class="text-gray-700">Receive Stock</a>
                </div>

                <div class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 cursor-pointer">
                    <i class="fas fa-industry mr-3 text-gray-600"></i>
                    <a href="{{ route('suppliers.index') }}" class="text-gray-700">Suppliers</a>
                </div>

                <div class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 cursor-pointer">
                    <i class="fas fa-cash-register mr-3 text-gray-600"></i>
                    <a href="{{ route('sales.create') }}" class="text-gray-700">New Sale</a>
                </div>

                <div class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 cursor-pointer">
                    <i class="fas fa-receipt mr-3 text-gray-600"></i>
                    <a href="{{ route('sales.index') }}" class="text-gray-700">All Sales</a>
                </div>

                <div class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 cursor-pointer">
                    <i class="fas fa-chart-bar mr-3 text-gray-600"></i>
                    <a href="{{ route('sales.report') }}" class="text-gray-700">Sales Report</a>
                </div>

                <div class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 cursor-pointer">
                    <i class="fas fa-building mr-3 text-gray-600"></i>
                    <a href="{{ route('layouts.manage') }}" class="text-gray-700">Manage Business</a>
                </div>

                <div class="mt-6">
                    <div class="sidebar-item flex items-center justify-between px-3 py-2 rounded-lg mb-1 cursor-pointer">
                        <div class="flex items-center">
                            <i class="fas fa-comment mr-3 text-gray-600"></i>
                            <a class="text-gray-700" href="{{ route('reorder.requests') }}">Messages</a>
                        </div>
                        <span class="badge">2</span>
                    </div>
                </div>

                <div class="mt-6">
                    <div class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 cursor-pointer">
                        <i class="fas fa-cog mr-3 text-gray-600"></i>
                        <span class="text-gray-700">Settings</span>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="sidebar-item w-full flex items-center px-3 py-2 rounded-lg mb-1 cursor-pointer text-left">
                            <i class="fas fa-sign-out-alt mr-3 text-gray-600"></i>
                            <span class="text-gray-700">Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="px-4 py-4 border-t border-gray-200">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-gray-400 rounded-full mr-3"></div>
                <div>
                    <div class="text-sm font-medium text-gray-700">{{ auth()->user()->name }}</div>
                    <div class="text-xs text-gray-500">{{ auth()->user()->email }}</div>
                </div>
            </div>
        </div>
    </div>
</div>


