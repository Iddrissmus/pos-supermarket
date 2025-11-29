@extends('layouts.app')

@section('title', 'Manual Product Assignment')

@section('content')
<div class="p-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Manual Product Assignment</h1>
                <p class="text-sm text-gray-600 mt-1">Assign products to branches with detailed control over quantities and pricing</p>
            </div>
            <a href="{{ route('layouts.product') }}" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-times text-xl"></i>
            </a>
        </div>

        <!-- Help Banner -->
        <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-500 text-lg mt-0.5"></i>
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-medium text-blue-800">How to use Manual Assignment</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ol class="list-decimal list-inside space-y-1">
                            <li>Select a branch to assign products to</li>
                            <li>Check the products you want to assign</li>
                            <li>Enter quantities (boxes and units per box)</li>
                            <li>Set selling and cost prices</li>
                            <li>Review the summary and click "Assign to Branch"</li>
                        </ol>
                        <p class="mt-2 text-xs">
                            <strong>Tip:</strong> Need to assign many products at once? Use 
                            <a href="{{ route('inventory.bulk-assignment') }}" class="underline font-medium">Bulk Assignment (Excel Upload)</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Branch Selection -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                {{ count($branches) > 1 ? 'Select Branch *' : 'Branch' }}
            </label>
            @if(count($branches) > 1)
                {{-- Superadmin can select any branch --}}
                <select id="branchSelect" class="w-full md:w-1/2 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- Select Branch to Assign Products --</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" 
                                data-manager="{{ $branch->manager?->name ?? 'No Manager' }}"
                                data-location="{{ $branch->location }}">
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
                <div id="branchInfo" class="mt-2 hidden">
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-3 text-sm">
                        <p class="text-gray-700">
                            <span class="font-medium">Branch Manager:</span> <span id="managerName"></span>
                        </p>
                        <p class="text-gray-700">
                            <span class="font-medium">Location:</span> <span id="branchLocation"></span>
                        </p>
                    </div>
                </div>
            @else
                {{-- Business admin/manager has only one branch - show it as read-only --}}
                @php $singleBranch = $branches->first(); @endphp
                <input 
                    type="text" 
                    value="{{ $singleBranch->name }}" 
                    readonly 
                    class="w-full md:w-1/2 border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-700 cursor-not-allowed"
                >
                <input type="hidden" id="branchSelect" value="{{ $singleBranch->id }}">
                <div class="mt-2">
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-3 text-sm">
                        <p class="text-gray-700">
                            <span class="font-medium">Branch Manager:</span> {{ $singleBranch->manager?->name ?? 'No Manager' }}
                        </p>
                        <p class="text-gray-700">
                            <span class="font-medium">Location:</span> {{ $singleBranch->location }}
                        </p>
                    </div>
                </div>
                <script>
                    // Auto-set branch for single branch users
                    document.addEventListener('DOMContentLoaded', function() {
                        const hiddenBranchInput = document.getElementById('branchSelect');
                        selectedBranchId = hiddenBranchInput.value;
                        enableInputs();
                        updateSummary();
                    });
                </script>
            @endif
        </div>

        <!-- Search and Filter -->
        <div class="mb-4 flex flex-col md:flex-row gap-3">
            <div class="flex-1">
                <input 
                    type="text" 
                    id="productSearch" 
                    placeholder="Search products by name or barcode..."
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500"
                >
            </div>
            <div class="w-full md:w-64">
                <select id="categoryFilter" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                    <option value="">All Categories</option>
                    @foreach($products->pluck('category')->unique()->filter() as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Products Table -->
        <div class="mb-3 bg-green-50 border-l-4 border-green-500 p-3 rounded text-sm">
            <div class="flex items-start gap-2">
                <i class="fas fa-warehouse text-green-600 mt-0.5"></i>
                <div>
                    <span class="font-medium text-green-800">Inventory Status:</span>
                    <span class="text-green-700">
                        <strong>Available</strong> = units you can assign | 
                        <strong>Total</strong> = units in warehouse | 
                        <strong>Assigned</strong> = already distributed to branches
                    </span>
                </div>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="border-b px-4 py-3 text-left">
                            <input type="checkbox" id="selectAll" class="rounded">
                        </th>
                        <th class="border-b px-4 py-3 text-left text-sm font-medium text-gray-700">Product</th>
                        <th class="border-b px-4 py-3 text-left text-sm font-medium text-gray-700">Category</th>
                        <th class="border-b px-4 py-3 text-left text-sm font-medium text-gray-700">Inventory</th>
                        <th class="border-b px-4 py-3 text-center text-sm font-medium text-gray-700">Boxes *</th>
                        <th class="border-b px-4 py-3 text-center text-sm font-medium text-gray-700">Units/Box *</th>
                        <th class="border-b px-4 py-3 text-center text-sm font-medium text-gray-700">Total Units</th>
                        <th class="border-b px-4 py-3 text-center text-sm font-medium text-gray-700">Selling Price</th>
                        <th class="border-b px-4 py-3 text-center text-sm font-medium text-gray-700">Cost Price</th>
                        <th class="border-b px-4 py-3 text-center text-sm font-medium text-gray-700">Line Total</th>
                    </tr>
                </thead>
                <tbody id="productsTable">
                    @foreach($products as $product)
                        @php
                            // Use product's default pricing first, then fall back to first branch assignment
                            $defaultPrice = $product->price ?? $product->branchProducts->first()?->price ?? 0;
                            $defaultCostPrice = $product->cost_price ?? $product->branchProducts->first()?->cost_price ?? 0;
                            
                            // Format the values for display
                            $formattedPrice = number_format($defaultPrice, 2, '.', '');
                            $formattedCost = number_format($defaultCostPrice, 2, '.', '');
                        @endphp
                        <tr class="product-row hover:bg-gray-50" 
                            data-product-id="{{ $product->id }}"
                            data-category-id="{{ $product->category_id }}"
                            data-name="{{ strtolower($product->name) }}"
                            data-barcode="{{ strtolower($product->barcode ?? '') }}"
                            data-default-price="{{ $formattedPrice }}"
                            data-default-cost="{{ $formattedCost }}"
                            title="Debug: Price={{ $formattedPrice }}, Cost={{ $formattedCost }}">
                            <td class="border-b px-4 py-3">
                                <input type="checkbox" class="product-checkbox rounded" value="{{ $product->id }}">
                            </td>
                            <td class="border-b px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $product->name }}</div>
                                <div class="text-xs text-gray-500">
                                    @if($product->description)
                                        {{ Str::limit($product->description, 50) }}
                                    @endif
                                </div>
                                <div class="text-xs text-gray-400 mt-1">
                                    Barcode: {{ $product->barcode ?? 'N/A' }}
                                </div>
                            </td>
                            <td class="border-b px-4 py-3 text-sm text-gray-600">
                                @if($product->category)
                                    <div class="flex items-center">
                                        <span class="inline-block px-2 py-1 bg-blue-50 text-blue-700 text-xs rounded">
                                            {{ $product->category->name }}
                                        </span>
                                    </div>
                                    @if($product->category->parent)
                                        <div class="text-xs text-gray-400 mt-1">
                                            {{ $product->category->parent->name }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-400 italic">Uncategorized</span>
                                @endif
                            </td>
                            <td class="border-b px-4 py-3">
                                <div class="space-y-1">
                                    <!-- Available Units (Main) -->
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-500">Available:</span>
                                        <span class="font-bold text-green-600" data-available-units="{{ $product->available_units }}">
                                            {{ number_format($product->available_units) }}
                                        </span>
                                        <span class="text-xs text-gray-500">units</span>
                                    </div>
                                    
                                    <!-- Total in Warehouse -->
                                    <div class="text-xs text-gray-500">
                                        Total: <span class="font-medium text-gray-700">{{ number_format($product->total_units) }}</span>
                                    </div>
                                    
                                    <!-- Already Assigned -->
                                    <div class="text-xs text-gray-500">
                                        Assigned: <span class="font-medium text-orange-600">{{ number_format($product->assigned_units) }}</span>
                                    </div>
                                    
                                    @if($product->available_units == 0)
                                        <div class="mt-1">
                                            <span class="inline-block px-2 py-0.5 bg-red-100 text-red-700 text-xs rounded">
                                                Out of Stock
                                            </span>
                                        </div>
                                    @elseif($product->available_units < 10)
                                        <div class="mt-1">
                                            <span class="inline-block px-2 py-0.5 bg-yellow-100 text-yellow-700 text-xs rounded">
                                                Low Stock
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="border-b px-4 py-3">
                                <input 
                                    type="number" 
                                    class="boxes-input w-20 border border-gray-300 rounded px-2 py-1 text-center text-sm"
                                    min="0"
                                    max="{{ floor($product->available_units / ($product->quantity_per_box ?: 1)) }}"
                                    data-max-boxes="{{ floor($product->available_units / ($product->quantity_per_box ?: 1)) }}"
                                    placeholder="0"
                                    disabled
                                >
                            </td>
                            <td class="border-b px-4 py-3">
                                <input 
                                    type="number" 
                                    class="units-per-box-input w-20 border border-gray-300 rounded px-2 py-1 text-center text-sm"
                                    min="1"
                                    placeholder="1"
                                    value="{{ $product->quantity_per_box ?? 1 }}"
                                    disabled
                                >
                            </td>
                            <td class="border-b px-4 py-3">
                                <span class="total-units-display inline-block w-20 text-center text-sm font-medium text-gray-700">0</span>
                            </td>
                            <td class="border-b px-4 py-3">
                                <input 
                                    type="number" 
                                    class="price-input w-24 border border-gray-300 rounded px-2 py-1 text-center text-sm"
                                    step="0.01"
                                    min="0"
                                    placeholder="{{ $formattedPrice }}"
                                    disabled
                                >
                            </td>
                            <td class="border-b px-4 py-3">
                                <input 
                                    type="number" 
                                    class="cost-input w-24 border border-gray-300 rounded px-2 py-1 text-center text-sm"
                                    step="0.01"
                                    min="0"
                                    placeholder="{{ $formattedCost }}"
                                    disabled
                                >
                            </td>
                            <td class="border-b px-4 py-3 text-center">
                                <span class="line-total-display font-medium text-gray-900 text-sm">₵0.00</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Summary and Submit -->
        <div class="mt-6 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-6 border border-blue-200">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Left side: Summary stats -->
                <div class="space-y-3">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Assignment Summary</h3>
                    
                    <div class="flex items-center justify-between bg-white rounded-lg px-4 py-2 shadow-sm">
                        <span class="text-sm text-gray-600">
                            <i class="fas fa-box text-blue-500 mr-2"></i>Selected Products:
                        </span>
                        <span id="selectedCount" class="text-lg font-bold text-blue-600">0</span>
                    </div>
                    
                    <div class="flex items-center justify-between bg-white rounded-lg px-4 py-2 shadow-sm">
                        <span class="text-sm text-gray-600">
                            <i class="fas fa-cubes text-purple-500 mr-2"></i>Total Units:
                        </span>
                        <span id="totalUnits" class="text-lg font-bold text-purple-600">0</span>
                    </div>
                    
                    <div class="flex items-center justify-between bg-white rounded-lg px-4 py-2 shadow-sm">
                        <span class="text-sm text-gray-600">
                            <i class="fas fa-money-bill-wave text-green-500 mr-2"></i>Total Cost:
                        </span>
                        <span id="totalCost" class="text-lg font-bold text-green-600">₵0.00</span>
                    </div>
                    
                    <div class="flex items-center justify-between bg-white rounded-lg px-4 py-2 shadow-sm">
                        <span class="text-sm text-gray-600">
                            <i class="fas fa-calculator text-orange-500 mr-2"></i>Avg. Cost/Unit:
                        </span>
                        <span id="avgCostPerUnit" class="text-lg font-bold text-orange-600">₵0.00</span>
                    </div>
                </div>
                
                <!-- Right side: Actions -->
                <div class="flex flex-col justify-center space-y-4">
                    <div class="bg-white rounded-lg p-4 shadow-sm">
                        <p class="text-xs text-gray-500 mb-2">
                            <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                            Assigning products will update the selected branch's inventory
                        </p>
                        <div class="flex space-x-3">
                            <button type="button" id="clearBtn" class="flex-1 px-4 py-3 border-2 border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-all">
                                <i class="fas fa-times mr-2"></i>Clear
                            </button>
                            <button type="button" id="assignBtn" class="flex-1 px-4 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-lg font-medium transition-all shadow-md" disabled>
                                <i class="fas fa-check mr-2"></i>Assign to Branch
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const branchSelect = document.getElementById('branchSelect');
    const branchInfo = document.getElementById('branchInfo');
    const productSearch = document.getElementById('productSearch');
    const categoryFilter = document.getElementById('categoryFilter');
    const selectAll = document.getElementById('selectAll');
    const assignBtn = document.getElementById('assignBtn');
    const clearBtn = document.getElementById('clearBtn');
    const selectedCountEl = document.getElementById('selectedCount');
    const totalCostEl = document.getElementById('totalCost');
    const totalUnitsEl = document.getElementById('totalUnits');
    const avgCostPerUnitEl = document.getElementById('avgCostPerUnit');

    let selectedBranchId = null;

    // Branch selection - handle both select dropdown and hidden input
    if (branchSelect.tagName === 'SELECT') {
        // Superadmin with dropdown
        branchSelect.addEventListener('change', function() {
            selectedBranchId = this.value;
            if (selectedBranchId) {
                const option = this.options[this.selectedIndex];
                document.getElementById('managerName').textContent = option.dataset.manager;
                document.getElementById('branchLocation').textContent = option.dataset.location;
                branchInfo.classList.remove('hidden');
                enableInputs();
            } else {
                branchInfo.classList.add('hidden');
                disableInputs();
            }
            updateSummary();
        });
    } else {
        // Business admin/manager with pre-selected branch (hidden input)
        // Branch is already set by inline script, just ensure it's captured
        if (branchSelect.value) {
            selectedBranchId = branchSelect.value;
        }
    }

    function enableInputs() {
        document.querySelectorAll('.boxes-input, .units-per-box-input, .price-input, .cost-input').forEach(input => {
            input.disabled = false;
        });
    }

    function disableInputs() {
        document.querySelectorAll('.boxes-input, .units-per-box-input, .price-input, .cost-input').forEach(input => {
            input.disabled = true;
        });
    }

    // Auto-calculate total units and line total
    document.querySelectorAll('.boxes-input, .units-per-box-input, .cost-input').forEach(input => {
        input.addEventListener('input', function() {
            const row = this.closest('tr');
            const boxesInput = row.querySelector('.boxes-input');
            const boxes = parseFloat(boxesInput.value) || 0;
            const unitsPerBox = parseFloat(row.querySelector('.units-per-box-input').value) || 1;
            const costPrice = parseFloat(row.querySelector('.cost-input').value) || 0;
            
            // Get available units for this product
            const availableUnitsEl = row.querySelector('[data-available-units]');
            const availableUnits = parseFloat(availableUnitsEl?.dataset.availableUnits || 0);
            const maxBoxes = parseFloat(boxesInput.dataset.maxBoxes || 0);
            
            // Validate: don't allow more boxes than available
            if (boxes > maxBoxes) {
                boxesInput.value = maxBoxes;
                alert(`Only ${maxBoxes} boxes (${availableUnits} units) available for this product!`);
                return;
            }
            
            const totalUnits = boxes * unitsPerBox;
            
            // Double-check total units don't exceed available
            if (totalUnits > availableUnits) {
                const correctedBoxes = Math.floor(availableUnits / unitsPerBox);
                boxesInput.value = correctedBoxes;
                alert(`Only ${availableUnits} units available. Adjusted to ${correctedBoxes} boxes.`);
                return;
            }
            
            row.querySelector('.total-units-display').textContent = totalUnits;
            
            const lineTotal = costPrice * totalUnits;
            row.querySelector('.line-total-display').textContent = '₵' + lineTotal.toFixed(2);
            
            updateSummary();
        });
    });

    // Search functionality
    productSearch.addEventListener('input', filterProducts);
    categoryFilter.addEventListener('change', filterProducts);

    function filterProducts() {
        const searchTerm = productSearch.value.toLowerCase();
        const categoryId = categoryFilter.value;
        
        document.querySelectorAll('.product-row').forEach(row => {
            const name = row.dataset.name;
            const barcode = row.dataset.barcode;
            const rowCategoryId = row.dataset.categoryId;
            
            const matchesSearch = !searchTerm || name.includes(searchTerm) || barcode.includes(searchTerm);
            const matchesCategory = !categoryId || rowCategoryId === categoryId;
            
            row.style.display = (matchesSearch && matchesCategory) ? '' : 'none';
        });
    }

    // Select all
    selectAll.addEventListener('change', function() {
        document.querySelectorAll('.product-checkbox:not([disabled])').forEach(checkbox => {
            if (checkbox.closest('tr').style.display !== 'none') {
                checkbox.checked = this.checked;
            }
        });
        updateSummary();
    });

    // Individual checkbox
    document.querySelectorAll('.product-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const row = this.closest('tr');
            const priceInput = row.querySelector('.price-input');
            const costInput = row.querySelector('.cost-input');
            
            if (this.checked) {
                // Set placeholders from default pricing when checkbox is checked
                const defaultPrice = row.dataset.defaultPrice || '0.00';
                const defaultCost = row.dataset.defaultCost || '0.00';
                
                priceInput.placeholder = defaultPrice;
                costInput.placeholder = defaultCost;
            }
            
            updateSummary();
        });
    });

    // Update summary
    function updateSummary() {
        let count = 0;
        let totalCost = 0;
        let totalUnits = 0;
        
        document.querySelectorAll('.product-checkbox:checked').forEach(checkbox => {
            const row = checkbox.closest('tr');
            const boxes = parseFloat(row.querySelector('.boxes-input').value) || 0;
            const unitsPerBox = parseFloat(row.querySelector('.units-per-box-input').value) || 1;
            const cost = parseFloat(row.querySelector('.cost-input').value) || 0;
            const units = boxes * unitsPerBox;
            
            count++;
            totalUnits += units;
            totalCost += cost * units;
        });
        
        const avgCostPerUnit = totalUnits > 0 ? totalCost / totalUnits : 0;
        
        selectedCountEl.textContent = count;
        totalCostEl.textContent = '₵' + totalCost.toFixed(2);
        totalUnitsEl.textContent = totalUnits.toLocaleString();
        avgCostPerUnitEl.textContent = '₵' + avgCostPerUnit.toFixed(2);
        
        assignBtn.disabled = count === 0 || !selectedBranchId;
    }

    // Clear selection
    clearBtn.addEventListener('click', function() {
        document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = false);
        selectAll.checked = false;
        updateSummary();
    });

    // Assign to branch
    assignBtn.addEventListener('click', async function() {
        if (!selectedBranchId) {
            alert('Please select a branch first');
            return;
        }

        const products = [];
        document.querySelectorAll('.product-checkbox:checked').forEach(checkbox => {
            const row = checkbox.closest('tr');
            const boxes = parseFloat(row.querySelector('.boxes-input').value) || 0;
            const unitsPerBox = parseFloat(row.querySelector('.units-per-box-input').value) || 1;
            
            if (boxes > 0) {
                products.push({
                    product_id: parseInt(checkbox.value),
                    quantity_of_boxes: boxes,
                    quantity_per_box: unitsPerBox,
                    selling_price: parseFloat(row.querySelector('.price-input').value) || null,
                    cost_price: parseFloat(row.querySelector('.cost-input').value) || null,
                    reorder_level: null,
                });
            }
        });

        if (products.length === 0) {
            alert('Please enter box quantities for selected products');
            return;
        }

        assignBtn.disabled = true;
        assignBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Assigning...';

        try {
            const response = await fetch('{{ route("inventory.bulk-assign") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    branch_id: selectedBranchId,
                    products: products
                })
            });

            const data = await response.json();

            if (response.ok) {
                let message = data.message;
                
                if (data.warnings && data.warnings.length > 0) {
                    message += '\n\nWarnings:\n' + data.warnings.join('\n');
                }
                
                alert(message);
                // Redirect to Product Manager to view assigned products
                window.location.href = '{{ route("layouts.productman") }}';
            } else {
                alert('Error: ' + (data.error || 'Unknown error'));
                assignBtn.disabled = false;
                assignBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Assign to Branch';
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to assign products. Please try again.');
            assignBtn.disabled = false;
            assignBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Assign to Branch';
        }
    });
</script>
@endsection
