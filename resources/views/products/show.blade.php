@extends('layouts.app')

@section('title', 'Product Details')

@section('content')
<div class="p-6 space-y-6">
    <!-- Header -->
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('layouts.product') }}" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-arrow-left text-xl"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800">Product Details</h1>
                    <p class="text-sm text-gray-600 mt-1">View and manage product information</p>
                </div>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('product.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors inline-flex items-center">
                    <i class="fas fa-plus mr-2"></i>Add New Product
                </a>
            </div>
        </div>
    </div>

    <!-- Product Information Card -->
    <div class="bg-white shadow rounded-lg p-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Product Image & Basic Info -->
            <div class="lg:col-span-1">
                <!-- Product Image -->
                <div class="mb-6">
                    @if($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" 
                             alt="{{ $product->name }}" 
                             class="w-full h-64 object-cover rounded-lg border-2 border-gray-200">
                    @else
                        <div class="w-full h-64 bg-gray-100 rounded-lg border-2 border-gray-200 flex items-center justify-center">
                            <i class="fas fa-image text-gray-400 text-6xl"></i>
                        </div>
                    @endif
                </div>

                <!-- QR Code -->
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">QR Code</h3>
                    <div class="bg-white border-2 border-gray-200 rounded-lg p-4 flex items-center justify-center">
                        <img src="{{ $product->qr_code_url }}" 
                             alt="QR Code" 
                             class="w-32 h-32"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="w-32 h-32 bg-gray-100 rounded-lg flex items-center justify-center" style="display:none;">
                            <i class="fas fa-qrcode text-gray-400 text-4xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Barcode -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Barcode</h3>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-center space-x-2">
                            <i class="fas fa-barcode text-gray-600"></i>
                            <span class="text-lg font-mono font-semibold text-gray-800">{{ $product->barcode ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Product Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information -->
                <div>
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                        Basic Information
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Product Name</label>
                            <p class="text-gray-900 font-medium">{{ $product->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            @if($product->category)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    <i class="fas {{ $product->category->icon ?? 'fa-tag' }} mr-1"></i>
                                    {{ $product->category->name }}
                                </span>
                            @else
                                <span class="text-gray-400 text-sm">Uncategorized</span>
                            @endif
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <p class="text-gray-600">{{ $product->description ?? 'No description provided' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Business</label>
                            <p class="text-gray-900">{{ $product->business->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Added By</label>
                            <p class="text-gray-900">{{ $product->addedBy->name ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Pricing Information -->
                <div>
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-tag text-green-600 mr-2"></i>
                        Pricing Information
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                            <label class="block text-sm font-medium text-green-700 mb-1">Selling Price</label>
                            <p class="text-2xl font-bold text-green-900">₵{{ number_format($product->price ?? 0, 2) }}</p>
                        </div>
                        <div class="bg-orange-50 rounded-lg p-4 border border-orange-200">
                            <label class="block text-sm font-medium text-orange-700 mb-1">Cost Price</label>
                            <p class="text-2xl font-bold text-orange-900">₵{{ number_format($product->cost_price ?? 0, 2) }}</p>
                        </div>
                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                            <label class="block text-sm font-medium text-blue-700 mb-1">Margin</label>
                            @php
                                $margin = ($product->price ?? 0) - ($product->cost_price ?? 0);
                                $marginPercent = $product->cost_price > 0 ? (($margin / $product->cost_price) * 100) : 0;
                            @endphp
                            <p class="text-2xl font-bold text-blue-900">₵{{ number_format($margin, 2) }}</p>
                            <p class="text-xs text-blue-600 mt-1">{{ number_format($marginPercent, 1) }}%</p>
                        </div>
                    </div>
                </div>

                <!-- Inventory Information -->
                <div>
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-boxes text-purple-600 mr-2"></i>
                        Inventory Information
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                            <label class="block text-sm font-medium text-purple-700 mb-1">Total Boxes</label>
                            <p class="text-2xl font-bold text-purple-900">{{ $product->total_boxes ?? 0 }}</p>
                        </div>
                        <div class="bg-indigo-50 rounded-lg p-4 border border-indigo-200">
                            <label class="block text-sm font-medium text-indigo-700 mb-1">Units Per Box</label>
                            <p class="text-2xl font-bold text-indigo-900">{{ $product->quantity_per_box ?? 0 }}</p>
                        </div>
                        <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                            <label class="block text-sm font-medium text-blue-700 mb-1">Total Units</label>
                            <p class="text-2xl font-bold text-blue-900">{{ $product->total_units ?? 0 }}</p>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                            <label class="block text-sm font-medium text-green-700 mb-1">Available Units</label>
                            <p class="text-2xl font-bold text-green-900">{{ $product->available_units ?? 0 }}</p>
                        </div>
                        <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                            <label class="block text-sm font-medium text-yellow-700 mb-1">Assigned Units</label>
                            <p class="text-2xl font-bold text-yellow-900">{{ $product->assigned_units ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <!-- Branch Assignments -->
                @if($product->branchProducts->count() > 0)
                <div>
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-store text-indigo-600 mr-2"></i>
                        Branch Assignments ({{ $product->branchProducts->count() }})
                    </h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Stock Qty</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Selling Price</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Cost Price</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Reorder Level</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($product->branchProducts as $branchProduct)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="text-sm font-medium text-gray-900">{{ $branchProduct->branch->name ?? 'N/A' }}</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right">
                                            <span class="text-sm {{ $branchProduct->stock_quantity <= 10 ? 'text-red-600 font-semibold' : 'text-gray-900' }}">
                                                {{ $branchProduct->stock_quantity ?? 0 }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right">
                                            <span class="text-sm text-gray-900">₵{{ number_format($branchProduct->price ?? 0, 2) }}</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right">
                                            <span class="text-sm text-gray-600">₵{{ number_format($branchProduct->cost_price ?? 0, 2) }}</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right">
                                            <span class="text-sm text-gray-600">{{ $branchProduct->reorder_level ?? 0 }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- Supplier Information -->
                @if($product->primarySupplier)
                <div>
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-truck text-gray-600 mr-2"></i>
                        Supplier Information
                    </h2>
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <p class="text-gray-900 font-medium">{{ $product->primarySupplier->name }}</p>
                        @if($product->primarySupplier->contact)
                            <p class="text-sm text-gray-600 mt-1">
                                <i class="fas fa-phone mr-1"></i>{{ $product->primarySupplier->contact }}
                            </p>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Weight-Based Selling (if applicable) -->
                @if($product->selling_mode && in_array($product->selling_mode, ['weight', 'box', 'both']))
                <div>
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-weight text-orange-600 mr-2"></i>
                        Weight-Based Pricing
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if($product->box_weight)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Box Weight</label>
                                <p class="text-gray-900">{{ $product->box_weight }} {{ $product->weight_unit ?? 'kg' }}</p>
                            </div>
                        @endif
                        @if($product->price_per_kilo)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Price Per Kilo</label>
                                <p class="text-gray-900">₵{{ number_format($product->price_per_kilo, 2) }}</p>
                            </div>
                        @endif
                        @if($product->price_per_box)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Price Per Box</label>
                                <p class="text-gray-900">₵{{ number_format($product->price_per_box, 2) }}</p>
                            </div>
                        @endif
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Selling Mode</label>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                                {{ ucfirst($product->selling_mode) }}
                            </span>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Timestamps -->
                <div>
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-clock text-gray-600 mr-2"></i>
                        Timestamps
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Created At</label>
                            <p class="text-gray-600">{{ $product->created_at->format('M d, Y h:i A') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Last Updated</label>
                            <p class="text-gray-600">{{ $product->updated_at->format('M d, Y h:i A') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex items-center justify-between">
            <a href="{{ route('layouts.product') }}" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left mr-2"></i>Back to Products
            </a>
            <div class="flex space-x-3">
                @if(auth()->user()->role === 'business_admin' || auth()->user()->role === 'superadmin')
                    <a href="{{ route('product.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors inline-flex items-center">
                        <i class="fas fa-plus mr-2"></i>Add New Product
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

