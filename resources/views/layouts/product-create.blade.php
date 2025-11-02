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
                        <label class="block text-sm font-medium text-gray-700">Selling Price *</label>
                        <input id="product_price" name="price" type="number" step="0.01" required class="mt-1 block w-full border rounded-md p-2" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Cost Price (optional)</label>
                        <input id="product_cost_price" name="cost_price" type="number" step="0.01" class="mt-1 block w-full border rounded-md p-2" />
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-blue-900 mb-3 flex items-center gap-2">
                        <i class="fas fa-box"></i>
                        Box Quantity Tracking
                    </h3>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Quantity of Boxes *</label>
                            <input id="quantity_of_boxes" name="quantity_of_boxes" type="number" min="0" required class="mt-1 block w-full border rounded-md p-2" placeholder="e.g., 5" />
                            <p class="text-xs text-gray-500 mt-1">Number of boxes</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Units per Box *</label>
                            <input id="quantity_per_box" name="quantity_per_box" type="number" min="1" required class="mt-1 block w-full border rounded-md p-2" placeholder="e.g., 24" />
                            <p class="text-xs text-gray-500 mt-1">Units in each box</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Total Units</label>
                            <input id="total_units" name="stock_quantity" type="number" class="mt-1 block w-full border rounded-md p-2 bg-gray-100" readonly />
                            <p class="text-xs text-gray-500 mt-1">Auto-calculated</p>
                        </div>
                    </div>
                    <p class="text-xs text-blue-700 mt-2">
                        <i class="fas fa-info-circle"></i>
                        Example: 5 boxes × 24 units/box = 120 total units
                    </p>
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

    // Box quantity calculation
    const quantityOfBoxesInput = document.getElementById('quantity_of_boxes');
    const quantityPerBoxInput = document.getElementById('quantity_per_box');
    const totalUnitsInput = document.getElementById('total_units');

    function calculateTotalUnits() {
        const boxes = parseInt(quantityOfBoxesInput.value) || 0;
        const perBox = parseInt(quantityPerBoxInput.value) || 0;
        const total = boxes * perBox;
        
        totalUnitsInput.value = total > 0 ? total : '';
    }

    quantityOfBoxesInput.addEventListener('input', calculateTotalUnits);
    quantityPerBoxInput.addEventListener('input', calculateTotalUnits);

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
