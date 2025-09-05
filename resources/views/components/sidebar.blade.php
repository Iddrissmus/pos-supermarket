<div class="sidebar bg-gray-100 border-r border-gray-200">
    <div class="flex flex-col h-full">
        <div class="flex-1 py-4">
            <div class="px-4">
                <div class="sidebar-item active flex items-center px-3 py-2 rounded-lg mb-1 cursor-pointer">
                    <i class="fas fa-home mr-3 text-gray-600"></i>
                    {{-- <span class="text-gray-700 font-medium">Home</span> --}}
                    <a href="{{route('layouts.productman')}}">Home</a>
                </div>

                <div class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 cursor-pointer">
                    <i class="fas fa-th-large mr-3 text-gray-600"></i>
                    <a href="{{route('dashboard')}}">Dashboard</a>
                </div>

                <div class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 cursor-pointer">
                    <i class="fas fa-box mr-3 text-gray-600"></i>
                    <a href="{{route('layouts.product')}}">Products</a>
                </div>

                <div class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 cursor-pointer">
                    <i class="fas fa-chart-line mr-3 text-gray-600"></i>
                    <span class="text-gray-700">Stocks</span>
                </div>

                <div class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 cursor-pointer">
                    <i class="fas fa-file-alt mr-3 text-gray-600"></i>
                    <span class="text-gray-700">Reports</span>
                </div>

                <div class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 cursor-pointer">
                    <i class="fas fa-warehouse mr-3 text-gray-600"></i>
                    <span class="text-gray-700">Warehouse</span>
                </div>

                <div class="sidebar-item flex items-center px-3 py-2 rounded-lg mb-1 cursor-pointer">
                    <i class="fas fa-history mr-3 text-gray-600"></i>
                    <span class="text-gray-700">History</span>
                </div>

                <div class="sidebar-item flex items-center justify-between px-3 py-2 rounded-lg mb-1 cursor-pointer">
                    <div class="flex items-center">
                        <i class="fas fa-dollar-sign mr-3 text-gray-600"></i>
                        <span class="text-gray-700">Sales</span>
                    </div>
                    <i class="fas fa-chevron-down text-gray-500 text-xs"></i>
                </div>

                <div class="mt-6">
                    <div class="sidebar-item flex items-center justify-between px-3 py-2 rounded-lg mb-1 cursor-pointer">
                        <div class="flex items-center">
                            <i class="fas fa-comment mr-3 text-gray-600"></i>
                            <span class="text-gray-700">Messages</span>
                        </div>
                        <span class="badge">2</span>
                    </div>

                    <div class="sidebar-item flex items-center justify-between px-3 py-2 rounded-lg mb-1 cursor-pointer">
                        <div class="flex items-center">
                            <i class="fas fa-bell mr-3 text-gray-600"></i>
                            <span class="text-gray-700">Notifications</span>
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


