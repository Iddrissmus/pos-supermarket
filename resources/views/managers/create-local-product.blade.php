@extends('layouts.app')

@section('title', 'Add Local Supplier Product')

@section('content')
<div class="p-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="bg-green-600 text-white px-6 py-4 rounded-t-lg mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold">Add Product from Local Supplier</h1>
                    <p class="text-green-100 text-sm mt-1">Create a new product and add initial stock</p>
                </div>
                <a href="{{ route('suppliers.index') }}" class="bg-green-700 hover:bg-green-800 px-4 py-2 rounded-lg font-medium transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Suppliers
                </a>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <h3 class="text-red-800 font-medium mb-2">Please correct the following errors:</h3>
                <ul class="list-disc list-inside text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-md p-6">
            <form action="{{ route('manager.local-product.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <!-- Supplier Selection -->
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">
                        <i class="fas fa-people-carry text-green-600 mr-2"></i>Supplier Information
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Local Supplier *</label>
                            <select name="supplier_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
                                <option value="">Select Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id', $selectedSupplierId ?? '') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-gray-500">Only local suppliers are shown</small>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Receipt Number (Optional)</label>
                            <input type="text" name="receipt_number" value="{{ old('receipt_number') }}" 
                                   placeholder="Auto-generated if empty"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
                        </div>
                    </div>
                </div>

                <hr class="my-6">

                <!-- Product Information -->
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">
                        <i class="fas fa-box text-green-600 mr-2"></i>Product Details
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Product Name *</label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                   placeholder="e.g., Plantain Chips - Original"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea name="description" rows="3" 
                                      placeholder="Product description..."
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">{{ old('description') }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                            <select name="category_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <optgroup label="{{ $category->name }}">
                                        @if($category->subcategories->isEmpty())
                                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @else
                                            @foreach($category->subcategories as $subcategory)
                                                <option value="{{ $subcategory->id }}" {{ old('category_id') == $subcategory->id ? 'selected' : '' }}>
                                                    {{ $subcategory->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Barcode (Auto-generated if left empty)</label>
                            <input type="text" name="barcode" value="{{ old('barcode') }}" 
                                   placeholder="Product code"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Product Image (Optional)</label>
                            <input type="file" name="image" accept="image/*" 
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
                        </div>
                    </div>
                </div>

                <hr class="my-6">

                <!-- Pricing & Stock Information -->
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">
                        <i class="fas fa-dollar-sign text-green-600 mr-2"></i>Pricing & Stock
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cost Price (per unit) *</label>
                            <input type="number" name="cost_price" value="{{ old('cost_price') }}" required step="0.01" min="0"
                                   placeholder="0.00"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
                            <small class="text-gray-500">What you pay the supplier</small>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Selling Price *</label>
                            <input type="number" name="price" value="{{ old('price') }}" required step="0.01" min="0"
                                   placeholder="0.00"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
                            <small class="text-gray-500">What customers pay</small>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Initial Stock Quantity *</label>
                            <input type="number" name="stock_quantity" value="{{ old('stock_quantity') }}" required min="1"
                                   placeholder="0"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
                            <small class="text-gray-500">How many units received</small>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Reorder Level (Optional)</label>
                            <input type="number" name="reorder_level" value="{{ old('reorder_level', 10) }}" min="0"
                                   placeholder="10"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
                            <small class="text-gray-500">Alert when stock is low</small>
                        </div>
                    </div>
                </div>

                <hr class="my-6">

                <!-- Receipt Information -->
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">
                        <i class="fas fa-calendar-alt text-green-600 mr-2"></i>Receipt Details
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date Received *</label>
                            <input type="date" name="received_date" value="{{ old('received_date', date('Y-m-d')) }}" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                            <textarea name="notes" rows="2" 
                                      placeholder="Additional notes about this product or delivery..."
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Summary Box -->
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                    <h3 class="font-semibold text-green-800 mb-2">What happens when you submit:</h3>
                    <ul class="text-sm text-green-700 space-y-1">
                        <li><i class="fas fa-check-circle mr-2"></i>A new product will be created in your branch inventory</li>
                        <li><i class="fas fa-check-circle mr-2"></i>Initial stock will be added from the selected supplier</li>
                        <li><i class="fas fa-check-circle mr-2"></i>A stock receipt record will be generated for tracking</li>
                        <li><i class="fas fa-check-circle mr-2"></i>The product will be available for sale immediately</li>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('suppliers.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                        <i class="fas fa-plus mr-2"></i>Create Product & Receive Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
