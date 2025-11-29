@php
    $user = auth()->user();
    $role = $user->role ?? null;
    
    $roleColors = [
        'superadmin' => 'purple',
        'business_admin' => 'blue',
        'manager' => 'green',
        'cashier' => 'orange',
    ];
    $accentColor = $roleColors[$role] ?? 'blue';
    
    $roleDashboardRoute = match ($role) {
        'superadmin' => route('dashboard.superadmin'),
        'business_admin' => route('dashboard.business-admin'),
        'manager' => route('dashboard.manager'),
        'cashier' => route('dashboard.cashier'),
        default => '/',
    };
@endphp

<div class="sidebar bg-white border-r border-gray-200 shadow-sm">
    <div class="flex flex-col h-full">
        <div class="flex-1 py-4 overflow-y-auto">
            <div class="px-4">
                <!-- Dashboard -->
                <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer {{ request()->routeIs('dashboard*') ? 'active bg-'.$accentColor.'-50' : 'hover:bg-gray-50' }}">
                    <i class="fas fa-home sidebar-icon {{ request()->routeIs('dashboard*') ? 'text-'.$accentColor.'-600' : 'text-gray-500' }}"></i>
                    <a href="{{ $roleDashboardRoute }}" class="sidebar-text ml-3 text-sm {{ request()->routeIs('dashboard*') ? 'text-'.$accentColor.'-600 font-semibold' : 'text-gray-700' }}">Dashboard</a>
                </div>

                @if($role === 'superadmin')
                    <!-- SuperAdmin Menu -->
                    <div class="mt-4 mb-2 px-3">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider sidebar-text">System Management</p>
                    </div>
                    
                    <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer {{ request()->routeIs('businesses.index') || request()->routeIs('businesses.create') || request()->routeIs('businesses.edit') || request()->routeIs('businesses.show') ? 'active bg-purple-50' : 'hover:bg-gray-50' }}">
                        <i class="fas fa-building sidebar-icon {{ request()->routeIs('businesses.index') || request()->routeIs('businesses.create') || request()->routeIs('businesses.edit') || request()->routeIs('businesses.show') ? 'text-purple-600' : 'text-gray-500' }}"></i>
                        <a href="{{ route('businesses.index') }}" class="sidebar-text ml-3 text-sm {{ request()->routeIs('businesses.index') || request()->routeIs('businesses.create') || request()->routeIs('businesses.edit') || request()->routeIs('businesses.show') ? 'text-purple-600 font-semibold' : 'text-gray-700' }}">Manage Business</a>
                    </div>
                    
                    <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer {{ request()->routeIs('businesses.map') ? 'active bg-purple-50' : 'hover:bg-gray-50' }}">
                        <i class="fas fa-map-marked-alt sidebar-icon {{ request()->routeIs('businesses.map') ? 'text-purple-600' : 'text-gray-500' }}"></i>
                        <a href="{{ route('businesses.map') }}" class="sidebar-text ml-3 text-sm {{ request()->routeIs('businesses.map') ? 'text-purple-600 font-semibold' : 'text-gray-700' }}">Business Map</a>
                    </div>
                    
                    <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer {{ request()->routeIs('system-users.*') ? 'active bg-purple-50' : 'hover:bg-gray-50' }}">
                        <i class="fas fa-users-cog sidebar-icon {{ request()->routeIs('system-users.*') ? 'text-purple-600' : 'text-gray-500' }}"></i>
                        <a href="{{ route('system-users.index') }}" class="sidebar-text ml-3 text-sm {{ request()->routeIs('system-users.*') ? 'text-purple-600 font-semibold' : 'text-gray-700' }}">System Users</a>
                    </div>
                    
                    <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer {{ request()->routeIs('superadmin.branch-requests.*') ? 'active bg-purple-50' : 'hover:bg-gray-50' }}">
                        <i class="fas fa-clipboard-list sidebar-icon {{ request()->routeIs('superadmin.branch-requests.*') ? 'text-purple-600' : 'text-gray-500' }}"></i>
                        <a href="{{ route('superadmin.branch-requests.index') }}" class="sidebar-text ml-3 text-sm {{ request()->routeIs('superadmin.branch-requests.*') ? 'text-purple-600 font-semibold' : 'text-gray-700' }}">Branch Requests</a>
                    </div>
                    
                    {{-- <div class="mt-4 mb-2 px-3">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider sidebar-text">Inventory</p>
                    </div>
                    
                    <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer {{ request()->routeIs('layouts.product', 'product.*') ? 'active bg-purple-50' : 'hover:bg-gray-50' }}">
                        <i class="fas fa-warehouse sidebar-icon {{ request()->routeIs('layouts.product', 'product.*') ? 'text-purple-600' : 'text-gray-500' }}"></i>
                        <a href="{{ route('layouts.product') }}" class="sidebar-text ml-3 text-sm {{ request()->routeIs('layouts.product', 'product.*') ? 'text-purple-600 font-semibold' : 'text-gray-700' }}">Warehouse Inventory</a>
                    </div>
                    
                    <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer {{ request()->routeIs('layouts.productman') ? 'active bg-purple-50' : 'hover:bg-gray-50' }}">
                        <i class="fas fa-store sidebar-icon {{ request()->routeIs('layouts.productman') ? 'text-purple-600' : 'text-gray-500' }}"></i>
                        <a href="{{ route('layouts.productman') }}" class="sidebar-text ml-3 text-sm {{ request()->routeIs('layouts.productman') ? 'text-purple-600 font-semibold' : 'text-gray-700' }}">Branch Products</a>
                    </div>
                    
                    <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer {{ request()->routeIs('inventory.bulk-import') ? 'active bg-purple-50' : 'hover:bg-gray-50' }}">
                        <i class="fas fa-file-excel sidebar-icon {{ request()->routeIs('inventory.bulk-import') ? 'text-purple-600' : 'text-gray-500' }}"></i>
                        <a href="{{ route('inventory.bulk-import') }}" class="sidebar-text ml-3 text-sm {{ request()->routeIs('inventory.bulk-import') ? 'text-purple-600 font-semibold' : 'text-gray-700' }}">Bulk Import</a>
                    </div>
                    
                    <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer {{ request()->routeIs('inventory.bulk-assignment') ? 'active bg-purple-50' : 'hover:bg-gray-50' }}">
                        <i class="fas fa-upload sidebar-icon {{ request()->routeIs('inventory.bulk-assignment') ? 'text-purple-600' : 'text-gray-500' }}"></i>
                        <a href="{{ route('inventory.bulk-assignment') }}" class="sidebar-text ml-3 text-sm {{ request()->routeIs('inventory.bulk-assignment') ? 'text-purple-600 font-semibold' : 'text-gray-700' }}">Bulk Assignment</a>
                    </div>
                    
                    <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer {{ request()->routeIs('inventory.assign') ? 'active bg-purple-50' : 'hover:bg-gray-50' }}">
                        <i class="fas fa-tasks sidebar-icon {{ request()->routeIs('inventory.assign') ? 'text-purple-600' : 'text-gray-500' }}"></i>
                        <a href="{{ route('inventory.assign') }}" class="sidebar-text ml-3 text-sm {{ request()->routeIs('inventory.assign') ? 'text-purple-600 font-semibold' : 'text-gray-700' }}">Manual Assignment</a>
                    </div>
                    
                    <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer {{ request()->routeIs('suppliers.*') ? 'active bg-purple-50' : 'hover:bg-gray-50' }}">
                        <i class="fas fa-industry sidebar-icon {{ request()->routeIs('suppliers.*') ? 'text-purple-600' : 'text-gray-500' }}"></i>
                        <a href="{{ route('suppliers.index') }}" class="sidebar-text ml-3 text-sm {{ request()->routeIs('suppliers.*') ? 'text-purple-600 font-semibold' : 'text-gray-700' }}">Suppliers</a>
                    </div> --}}

                @elseif($role === 'business_admin')
                    <!-- Business Admin Menu -->
                    <div class="mt-4 mb-2 px-3">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider sidebar-text">Business Management</p>
                    </div>
                    
                    <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer {{ request()->routeIs('businesses.index') || request()->routeIs('businesses.edit') ? 'active bg-blue-50' : 'hover:bg-gray-50' }}">
                        <i class="fas fa-building sidebar-icon {{ request()->routeIs('businesses.index') || request()->routeIs('businesses.edit') ? 'text-blue-600' : 'text-gray-500' }}"></i>
                        <a href="{{ route('businesses.index') }}" class="sidebar-text ml-3 text-sm {{ request()->routeIs('businesses.index') || request()->routeIs('businesses.edit') ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">My Business</a>
                    </div>
                    
                    <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer {{ request()->routeIs('businesses.myMap') ? 'active bg-blue-50' : 'hover:bg-gray-50' }}">
                        <i class="fas fa-map-marked-alt sidebar-icon {{ request()->routeIs('businesses.myMap') ? 'text-blue-600' : 'text-gray-500' }}"></i>
                        <a href="{{ route('businesses.myMap') }}" class="sidebar-text ml-3 text-sm {{ request()->routeIs('businesses.myMap') ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">Branch Locations</a>
                    </div>
                    
                    <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer {{ request()->routeIs('my-branch') ? 'active bg-blue-50' : 'hover:bg-gray-50' }}">
                        <i class="fas fa-store sidebar-icon {{ request()->routeIs('my-branch') ? 'text-blue-600' : 'text-gray-500' }}"></i>
                        <a href="{{ route('my-branch') }}" class="sidebar-text ml-3 text-sm {{ request()->routeIs('my-branch') ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">My Branch</a>
                    </div>
                    
                    <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer {{ request()->routeIs('admin.cashiers.*') ? 'active bg-blue-50' : 'hover:bg-gray-50' }}">
                        <i class="fas fa-users sidebar-icon {{ request()->routeIs('admin.cashiers.*') ? 'text-blue-600' : 'text-gray-500' }}"></i>
                        <a href="{{ route('admin.cashiers.index') }}" class="sidebar-text ml-3 text-sm {{ request()->routeIs('admin.cashiers.*') ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">Manage Staff</a>
                    </div>
                    
                    <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer {{ request()->routeIs('categories.*') ? 'active bg-blue-50' : 'hover:bg-gray-50' }}">
                        <i class="fas fa-tags sidebar-icon {{ request()->routeIs('categories.*') ? 'text-blue-600' : 'text-gray-500' }}"></i>
                        <a href="{{ route('categories.index') }}" class="sidebar-text ml-3 text-sm {{ request()->routeIs('categories.*') ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">Categories</a>
                    </div>
                    
                    <div class="mt-4 mb-2 px-3">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider sidebar-text">Inventory</p>
                    </div>
                    
                    <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer {{ request()->routeIs('layouts.product', 'product.*') ? 'active bg-blue-50' : 'hover:bg-gray-50' }}">
                        <i class="fas fa-warehouse sidebar-icon {{ request()->routeIs('layouts.product', 'product.*') ? 'text-blue-600' : 'text-gray-500' }}"></i>
                        <a href="{{ route('layouts.product') }}" class="sidebar-text ml-3 text-sm {{ request()->routeIs('layouts.product', 'product.*') ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">Warehouse Inventory</a>
                    </div>
                    
                    <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer {{ request()->routeIs('layouts.productman') ? 'active bg-blue-50' : 'hover:bg-gray-50' }}">
                        <i class="fas fa-store sidebar-icon {{ request()->routeIs('layouts.productman') ? 'text-blue-600' : 'text-gray-500' }}"></i>
                        <a href="{{ route('layouts.productman') }}" class="sidebar-text ml-3 text-sm {{ request()->routeIs('layouts.productman') ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">Branch Products</a>
                    </div>
                    
                    <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer {{ request()->routeIs('inventory.bulk-import') ? 'active bg-blue-50' : 'hover:bg-gray-50' }}">
                        <i class="fas fa-file-excel sidebar-icon {{ request()->routeIs('inventory.bulk-import') ? 'text-blue-600' : 'text-gray-500' }}"></i>
                        <a href="{{ route('inventory.bulk-import') }}" class="sidebar-text ml-3 text-sm {{ request()->routeIs('inventory.bulk-import') ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">Bulk Import</a>
                    </div>
                    
                    <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer {{ request()->routeIs('inventory.bulk-assignment') ? 'active bg-blue-50' : 'hover:bg-gray-50' }}">
                        <i class="fas fa-upload sidebar-icon {{ request()->routeIs('inventory.bulk-assignment') ? 'text-blue-600' : 'text-gray-500' }}"></i>
                        <a href="{{ route('inventory.bulk-assignment') }}" class="sidebar-text ml-3 text-sm {{ request()->routeIs('inventory.bulk-assignment') ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">Bulk Assignment</a>
                    </div>
                    
                    <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer {{ request()->routeIs('inventory.assign') ? 'active bg-blue-50' : 'hover:bg-gray-50' }}">
                        <i class="fas fa-tasks sidebar-icon {{ request()->routeIs('inventory.assign') ? 'text-blue-600' : 'text-gray-500' }}"></i>
                        <a href="{{ route('inventory.assign') }}" class="sidebar-text ml-3 text-sm {{ request()->routeIs('inventory.assign') ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">Manual Assignment</a>
                    </div>
                    
                    <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer {{ request()->routeIs('stock-receipts.*') ? 'active bg-blue-50' : 'hover:bg-gray-50' }}">
                        <i class="fas fa-truck sidebar-icon {{ request()->routeIs('stock-receipts.*') ? 'text-blue-600' : 'text-gray-500' }}"></i>
                        <a href="{{ route('stock-receipts.index') }}" class="sidebar-text ml-3 text-sm {{ request()->routeIs('stock-receipts.*') ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">Receive Stock</a>
                    </div>
                    
                    <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer {{ request()->routeIs('suppliers.*') ? 'active bg-blue-50' : 'hover:bg-gray-50' }}">
                        <i class="fas fa-industry sidebar-icon {{ request()->routeIs('suppliers.*') ? 'text-blue-600' : 'text-gray-500' }}"></i>
                        <a href="{{ route('suppliers.index') }}" class="sidebar-text ml-3 text-sm {{ request()->routeIs('suppliers.*') ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">Suppliers</a>
                    </div>
                    
                    <div class="mt-4 mb-2 px-3">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider sidebar-text">Operations</p>
                    </div>
                    
                    <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer {{ request()->routeIs('customers.*') ? 'active bg-blue-50' : 'hover:bg-gray-50' }}">
                        <i class="fas fa-users sidebar-icon {{ request()->routeIs('customers.*') ? 'text-blue-600' : 'text-gray-500' }}"></i>
                        <a href="{{ route('customers.index') }}" class="sidebar-text ml-3 text-sm {{ request()->routeIs('customers.*') ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">Customers</a>
                    </div>
                    
                    <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer {{ request()->routeIs('sales.report*') ? 'active bg-blue-50' : 'hover:bg-gray-50' }}">
                        <i class="fas fa-chart-bar sidebar-icon {{request()->routeIs('sales.report*') ? 'text-blue-600' : 'text-gray-500'}}"></i>
                        <a href="{{route('sales.report')}}" class="sidebar-text ml-3 text-sm {{request()->routeIs('sales.report*') ? 'text-blue-600 font-semibold' : 'text-gray-700'}}">Business Reports</a>
                    </div>

                @elseif($role === 'manager')
                    <!-- Manager Menu -->
                    <div class="mt-4 mb-2 px-3">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider sidebar-text">Branch Operations</p>
                    </div>
                    
                    <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer {{ request()->routeIs('manager.cashiers.*') ? 'active bg-green-50' : 'hover:bg-gray-50' }}">
                        <i class="fas fa-user-friends sidebar-icon {{ request()->routeIs('manager.cashiers.*') ? 'text-green-600' : 'text-gray-500' }}"></i>
                        <a href="{{ route('manager.cashiers.index') }}" class="sidebar-text ml-3 text-sm {{ request()->routeIs('manager.cashiers.*') ? 'text-green-600 font-semibold' : 'text-gray-700' }}">Manage Cashiers</a>
                    </div>
                    
                    <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer {{ request()->routeIs('sales.index') ? 'active bg-green-50' : 'hover:bg-gray-50' }}">
                        <i class="fas fa-receipt sidebar-icon {{ request()->routeIs('sales.index') ? 'text-green-600' : 'text-gray-500' }}"></i>
                        <a href="{{ route('sales.index') }}" class="sidebar-text ml-3 text-sm {{ request()->routeIs('sales.index') ? 'text-green-600 font-semibold' : 'text-gray-700' }}">Branch Sales</a>
                    </div>
                    
                    <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer {{ request()->routeIs('sales.report*') ? 'active bg-green-50' : 'hover:bg-gray-50' }}">
                        <i class="fas fa-chart-line sidebar-icon {{ request()->routeIs('sales.report*') ? 'text-green-600' : 'text-gray-500' }}"></i>
                        <a href="{{ route('sales.report') }}" class="sidebar-text ml-3 text-sm {{ request()->routeIs('sales.report*') ? 'text-green-600 font-semibold' : 'text-gray-700' }}">Sales Reports</a>
                    </div>
                    
                    <div class="mt-4 mb-2 px-3">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider sidebar-text">Inventory</p>
                    </div>
                    
                    <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer {{ request()->routeIs('layouts.product', 'product.*') ? 'active bg-green-50' : 'hover:bg-gray-50' }}">
                        <i class="fas fa-boxes sidebar-icon {{ request()->routeIs('layouts.product', 'product.*') ? 'text-green-600' : 'text-gray-500' }}"></i>
                        <a href="{{ route('layouts.product') }}" class="sidebar-text ml-3 text-sm {{ request()->routeIs('layouts.product', 'product.*') ? 'text-green-600 font-semibold' : 'text-gray-700' }}">Products</a>
                    </div>

                    <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer {{ request()->routeIs('stock-receipts.*') ? 'active bg-green-50' : 'hover:bg-gray-50' }}">
                        <i class="fas fa-truck-loading sidebar-icon {{ request()->routeIs('stock-receipts.*') ? 'text-green-600' : 'text-gray-500' }}"></i>
                        <a href="{{ route('stock-receipts.index') }}" class="sidebar-text ml-3 text-sm {{ request()->routeIs('stock-receipts.*') ? 'text-green-600 font-semibold' : 'text-gray-700' }}">Receive Stock</a>
                    </div>
                    
                    <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer {{ request()->routeIs('suppliers.*') ? 'active bg-green-50' : 'hover:bg-gray-50' }}">
                        <i class="fas fa-people-carry sidebar-icon {{ request()->routeIs('suppliers.*') ? 'text-green-600' : 'text-gray-500' }}"></i>
                        <a href="{{ route('suppliers.index') }}" class="sidebar-text ml-3 text-sm {{ request()->routeIs('suppliers.*') ? 'text-green-600 font-semibold' : 'text-gray-700' }}">Local Suppliers</a>
                    </div>
                    
                    <div class="mt-4 mb-2 px-3">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider sidebar-text">Requests</p>
                    </div>
                    
                    <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer {{ request()->routeIs('manager.item-requests.*') ? 'active bg-green-50' : 'hover:bg-gray-50' }}">
                        <i class="fas fa-box-open sidebar-icon {{ request()->routeIs('manager.item-requests.*') ? 'text-green-600' : 'text-gray-500' }}"></i>
                        <a href="{{ route('manager.item-requests.index') }}" class="sidebar-text ml-3 text-sm {{ request()->routeIs('manager.item-requests.*') ? 'text-green-600 font-semibold' : 'text-gray-700' }}">Request Items</a>
                    </div>
                    
                    <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer {{ request()->routeIs('reorder.requests') ? 'active bg-green-50' : 'hover:bg-gray-50' }}">
                        <i class="fas fa-redo sidebar-icon {{ request()->routeIs('reorder.requests') ? 'text-green-600' : 'text-gray-500' }}"></i>
                        <a href="{{ route('reorder.requests') }}" class="sidebar-text ml-3 text-sm {{ request()->routeIs('reorder.requests') ? 'text-green-600 font-semibold' : 'text-gray-700' }}">Reorder Requests</a>
                    </div>
                    
                    <div class="mt-4 mb-2 px-3">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider sidebar-text">Information</p>
                    </div>
                    
                    <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer {{ request()->routeIs('customers.*') ? 'active bg-green-50' : 'hover:bg-gray-50' }}">
                        <i class="fas fa-address-book sidebar-icon {{ request()->routeIs('customers.*') ? 'text-green-600' : 'text-gray-500' }}"></i>
                        <a href="{{ route('customers.index') }}" class="sidebar-text ml-3 text-sm {{ request()->routeIs('customers.*') ? 'text-green-600 font-semibold' : 'text-gray-700' }}">Customers</a>
                    </div>
                    
                    <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer {{ request()->routeIs('notifications.*') ? 'active bg-green-50' : 'hover:bg-gray-50' }}">
                        <i class="fas fa-bell sidebar-icon {{ request()->routeIs('notifications.*') ? 'text-green-600' : 'text-gray-500' }}"></i>
                        <a href="{{ route('notifications.index') }}" class="sidebar-text ml-3 text-sm {{ request()->routeIs('notifications.*') ? 'text-green-600 font-semibold' : 'text-gray-700' }}">Notifications</a>
                    </div>

                @elseif($role === 'cashier')
                    <!-- Cashier Menu -->
                    <div class="mt-4 mb-2 px-3">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider sidebar-text">Point of Sale</p>
                    </div>
                    
                    <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer {{ request()->routeIs('sales.terminal') ? 'active bg-orange-50' : 'hover:bg-gray-50' }}">
                        <i class="fas fa-cart-shopping sidebar-icon {{ request()->routeIs('sales.terminal') ? 'text-orange-600' : 'text-gray-500' }}"></i>
                        <a href="{{ route('sales.terminal') }}" class="sidebar-text ml-3 text-sm {{ request()->routeIs('sales.terminal') ? 'text-orange-600 font-semibold' : 'text-gray-700' }}">POS Terminal</a>
                    </div>
                    
                    <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer {{ request()->routeIs('sales.index') ? 'active bg-orange-50' : 'hover:bg-gray-50' }}">
                        <i class="fas fa-receipt sidebar-icon {{ request()->routeIs('sales.index') ? 'text-orange-600' : 'text-gray-500' }}"></i>
                        <a href="{{ route('sales.index') }}" class="sidebar-text ml-3 text-sm {{ request()->routeIs('sales.index') ? 'text-orange-600 font-semibold' : 'text-gray-700' }}">My Sales</a>
                    </div>
                    
                    {{-- <div class="mt-4 mb-2 px-3">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider sidebar-text">Quick Info</p>
                    </div>
                    
                    <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer hover:bg-gray-50">
                        <i class="fas fa-box sidebar-icon text-gray-500"></i>
                        <span class="sidebar-text ml-3 text-sm text-gray-700">Product Stock</span>
                    </div> --}}
                @endif

                <!-- Common Items for All Roles -->
                <div class="mt-6 pt-4 border-t border-gray-200">
                    <div class="sidebar-item flex items-center px-3 py-2.5 rounded-lg mb-1 cursor-pointer hover:bg-gray-50">
                        <i class="fas fa-cog sidebar-icon text-gray-500"></i>
                        <span class="sidebar-text text-sm text-gray-700 ml-3">Settings</span>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" class="inline w-full">
                        @csrf
                        <button type="submit" class="sidebar-item w-full flex items-center px-3 py-2.5 rounded-lg cursor-pointer text-left hover:bg-red-50 group">
                            <i class="fas fa-sign-out-alt sidebar-icon text-gray-500 group-hover:text-red-600"></i>
                            <span class="sidebar-text text-sm text-gray-700 ml-3 group-hover:text-red-600">Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar Footer -->
        <div class="sidebar-footer border-t border-gray-200 p-4 bg-gray-50">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-{{ $accentColor }}-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    @if($role === 'superadmin')
                        <i class="fas fa-crown text-{{ $accentColor }}-600"></i>
                    @elseif($role === 'business_admin')
                        <i class="fas fa-briefcase text-{{ $accentColor }}-600"></i>
                    @elseif($role === 'manager')
                        <i class="fas fa-users-cog text-{{ $accentColor }}-600"></i>
                    @elseif($role === 'cashier')
                        <i class="fas fa-cash-register text-{{ $accentColor }}-600"></i>
                    @endif
                </div>
                <div class="sidebar-text overflow-hidden">
                    <p class="text-xs text-gray-500 truncate">Logged in as</p>
                    <p class="text-sm font-semibold text-gray-800 truncate">
                        @if($role === 'superadmin') System Admin
                        @elseif($role === 'business_admin') Business Admin
                        @elseif($role === 'manager') Branch Manager
                        @elseif($role === 'cashier') Cashier
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

