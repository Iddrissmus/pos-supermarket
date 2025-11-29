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
                    <label class="block text-sm font-medium text-gray-700">Product Name *</label>
                    <input id="product_name" name="name" type="text" required class="mt-1 block w-full border rounded-md p-2" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="product_description" name="description" class="mt-1 block w-full border rounded-md p-2" rows="4"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Category *</label>
                    <div class="relative">
                        <!-- Search Input -->
                        <input type="text" 
                            id="category_search" 
                            placeholder="Search categories..." 
                            class="w-full border rounded-md p-2 pr-10"
                            autocomplete="off"
                        />
                        <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
                        
                        <!-- Hidden select for form submission -->
                        <select id="product_category" name="category_id" required class="hidden">
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
                                @if($category->subcategories->isEmpty())
                                    <option value="{{ $category->id }}" data-name="{{ strtolower($category->name) }}">
                                        {{ $category->name }}
                                    </option>
                                @else
                                    @foreach($category->subcategories as $subcategory)
                                        <option value="{{ $subcategory->id }}" data-name="{{ strtolower($subcategory->name) }}" data-parent="{{ strtolower($category->name) }}">
                                            {{ $category->name }} → {{ $subcategory->name }}
                                        </option>
                                    @endforeach
                                @endif
                            @endforeach
                            <option value="new">+ Create New Category</option>
                        </select>
                        
                        <!-- Dropdown Results -->
                        <div id="category_dropdown" class="hidden absolute z-10 w-full mt-1 bg-white border rounded-md shadow-lg max-h-60 overflow-y-auto">
                            <div id="category_results"></div>
                            <div class="border-t p-2 hover:bg-gray-50 cursor-pointer text-blue-600" data-value="new">
                                <i class="fas fa-plus-circle"></i> Create New Category
                            </div>
                        </div>
                    </div>
                    <input type="text" id="new_category_name" name="new_category_name" placeholder="Enter new category name" class="mt-2 hidden w-full border rounded-md p-2" />
                    <small class="text-gray-500">Search and select category or create a new one</small>
                </div>

                <!-- Weight-Based Selling (Optional) - MOVED TO TOP -->
                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-purple-900 mb-3 flex items-center gap-2">
                        <i class="fas fa-weight"></i>
                        Weight-Based Selling (Optional)
                    </h3>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Selling Mode</label>
                        <select id="selling_mode" name="selling_mode" class="block w-full border rounded-md p-2">
                            <option value="unit">By Unit (Default)</option>
                            <option value="weight">By Weight</option>
                            <option value="box">By Box</option>
                            <option value="both">Both Unit & Weight</option>
                        </select>
                    </div>

                    <div id="weight_fields" class="space-y-4 hidden">
                        <!-- Box Weight -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Weight per Box (kg)</label>
                            <input id="box_weight" name="box_weight" type="number" step="0.001" min="0" class="mt-1 block w-full border rounded-md p-2" placeholder="e.g., 12.500" />
                            <p class="text-xs text-gray-500 mt-1">Optional: Weight of one full box in kilograms</p>
                        </div>

                        <!-- Price Per Kilo -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Price per Kilogram</label>
                            <input id="price_per_kilo" name="price_per_kilo" type="number" step="0.01" min="0" class="mt-1 block w-full border rounded-md p-2" placeholder="e.g., 25.00" />
                            <p class="text-xs text-gray-500 mt-1">Selling price per kg</p>
                        </div>

                        <!-- Price Per Box -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Price per Box</label>
                            <input id="price_per_box" name="price_per_box" type="number" step="0.01" min="0" class="mt-1 block w-full border rounded-md p-2" placeholder="e.g., 300.00" />
                            <p class="text-xs text-gray-500 mt-1">Selling price for complete box</p>
                        </div>

                        <!-- Other Weight Units -->
                        <div class="grid grid-cols-2 gap-4 pt-3 border-t border-purple-200">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Alternative Weight Unit</label>
                                <select id="weight_unit" name="weight_unit" class="block w-full border rounded-md p-2">
                                    <option value="">-- None --</option>
                                    <option value="g">Grams (g)</option>
                                    <option value="kg">Kilograms (kg)</option>
                                    <option value="ton">Tons</option>
                                    <option value="lb">Pounds (lb)</option>
                                    <option value="oz">Ounces (oz)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Price per Unit Weight</label>
                                <input id="price_per_unit_weight" name="price_per_unit_weight" type="number" step="0.01" min="0" class="mt-1 block w-full border rounded-md p-2" placeholder="e.g., 0.025" />
                            </div>
                        </div>
                    </div>

                    <p class="text-xs text-purple-700 mt-3">
                        <i class="fas fa-info-circle"></i>
                        Enable weight-based selling for products like rice, flour, sugar sold by weight
                    </p>
                </div>

                <!-- Regular Pricing -->
                <div id="regular_pricing" class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Selling Price <span id="price_required">*</span></label>
                        <input id="product_price" name="price" type="number" step="0.01" class="mt-1 block w-full border rounded-md p-2" />
                        <p class="text-xs text-gray-500 mt-1" id="price_hint">Price per unit</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Cost Price *</label>
                        <input id="product_cost_price" name="cost_price" type="number" step="0.01" required class="mt-1 block w-full border rounded-md p-2" />
                        <p class="text-xs text-gray-500 mt-1">Your purchase cost per unit</p>
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

    // Category searchable dropdown
    const categorySearch = document.getElementById('category_search');
    const categorySelect = document.getElementById('product_category');
    const categoryDropdown = document.getElementById('category_dropdown');
    const categoryResults = document.getElementById('category_results');
    const newCategoryInput = document.getElementById('new_category_name');

    // Build category options array for searching
    const categoryOptions = Array.from(categorySelect.options).filter(opt => opt.value !== '').map(opt => ({
        value: opt.value,
        text: opt.textContent.trim(),
        name: opt.dataset.name || '',
        parent: opt.dataset.parent || ''
    }));

    function filterCategories(searchTerm) {
        const term = searchTerm.toLowerCase();
        return categoryOptions.filter(opt => {
            if (opt.value === 'new') return false;
            return opt.name.includes(term) || opt.parent.includes(term);
        });
    }

    function showDropdown() {
        categoryDropdown.classList.remove('hidden');
    }

    function hideDropdown() {
        setTimeout(() => categoryDropdown.classList.add('hidden'), 200);
    }

    categorySearch.addEventListener('focus', function() {
        const filtered = filterCategories(this.value);
        renderResults(filtered);
        showDropdown();
    });

    categorySearch.addEventListener('input', function() {
        const filtered = filterCategories(this.value);
        renderResults(filtered);
        showDropdown();
    });

    categorySearch.addEventListener('blur', hideDropdown);

    function renderResults(results) {
        if (results.length === 0) {
            categoryResults.innerHTML = '<div class="p-3 text-gray-500 text-sm">No categories found</div>';
            return;
        }

        categoryResults.innerHTML = results.map(cat => 
            `<div class="p-2 hover:bg-blue-50 cursor-pointer border-b" data-value="${cat.value}">
                ${cat.text}
            </div>`
        ).join('');

        // Add click handlers
        categoryResults.querySelectorAll('[data-value]').forEach(el => {
            el.addEventListener('click', function() {
                selectCategory(this.dataset.value, this.textContent.trim());
            });
        });
    }

    // Handle "Create New Category" click
    categoryDropdown.querySelector('[data-value="new"]').addEventListener('click', function() {
        selectCategory('new', '+ Create New Category');
    });

    function selectCategory(value, text) {
        categorySelect.value = value;
        if (value === 'new') {
            categorySearch.value = '';
            newCategoryInput.classList.remove('hidden');
            newCategoryInput.required = true;
            categorySelect.required = false;
        } else {
            categorySearch.value = text;
            newCategoryInput.classList.add('hidden');
            newCategoryInput.required = false;
            categorySelect.required = true;
        }
        hideDropdown();
    }

    // Show all categories on initial focus
    categorySearch.addEventListener('focus', function() {
        if (!this.value) {
            renderResults(categoryOptions.filter(opt => opt.value !== 'new'));
        }
    });

    // Old category change handler (keep for compatibility)
    categorySelect.addEventListener('change', function() {
        if (this.value === 'new') {
            newCategoryInput.classList.remove('hidden');
            newCategoryInput.required = true;
            categorySelect.required = false;
        } else {
            newCategoryInput.classList.add('hidden');
            newCategoryInput.required = false;
            categorySelect.required = true;
        }
    });

    // Weight-based selling toggle
    const sellingModeSelect = document.getElementById('selling_mode');
    const weightFieldsDiv = document.getElementById('weight_fields');
    const priceInput = document.getElementById('product_price');
    const priceRequired = document.getElementById('price_required');
    const priceHint = document.getElementById('price_hint');

    function toggleWeightFields() {
        const mode = sellingModeSelect.value;
        if (mode === 'weight' || mode === 'box' || mode === 'both') {
            weightFieldsDiv.classList.remove('hidden');
            // Disable selling price for weight-based selling (use weight pricing instead)
            priceInput.disabled = true;
            priceInput.required = false;
            priceInput.value = ''; // Clear the value
            priceRequired.classList.add('hidden');
            priceHint.textContent = 'Not used for weight-based selling';
            priceInput.classList.add('bg-gray-100', 'cursor-not-allowed');
        } else {
            weightFieldsDiv.classList.add('hidden');
            // Enable and require selling price for unit-based selling
            priceInput.disabled = false;
            priceInput.required = true;
            priceRequired.classList.remove('hidden');
            priceHint.textContent = 'Price per unit';
            priceInput.classList.remove('bg-gray-100', 'cursor-not-allowed');
        }
    }

    sellingModeSelect.addEventListener('change', toggleWeightFields);
    toggleWeightFields(); // Initialize on page load

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
