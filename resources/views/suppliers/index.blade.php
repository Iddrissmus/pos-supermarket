@extends('layouts.app')

@section('title', 'Supplier Management')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto space-y-8">

    <!-- Notification Container -->
    <div id="notification-container" class="fixed top-4 right-4 z-50 space-y-2 pointer-events-none"></div>
    @if (session('success')) <div class="hidden" id="flash-success">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="hidden" id="flash-error">{{ session('error') }}</div> @endif

    <!-- Modern Header -->
    <div class="relative bg-gradient-to-r from-blue-700 to-sky-700 rounded-xl shadow-lg overflow-hidden">
        <div class="absolute inset-0 bg-white/10" style="background-image: radial-gradient(circle at 10% 20%, rgba(255,255,255,0.1) 0%, transparent 20%), radial-gradient(circle at 90% 80%, rgba(255,255,255,0.1) 0%, transparent 20%);"></div>
        <div class="relative p-8 flex flex-col md:flex-row items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-bold text-white tracking-tight flex items-center">
                    <i class="fas fa-truck-loading mr-3 text-blue-200"></i> Supplier Management
                </h1>
                <p class="mt-2 text-blue-100 text-lg opacity-90 max-w-2xl">
                    Manage relationships with manufacturers, distributors, and local vendors.
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                 @if(auth()->user()->role === 'manager')
                    <a href="{{ route('manager.local-product.create') }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg transition-colors font-medium backdrop-blur-sm border border-white/10 flex items-center">
                        <i class="fas fa-box-open mr-2"></i> Add Local Product
                    </a>
                @endif
                <a href="{{ route('suppliers.create') }}" class="px-4 py-2 bg-white text-blue-700 hover:bg-blue-50 rounded-lg transition-colors font-bold shadow-sm flex items-center">
                    <i class="fas fa-plus mr-2"></i> Add Supplier
                </a>
            </div>
        </div>
    </div>

    @if(auth()->user()->role === 'manager')
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg shadow-sm">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-blue-500 text-lg mt-0.5 mr-3"></i>
                <div class="text-sm text-blue-800">
                    <p class="font-medium">Managing Local Suppliers</p>
                    <p class="mt-1 opacity-90">
                        You can add and manage inventory for <strong>local vendors</strong>. Central suppliers are managed by business admins.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
         <!-- Total -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group hover:border-blue-200 transition-colors">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Suppliers</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <h3 class="text-2xl font-bold text-gray-900">{{ $suppliers->total() }}</h3>
                </div>
            </div>
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-users text-xl"></i>
            </div>
        </div>

        <!-- Active -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group hover:border-green-200 transition-colors">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Active Partners</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <h3 class="text-2xl font-bold text-gray-900">{{ $suppliers->where('is_active', true)->count() }}</h3>
                </div>
            </div>
             <div class="w-12 h-12 bg-green-50 text-green-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-handshake text-xl"></i>
            </div>
        </div>

        <!-- Warehouses -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group hover:border-yellow-200 transition-colors">
             <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Warehouses</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <h3 class="text-2xl font-bold text-gray-900">{{ $suppliers->where('type', 'warehouse')->count() }}</h3>
                </div>
            </div>
            <div class="w-12 h-12 bg-yellow-50 text-yellow-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-warehouse text-xl"></i>
            </div>
        </div>

        <!-- Manufacturers -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between group hover:border-purple-200 transition-colors">
             <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Manufacturers</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <h3 class="text-2xl font-bold text-gray-900">{{ $suppliers->where('type', 'manufacturer')->count() }}</h3>
                </div>
            </div>
            <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="fas fa-industry text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Suppliers Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        
        <!-- Filters -->
        <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex flex-col md:flex-row gap-4 justify-between items-center">
            <div class="relative w-full md:w-96">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input type="text" id="searchInput" placeholder="Search suppliers by name, email..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white">
            </div>
            <div class="flex gap-2 w-full md:w-auto">
                <select id="typeFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">All Types</option>
                    <option value="warehouse">Warehouse</option>
                    <option value="external">External</option>
                    <option value="manufacturer">Manufacturer</option>
                </select>
                <select id="statusFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200" id="suppliersTable">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Supplier</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($suppliers as $supplier)
                        <tr class="hover:bg-gray-50/80 transition-colors group" data-type="{{ $supplier->type }}" data-status="{{ $supplier->is_active ? 'active' : 'inactive' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded-full flex items-center justify-center text-gray-500 font-bold text-sm border border-gray-200">
                                        {{ substr($supplier->name, 0, 1) }}
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 group-hover:text-blue-600 transition-colors">{{ $supplier->name }}</div>
                                        @if($supplier->address)
                                            <div class="text-xs text-gray-500">{{ Str::limit($supplier->address, 30) }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    @if($supplier->type === 'warehouse') bg-yellow-100 text-yellow-800
                                    @elseif($supplier->type === 'manufacturer') bg-purple-100 text-purple-800
                                    @else bg-blue-100 text-blue-800 @endif">
                                    {{ ucfirst($supplier->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col text-sm text-gray-500">
                                    @if($supplier->contact_person)
                                        <span class="text-gray-900 font-medium">{{ $supplier->contact_person }}</span>
                                    @endif
                                    <span>{{ $supplier->phone ?? 'No Phone' }}</span>
                                </div>
                            </td>
                             <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-2.5 w-2.5 rounded-full mr-2 {{ $supplier->is_active ? 'bg-green-500' : 'bg-red-500' }}"></div>
                                    <span class="text-sm text-gray-700">{{ $supplier->is_active ? 'Active' : 'Inactive' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('suppliers.show', $supplier) }}" class="text-gray-500 hover:text-blue-600 transition-colors" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                     @if(auth()->user()->role === 'manager' && !$supplier->is_central)
                                        <a href="{{ route('manager.local-product.create', ['supplier_id' => $supplier->id]) }}" 
                                           class="text-gray-500 hover:text-purple-600" 
                                           title="Add New Product">
                                            <i class="fas fa-box-open"></i>
                                        </a>
                                        <a href="{{ route('stock-receipts.create', ['supplier_id' => $supplier->id]) }}" 
                                           class="text-gray-500 hover:text-green-600" 
                                           title="Receive Stock">
                                            <i class="fas fa-truck-loading"></i>
                                        </a>
                                    @endif

                                    <a href="{{ route('suppliers.edit', $supplier) }}" class="text-gray-500 hover:text-indigo-600 transition-colors" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    
                                    <form action="{{ route('suppliers.toggle-status', $supplier) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-gray-500 hover:text-yellow-600 transition-colors" 
                                                title="{{ $supplier->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="fas fa-power-off"></i>
                                        </button>
                                    </form>

                                    @if($supplier->stockReceipts()->count() === 0)
                                        <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="inline" 
                                              onsubmit="return confirm('Are you sure you want to delete this supplier?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-gray-500 hover:text-red-600 transition-colors" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                     <i class="fas fa-search text-gray-400 text-2xl"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900">No suppliers found</h3>
                                <p class="text-gray-500 mt-1 mb-6">Try adjusting your filters or add a new supplier.</p>
                                <a href="{{ route('suppliers.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                    <i class="fas fa-plus mr-2"></i> Add Supplier
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($suppliers->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                {{ $suppliers->links() }}
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const typeFilter = document.getElementById('typeFilter');
    const statusFilter = document.getElementById('statusFilter');
    const tableRows = document.querySelectorAll('#suppliersTable tbody tr');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedType = typeFilter.value;
        const selectedStatus = statusFilter.value;

        tableRows.forEach(row => {
            if (row.cells.length === 1) return; // Skip empty/loading rows

            const name = row.cells[0].textContent.toLowerCase();
            const type = row.dataset.type;
            const status = row.dataset.status;

            const matchesSearch = name.includes(searchTerm);
            const matchesType = !selectedType || type === selectedType;
            const matchesStatus = !selectedStatus || status === selectedStatus;

            row.style.display = (matchesSearch && matchesType && matchesStatus) ? '' : 'none';
        });
    }

    if(searchInput) searchInput.addEventListener('keyup', filterTable);
    if(typeFilter) typeFilter.addEventListener('change', filterTable);
    if(statusFilter) statusFilter.addEventListener('change', filterTable);

    // Notification Logic
    const successMsg = document.getElementById('flash-success');
    const errorMsg = document.getElementById('flash-error');
    
    if(successMsg) showNotification(successMsg.innerText, 'success');
    if(errorMsg) showNotification(errorMsg.innerText, 'error');

    function showNotification(message, type = 'success') {
        const container = document.getElementById('notification-container');
        const notif = document.createElement('div');
        const bg = type === 'success' ? 'bg-green-500' : 'bg-red-500'; // Simple colors
        
        notif.className = `transform transition-all duration-300 ease-out translate-x-full opacity-0 flex items-center p-4 mb-4 text-sm text-white rounded-lg shadow-lg ${bg}`;
        notif.innerHTML = `
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>
            <span>${message}</span>
        `;
        
        container.appendChild(notif);
        requestAnimationFrame(() => notif.classList.remove('translate-x-full', 'opacity-0'));
        setTimeout(() => {
            notif.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => notif.remove(), 300);
        }, 4000);
    }
});
</script>
@endsection