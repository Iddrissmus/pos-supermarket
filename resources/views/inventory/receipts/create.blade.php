@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-truck mr-3 text-blue-600"></i>Receive Stock
                    </h1>
                    <a href="{{ route('stock-receipts.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-list mr-2"></i>View All Receipts
                    </a>
                </div>
            </div>

            <form action="{{ route('stock-receipts.store') }}" method="POST" class="p-6">
                @csrf
                
                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                        <h3 class="text-red-800 font-medium">Please correct the following errors:</h3>
                        <ul class="mt-2 text-red-700 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Branch Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Branch *</label>
                        <select name="branch_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->display_label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Supplier Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Supplier *
                            @if(auth()->user()->role === 'manager')
                                <span class="text-xs text-gray-500">(Local suppliers only)</span>
                            @endif
                        </label>
                        <select name="supplier_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" 
                                    {{ (old('supplier_id') == $supplier->id || (isset($selectedSupplierId) && $selectedSupplierId == $supplier->id)) ? 'selected' : '' }}>
                                    {{ $supplier->name }}{{ $supplier->is_central ? ' [Central]' : ' [Local]' }}
                                </option>
                            @endforeach
                        </select>
                        @if(auth()->user()->role === 'manager')
                            <p class="text-xs text-amber-600 mt-1">
                                <i class="fas fa-info-circle mr-1"></i>You can only add inventory for local suppliers (e.g., plantain chips sellers)
                            </p>
                        @endif
                    </div>

                    <!-- Receipt Number -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Receipt Number</label>
                        <input type="text" name="receipt_number" value="{{ old('receipt_number') }}" 
                               placeholder="Auto-generated if empty"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Leave empty for auto-generation</p>
                    </div>

                    <!-- Received Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Received Date *</label>
                        <input type="date" name="received_date" value="{{ old('received_date', date('Y-m-d')) }}" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <!-- Notes -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                    <textarea name="notes" rows="3" placeholder="Optional notes about this shipment"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">{{ old('notes') }}</textarea>
                </div>

                <!-- Stock Items -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-800">Stock Items</h3>
                        <button type="button" id="add-item" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">
                            <i class="fas fa-plus mr-2"></i>Add Item
                        </button>
                    </div>

                    <div id="items-container">
                        <!-- Initial item row -->
                        <div class="item-row grid grid-cols-12 gap-3 mb-3 p-3 bg-gray-50 rounded-lg">
                            <div class="col-span-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Product *</label>
                                <select name="items[0][product_id]" required class="product-select w-full border border-gray-300 rounded px-2 py-1 text-sm">
                                    <option value="">Select Product</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" data-name="{{ $product->name }}" data-barcode="{{ $product->barcode }}">
                                            {{ $product->name }} ({{ $product->barcode }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Boxes *</label>
                                <input type="number" name="items[0][quantity_of_boxes]" min="0" required
                                       class="boxes-input w-full border border-gray-300 rounded px-2 py-1 text-sm" placeholder="0">
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Units/Box *</label>
                                <input type="number" name="items[0][quantity_per_box]" min="1" required
                                       class="units-per-box-input w-full border border-gray-300 rounded px-2 py-1 text-sm" placeholder="1">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Total Units</label>
                                <input type="number" name="items[0][quantity]" min="1" required
                                       class="quantity-input w-full border border-gray-300 rounded px-2 py-1 text-sm bg-blue-50" readonly>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Unit Cost *</label>
                                <input type="number" name="items[0][unit_cost]" step="0.01" min="0" required
                                       class="unit-cost-input w-full border border-gray-300 rounded px-2 py-1 text-sm">
                            </div>
                            <div class="col-span-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Total Cost</label>
                                <input type="text" class="total-cost-display w-full border border-gray-200 rounded px-2 py-1 text-sm bg-gray-100" readonly>
                            </div>
                            <div class="col-span-1 flex items-end">
                                <button type="button" class="remove-item bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded text-sm" style="display: none;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-gray-700">Total Receipt Amount:</span>
                            <span id="grand-total" class="text-xl font-bold text-blue-600">₵0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-4 mt-6 pt-6 border-t">
                    <a href="{{ route('stock-receipts.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                        <i class="fas fa-save mr-2"></i>Receive Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemCount = 1;

    // Add new item row
    document.getElementById('add-item').addEventListener('click', function() {
        const container = document.getElementById('items-container');
        const newRow = createItemRow(itemCount);
        container.appendChild(newRow);
        itemCount++;
        updateRemoveButtons();
    });

    // Remove item functionality
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-item')) {
            e.target.closest('.item-row').remove();
            updateRemoveButtons();
            calculateGrandTotal();
        }
    });

    // Calculate totals when inputs change
    document.addEventListener('input', function(e) {
        if (e.target.matches('.quantity-input, .unit-cost-input')) {
            calculateRowTotal(e.target.closest('.item-row'));
            calculateGrandTotal();
        }
        
        // Handle box quantity calculations
        if (e.target.matches('.boxes-input, .units-per-box-input')) {
            const row = e.target.closest('.item-row');
            calculateTotalUnits(row);
            calculateRowTotal(row);
            calculateGrandTotal();
        }
    });
    
    function calculateTotalUnits(row) {
        const boxes = parseFloat(row.querySelector('.boxes-input').value) || 0;
        const unitsPerBox = parseFloat(row.querySelector('.units-per-box-input').value) || 0;
        const totalUnits = boxes * unitsPerBox;
        
        if (totalUnits > 0) {
            row.querySelector('.quantity-input').value = totalUnits;
        }
    }

    function createItemRow(index) {
        const template = document.querySelector('.item-row').cloneNode(true);
        
        // Update name attributes
        template.querySelector('select').setAttribute('name', `items[${index}][product_id]`);
        template.querySelector('.boxes-input').setAttribute('name', `items[${index}][quantity_of_boxes]`);
        template.querySelector('.units-per-box-input').setAttribute('name', `items[${index}][quantity_per_box]`);
        template.querySelector('.quantity-input').setAttribute('name', `items[${index}][quantity]`);
        template.querySelector('.unit-cost-input').setAttribute('name', `items[${index}][unit_cost]`);
        
        // Clear values
        template.querySelector('select').value = '';
        template.querySelector('.boxes-input').value = '';
        template.querySelector('.units-per-box-input').value = '';
        template.querySelector('.quantity-input').value = '';
        template.querySelector('.unit-cost-input').value = '';
        template.querySelector('.total-cost-display').value = '';
        
        return template;
    }

    function updateRemoveButtons() {
        const rows = document.querySelectorAll('.item-row');
        rows.forEach((row, index) => {
            const removeBtn = row.querySelector('.remove-item');
            removeBtn.style.display = rows.length > 1 ? 'block' : 'none';
        });
    }

    function calculateRowTotal(row) {
        const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
        const unitCost = parseFloat(row.querySelector('.unit-cost-input').value) || 0;
        const total = quantity * unitCost;
        row.querySelector('.total-cost-display').value = '₵' + total.toFixed(2);
    }

    function calculateGrandTotal() {
        let grandTotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
            const unitCost = parseFloat(row.querySelector('.unit-cost-input').value) || 0;
            grandTotal += quantity * unitCost;
        });
        document.getElementById('grand-total').textContent = '₵' + grandTotal.toFixed(2);
    }

    // Initial calculations
    updateRemoveButtons();
    calculateGrandTotal();
});
</script>
@endsection