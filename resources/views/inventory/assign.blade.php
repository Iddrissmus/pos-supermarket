@extends('layouts.app')

@section('title', 'Manual Product Assignment')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto space-y-8">
    
    <!-- Modern Header -->
    <div class="relative bg-gradient-to-r from-teal-700 to-emerald-800 rounded-xl shadow-lg overflow-hidden">
        <div class="absolute inset-0 bg-white/10" style="background-image: radial-gradient(circle at 10% 20%, rgba(255,255,255,0.1) 0%, transparent 20%), radial-gradient(circle at 90% 80%, rgba(255,255,255,0.1) 0%, transparent 20%);"></div>
        <div class="relative p-8 flex flex-col md:flex-row items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-bold text-white tracking-tight flex items-center">
                    <i class="fas fa-dolly-flatbed mr-3 text-teal-200"></i> Manual Assignment
                </h1>
                <p class="mt-2 text-teal-100 text-lg opacity-90 max-w-2xl">
                    Assign products to branches with detailed control over quantities and pricing.
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                 <a href="{{ route('layouts.product') }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-lg transition-colors font-medium backdrop-blur-sm border border-white/10 flex items-center">
                    <i class="fas fa-arrow-left mr-2 opacity-80"></i> Back to Inventory
                </a>
                <a href="{{ route('inventory.bulk-assignment') }}" class="px-4 py-2 bg-white text-teal-700 hover:bg-teal-50 rounded-lg transition-colors font-bold shadow-sm flex items-center">
                    <i class="fas fa-file-excel mr-2"></i> Bulk Upload
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Column: Controls & Summary -->
        <div class="space-y-6">
            <!-- Branch Selection Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4 border-b border-gray-100 pb-2">Target Branch</h3>
                
                <div class="space-y-4">
                    <label class="block text-sm font-medium text-gray-700">
                        {{ count($branches) > 1 ? 'Select Branch' : 'Assigned Branch' }}
                    </label>
                    
                    @if(count($branches) > 1)
                        <div class="relative">
                            <select id="branchSelect" class="tom-select block w-full pl-3 pr-10 py-3 text-base border-gray-300 focus:outline-none focus:ring-teal-500 focus:border-teal-500 sm:text-sm rounded-lg bg-gray-50 hover:bg-white transition-colors">
                                <option value="">-- Choose Branch --</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" 
                                            data-manager="{{ $branch->manager?->name ?? 'No Manager' }}"
                                            data-location="{{ $branch->location }}">
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div id="branchInfo" class="hidden bg-teal-50 rounded-lg p-4 border border-teal-100 mt-4 animate-fade-in-down">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 bg-teal-100 rounded-full p-2 text-teal-600">
                                    <i class="fas fa-store"></i>
                                </div>
                                <div class="text-sm">
                                    <p class="font-medium text-teal-900">Branch Details</p>
                                    <p class="text-teal-700 mt-1"><span class="opacity-75">Manager:</span> <span id="managerName" class="font-semibold"></span></p>
                                    <p class="text-teal-700"><span class="opacity-75">Location:</span> <span id="branchLocation" class="font-semibold"></span></p>
                                </div>
                            </div>
                        </div>
                    @else
                        @php $singleBranch = $branches->first(); @endphp
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                             <div class="flex items-center gap-3 mb-2">
                                <i class="fas fa-store text-gray-400 text-xl"></i>
                                <span class="font-bold text-gray-800 text-lg">{{ $singleBranch->name }}</span>
                             </div>
                             <p class="text-sm text-gray-600 ml-8 mb-1"><i class="fas fa-user-tie w-4 text-center mr-1"></i> {{ $singleBranch->manager?->name ?? 'No Manager' }}</p>
                             <p class="text-sm text-gray-600 ml-8"><i class="fas fa-map-marker-alt w-4 text-center mr-1"></i> {{ $singleBranch->location }}</p>
                        </div>
                        <input type="hidden" id="branchSelect" value="{{ $singleBranch->id }}">
                        
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                selectedBranchId = "{{ $singleBranch->id }}";
                                enableInputs();
                                updateSummary();
                            });
                        </script>
                    @endif
                </div>
            </div>

            <!-- Sticky Summary Card -->
            <div class="bg-white rounded-xl shadow-lg border border-teal-100 p-6 sticky top-6 z-10">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-clipboard-check text-teal-600"></i> Summary
                </h3>
                
                <div class="space-y-3 mb-6">
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                        <span class="text-sm text-gray-600">Items Selected</span>
                        <span id="selectedCount" class="font-bold text-gray-900 text-lg">0</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                        <span class="text-sm text-gray-600">Total Units</span>
                        <span id="totalUnits" class="font-bold text-teal-600 text-lg">0</span>
                    </div>
                     <div class="flex justify-between items-center p-3 bg-teal-50 rounded-lg border border-teal-100">
                        <span class="text-sm text-teal-800 font-medium">Total Cost Value</span>
                        <span id="totalCost" class="font-bold text-teal-700 text-xl">₵0.00</span>
                    </div>
                </div>

                <button type="button" id="assignBtn" class="w-full py-4 px-6 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white rounded-xl font-bold shadow-md transition-all transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2" disabled>
                    <i class="fas fa-check-circle text-xl"></i>
                    <span>Confirm Assignment</span>
                </button>
                 <button type="button" id="clearBtn" class="w-full mt-3 py-2 text-sm text-gray-500 hover:text-red-500 hover:underline transition-colors text-center">
                    Clear Selection
                </button>
            </div>
        </div>

        <!-- Right Column: Product Table -->
        <div class="lg:col-span-2 space-y-4">
            
            <!-- Filters -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 flex flex-col sm:flex-row gap-4">
                <div class="flex-1 relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" id="productSearch" placeholder="Search products..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 bg-gray-50 focus:bg-white transition-colors">
                </div>
                <div class="w-full sm:w-48">
                    <select id="categoryFilter" class="tom-select w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-teal-500 bg-gray-50">
                        <option value="">All Categories</option>
                         @foreach($products->pluck('category')->unique()->filter() as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Table Container -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50/50 text-gray-500 text-xs uppercase font-semibold tracking-wider">
                            <tr>
                                <th class="p-4 w-12 text-center">
                                    <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-teal-600 focus:ring-teal-500 w-4 h-4 cursor-pointer">
                                </th>
                                <th class="p-4">Product Info</th>
                                <th class="p-4 text-center">Availability</th>
                                <th class="p-4 text-center w-32">Boxes <span class="text-red-500">*</span></th>
                                <th class="p-4 text-center w-24">Units/Box</th>
                                <th class="p-4 text-right w-32">Price (₵)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100" id="productsTable">
                            @foreach($products as $product)
                                @php
                                    $defaultPrice = $product->price ?? $product->branchProducts->first()?->price ?? 0;
                                    $defaultCostPrice = $product->cost_price ?? $product->branchProducts->first()?->cost_price ?? 0;
                                    $formattedPrice = number_format($defaultPrice, 2, '.', '');
                                    $formattedCost = number_format($defaultCostPrice, 2, '.', '');
                                @endphp
                                <tr class="product-row hover:bg-teal-50/30 transition-colors group"
                                     data-product-id="{{ $product->id }}"
                                     data-category-id="{{ $product->category_id }}"
                                     data-name="{{ strtolower($product->name) }}"
                                     data-barcode="{{ strtolower($product->barcode ?? '') }}">
                                    
                                    <td class="p-4 text-center align-top pt-5">
                                        <input type="checkbox" class="product-checkbox rounded border-gray-300 text-teal-600 focus:ring-teal-500 w-5 h-5 cursor-pointer" value="{{ $product->id }}">
                                    </td>
                                    
                                    <td class="p-4 align-top">
                                        <div class="font-bold text-gray-900 mb-1 group-hover:text-teal-700 transition-colors">{{ $product->name }}</div>
                                        <div class="flex flex-wrap gap-2 text-xs">
                                            @if($product->category)
                                                <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-600 border border-gray-200">{{ $product->category->name }}</span>
                                            @endif
                                            <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-500 border border-gray-200 font-mono">{{ $product->barcode ?? 'No Barcode' }}</span>
                                        </div>
                                         <input type="hidden" class="cost-input" value="{{ $formattedCost }}">
                                    </td>

                                    <td class="p-4 text-center align-top">
                                        <div class="inline-flex flex-col items-center justify-center p-2 rounded-lg bg-gray-50 border border-gray-100 min-w-[5rem]">
                                            <span class="text-lg font-bold text-gray-800" data-available-units="{{ $product->available_units }}">
                                                {{ number_format($product->available_units) }}
                                            </span>
                                            <span class="text-[10px] uppercase text-gray-500 font-semibold tracking-wide">Available</span>
                                        </div>
                                        @if($product->available_units < 10 && $product->available_units > 0)
                                            <div class="mt-1 text-[10px] text-orange-600 font-bold uppercase">Low Stock</div>
                                        @elseif($product->available_units == 0)
                                            <div class="mt-1 text-[10px] text-red-600 font-bold uppercase">Out </div>
                                        @endif
                                    </td>

                                    <td class="p-4 align-top">
                                        <label class="text-[10px] text-gray-400 font-medium uppercase tracking-wide mb-1 block">Qty (Boxes)</label>
                                        <input type="number" 
                                               class="boxes-input w-full border-gray-300 rounded-lg text-center font-bold text-gray-900 focus:ring-teal-500 focus:border-teal-500 disabled:bg-gray-100 disabled:text-gray-400" 
                                               min="0"
                                               max="{{ floor($product->available_units / ($product->quantity_per_box ?: 1)) }}"
                                               data-max-boxes="{{ floor($product->available_units / ($product->quantity_per_box ?: 1)) }}"
                                               placeholder="0" disabled>
                                        <div class="text-right mt-1">
                                            <span class="text-xs text-gray-500">Total Units: <span class="total-units-display font-medium text-teal-600">0</span></span>
                                        </div>
                                    </td>

                                    <td class="p-4 align-top">
                                        <label class="text-[10px] text-gray-400 font-medium uppercase tracking-wide mb-1 block">Per Box</label>
                                        <input type="number" 
                                               class="units-per-box-input w-full border-gray-300 rounded-lg text-center text-sm text-gray-600 focus:ring-teal-500 focus:border-teal-500 bg-gray-50 disabled:bg-gray-100" 
                                               min="1"
                                               value="{{ $product->quantity_per_box ?? 1 }}" disabled>
                                    </td>

                                    <td class="p-4 align-top">
                                        <label class="text-[10px] text-gray-400 font-medium uppercase tracking-wide mb-1 block">Sell Price</label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">₵</span>
                                            <input type="number" 
                                                   class="price-input w-full pl-7 pr-3 border-gray-300 rounded-lg text-right font-medium text-gray-900 focus:ring-teal-500 focus:border-teal-500 disabled:bg-gray-100" 
                                                   step="0.01" min="0" placeholder="{{ $formattedPrice }}" disabled>
                                        </div>
                                        <div class="text-right mt-1 text-xs text-gray-400">Cost: {{ $formattedCost }}</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
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
    
    let selectedBranchId = null;

    if(branchSelect && branchSelect.tagName === 'SELECT') {
        branchSelect.addEventListener('change', function() {
            selectedBranchId = this.value;
            if(selectedBranchId) {
                const opt = this.options[this.selectedIndex];
                document.getElementById('managerName').textContent = opt.dataset.manager;
                document.getElementById('branchLocation').textContent = opt.dataset.location;
                branchInfo.classList.remove('hidden');
                enableInputs();
            } else {
                branchInfo.classList.add('hidden');
                disableInputs();
            }
            updateSummary();
        });
    } else if (document.getElementById('branchSelect')) {
         selectedBranchId = document.getElementById('branchSelect').value;
    }

    function enableInputs() {
        document.querySelectorAll('.boxes-input, .units-per-box-input, .price-input').forEach(el => el.disabled = false);
    }
    
    function disableInputs() {
        document.querySelectorAll('.boxes-input, .units-per-box-input, .price-input').forEach(el => el.disabled = true);
    }

    // Calculation Logic
    document.querySelectorAll('.boxes-input, .units-per-box-input').forEach(input => {
        input.addEventListener('input', function() {
            const row = this.closest('tr');
            const boxes = parseFloat(row.querySelector('.boxes-input').value) || 0;
            const perBox = parseFloat(row.querySelector('.units-per-box-input').value) || 1;
            const maxBoxes = parseFloat(row.querySelector('.boxes-input').dataset.maxBoxes) || 0;
            const availableUnits = parseFloat(row.querySelector('[data-available-units]').dataset.availableUnits) || 0;

            if (boxes > maxBoxes) {
                // Determine which input triggered the event to avoid infinite loops if needed, 
                // but simpler to just correct value
                if(this.classList.contains('boxes-input')){
                     this.value = maxBoxes;
                     // re-read boxes
                     // boxes = maxBoxes; (but const is immutable in block scale if not careful, just re-calc below)
                }
                   // alert? Modern UI prefers visual validation or auto-correction
            }

            // Re-calc after potential correction
             const safeBoxes = parseFloat(row.querySelector('.boxes-input').value) || 0;
             const total = safeBoxes * perBox;
             
             if(total > availableUnits) {
                 // Adjust boxes down
                 const adjustedBoxes = Math.floor(availableUnits / perBox);
                 row.querySelector('.boxes-input').value = adjustedBoxes;
                 row.querySelector('.total-units-display').textContent = adjustedBoxes * perBox;
             } else {
                 row.querySelector('.total-units-display').textContent = total;
             }
             
             // Auto-check row if quantities entered
             const checkbox = row.querySelector('.product-checkbox');
             if(safeBoxes > 0 && !checkbox.checked) checkbox.checked = true;
             if(safeBoxes === 0 && checkbox.checked && this.classList.contains('boxes-input')) checkbox.checked = false;

             updateSummary();
        });
    });

    // Checkbox Logic
    document.querySelectorAll('.product-checkbox').forEach(cb => {
        cb.addEventListener('change', updateSummary);
    });

    selectAll.addEventListener('change', function() {
        document.querySelectorAll('.product-checkbox').forEach(cb => {
            if(!cb.closest('tr').hidden) cb.checked = this.checked;
        });
        updateSummary();
    });

    function updateSummary() {
        let count = 0;
        let units = 0;
        let cost = 0;

        document.querySelectorAll('.product-checkbox:checked').forEach(cb => {
            const row = cb.closest('tr');
            const boxes = parseFloat(row.querySelector('.boxes-input').value) || 0;
            const perBox = parseFloat(row.querySelector('.units-per-box-input').value) || 1;
            const cVal = parseFloat(row.querySelector('.cost-input').value) || 0;
            
            const total = boxes * perBox;
            count++;
            units += total;
            cost += total * cVal; // Total cost value
        });

        selectedCountEl.textContent = count;
        totalUnitsEl.textContent = units.toLocaleString();
        totalCostEl.textContent = '₵' + cost.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        
        assignBtn.disabled = count === 0 || !selectedBranchId;
        assignBtn.className = assignBtn.disabled 
            ? "w-full py-4 px-6 bg-gray-300 text-gray-500 rounded-xl font-bold shadow-none cursor-not-allowed flex items-center justify-center gap-2"
            : "w-full py-4 px-6 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white rounded-xl font-bold shadow-md transition-all transform active:scale-95 flex items-center justify-center gap-2";
    }

    clearBtn.addEventListener('click', function() {
        document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = false);
        document.querySelectorAll('.boxes-input').forEach(i => i.value = '');
        document.querySelectorAll('.total-units-display').forEach(s => s.textContent = '0');
        selectAll.checked = false;
        updateSummary();
    });

    // Search Logic
    const filter = () => {
        const term = productSearch.value.toLowerCase();
        const cat = categoryFilter.value;
        const rows = document.querySelectorAll('.product-row');
        
        rows.forEach(row => {
            const txt = row.dataset.name + ' ' + row.dataset.barcode;
            const rCat = row.dataset.categoryId;
            const matchTerm = txt.includes(term);
            const matchCat = !cat || rCat === cat;
            row.style.display = (matchTerm && matchCat) ? '' : 'none';
        });
    };
    productSearch.addEventListener('input', filter);
    categoryFilter.addEventListener('change', filter);

    // Submit Logic
    assignBtn.addEventListener('click', async function() {
        if (!selectedBranchId) return;

        const payload = [];
        document.querySelectorAll('.product-checkbox:checked').forEach(cb => {
            const row = cb.closest('tr');
            const boxes = parseFloat(row.querySelector('.boxes-input').value) || 0;
             if (boxes > 0) {
                payload.push({
                    product_id: parseInt(cb.value),
                    quantity_of_boxes: boxes,
                    quantity_per_box: parseFloat(row.querySelector('.units-per-box-input').value) || 1,
                    selling_price: parseFloat(row.querySelector('.price-input').value) || parseFloat(row.querySelector('.price-input').placeholder) || 0,
                    cost_price: parseFloat(row.querySelector('.cost-input').value) || 0, // Should typically use system cost, not user entry, but keeping logic
                    reorder_level: null
                });
             }
        });

        if(payload.length === 0) {
            alert("No products with quantities selected.");
            return;
        }

        assignBtn.disabled = true;
        assignBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

        try {
            const res = await fetch('{{ route("inventory.bulk-assign") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    branch_id: selectedBranchId,
                    products: payload
                })
            });

            const data = await res.json();
            if(res.ok) {
                // Success
                 // Use a nice modal or redirect
                alert(data.message);
                window.location.href = '{{ route("layouts.productman") }}';
            } else {
                alert('Error: ' + (data.error || 'Assignment failed'));
                assignBtn.disabled = false;
                assignBtn.innerHTML = '<i class="fas fa-check-circle text-xl"></i><span>Confirm Assignment</span>';
            }
        } catch(e) {
            console.error(e);
            alert('Network error occurred.');
            assignBtn.disabled = false;
             assignBtn.innerHTML = '<i class="fas fa-check-circle text-xl"></i><span>Confirm Assignment</span>';
        }
    });
</script>
@endsection
