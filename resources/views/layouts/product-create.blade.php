@extends('layouts.app')

@section('title', 'Add Product')

@section('content')
<div class="p-6 max-w-3xl mx-auto">
    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold mb-4">Add Product</h1>

        <form id="productForm" action="{{ route('product.store') }}" method="POST" enctype="multipart/form-data" data-method="POST">
            @csrf
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Product Name</label>
                    <input id="product_name" name="name" type="text" required class="mt-1 block w-full border rounded-md p-2" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="product_description" name="description" class="mt-1 block w-full border rounded-md p-2" rows="4"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Category *</label>
                    <select id="product_category" name="category_id" required class="mt-1 block w-full border rounded-md p-2">
                        <option value="">-- Select Category --</option>
                        @php
                            $user = auth()->user();
                            $categories = \App\Models\Category::where('business_id', $user->business_id)
                                ->active()
                                ->parents()
                                ->with('subcategories')
                                ->orderBy('display_order')
                                ->get();
                        @endphp
                        @foreach($categories as $category)
                            <optgroup label="{{ $category->name }}">
                                @if($category->subcategories->isEmpty())
                                    <option value="{{ $category->id }}">
                                        {{ $category->name }}
                                    </option>
                                @else
                                    @foreach($category->subcategories as $subcategory)
                                        <option value="{{ $subcategory->id }}">
                                            {{ $subcategory->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </optgroup>
                        @endforeach
                    </select>
                    <small class="text-gray-500">Select the most specific category for this product</small>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Price</label>
                        <input id="product_price" name="price" type="number" step="0.01" class="mt-1 block w-full border rounded-md p-2" />
                    </div>
                    
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Stock Quantity</label>
                        <input id="product_stock_quantity" name="stock_quantity" type="number" min="0" class="mt-1 block w-full border rounded-md p-2" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Cost Price (optional)</label>
                        <input id="product_cost_price" name="cost_price" type="number" step="0.01" class="mt-1 block w-full border rounded-md p-2" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">SKU</label>
                    <input id="product_sku" name="sku" type="text" class="mt-1 block w-full border rounded-md p-2" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Image</label>
                    <input id="product_image" name="image" type="file" accept="image/*" class="mt-1 block w-full" />
                </div>

                <div class="flex justify-end space-x-3 mt-4">
                    <a href="{{ route('layouts.product') }}" class="px-4 py-2 border rounded-lg">Cancel</a>
                    <button type="submit" id="submitBtn" class="px-4 py-2 bg-green-600 text-white rounded-lg">Save Product</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function generateSKU(name) {
        const namePart = (name || '').toUpperCase().replace(/\s+/g, '').substring(0, 3);
        const randomPart = Math.floor(Math.random() * 1000000).toString().padStart(6, '0');
        return namePart + randomPart;
    }

    const nameInput = document.getElementById('product_name');
    const skuInput = document.getElementById('product_sku');
    const form = document.getElementById('productForm');

    nameInput.addEventListener('blur', function() {
        if (this.value && !skuInput.value) {
            skuInput.value = generateSKU(this.value);
        }
    });

    form.addEventListener('submit', async function(e) {
        // Let default form submit happen for now; fallback to fetch for AJAX if desired
        // We use a simple progressive enhancement: server will accept multipart/form-data
    });
});
</script>

@endsection
