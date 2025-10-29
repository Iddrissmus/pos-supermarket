@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-cash-register mr-3 text-green-600"></i>New Sale
                    </h1>
                    <a href="{{ route('sales.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-list mr-2"></i>View Sales
                    </a>
                </div>
            </div>

            <form action="{{ route('sales.store') }}" method="POST" class="p-6" id="sale-form">
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

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <!-- Customer Selection (Optional) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Customer (Optional)</label>
                        <select name="customer_id" id="customer-select" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">Walk-in Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" 
                                        data-name="{{ $customer->display_name }}"
                                        data-email="{{ $customer->email }}"
                                        data-phone="{{ $customer->phone }}"
                                        data-credit-limit="{{ $customer->credit_limit }}"
                                        data-outstanding="{{ $customer->outstanding_balance }}"
                                        {{ old('customer_id', request('customer_id')) == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->display_name }} (#{{ $customer->customer_number }})
                                </option>
                            @endforeach
                        </select>
                        <div id="customer-info" class="mt-2 text-xs text-gray-500 hidden">
                            <div class="bg-blue-50 border border-blue-200 rounded p-2">
                                <p class="customer-details"></p>
                                <p class="credit-info"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Branch Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Branch *</label>
                        <select name="branch_id" id="branch-select" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->display_label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Payment Method -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method *</label>
                        <select name="payment_method" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">Select Payment Method</option>
                            <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                            <option value="mobile_money" {{ old('payment_method') == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                        </select>
                    </div>
                </div>

                <!-- Sale Items -->
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-800">Sale Items</h3>
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
                                </select>
                                <div class="product-info text-xs text-gray-500 mt-1"></div>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Available</label>
                                <input type="text" class="stock-display w-full border border-gray-200 rounded px-2 py-1 text-sm bg-gray-100" readonly>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity *</label>
                                <input type="number" name="items[0][quantity]" min="1" required
                                       class="quantity-input w-full border border-gray-300 rounded px-2 py-1 text-sm">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Price *</label>
                                <input type="number" name="items[0][price]" step="0.01" min="0" required
                                       class="price-input w-full border border-gray-300 rounded px-2 py-1 text-sm">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Total</label>
                                <input type="text" class="total-display w-full border border-gray-200 rounded px-2 py-1 text-sm bg-gray-100" readonly>
                            </div>
                            <div class="col-span-1 flex items-end">
                                <button type="button" class="remove-item bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded text-sm" style="display: none;">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 p-3 bg-green-50 rounded-lg">
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-gray-700">Total Sale Amount:</span>
                            <span id="grand-total" class="text-xl font-bold text-green-600">0.00</span>
                        </div>
                    </div>

                    <!-- Credit Warning -->
                    <div id="credit-warning" class="mt-4"></div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-4 mt-6 pt-6 border-t">
                    <a href="{{ route('sales.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg">
                        Cancel
                    </a>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg">
                        <i class="fas fa-money-bill-wave mr-2"></i>Complete Sale
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemCount = 1;

    // Product data for the selected branch
    const products = @json($products);
    
    // Add new item row
    document.getElementById('add-item').addEventListener('click', function() {
        const container = document.getElementById('items-container');
        const newRow = createItemRow(itemCount);
        container.appendChild(newRow);
        itemCount++;
        updateRemoveButtons();
        populateProductSelect(newRow.querySelector('.product-select'));
    });

    // Remove item functionality
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-item')) {
            e.target.closest('.item-row').remove();
            updateRemoveButtons();
            calculateGrandTotal();
        }
    });

    // Handle branch change
    document.getElementById('branch-select').addEventListener('change', function() {
        const branchId = this.value;
        document.querySelectorAll('.product-select').forEach(select => {
            populateProductSelect(select, branchId);
        });
    });

    // Handle product selection
    document.addEventListener('change', function(e) {
        if (e.target.matches('.product-select')) {
            const row = e.target.closest('.item-row');
            const productId = e.target.value;
            const branchId = document.getElementById('branch-select').value;
            
            if (productId && branchId) {
                fetchProductStock(branchId, productId, row);
            } else {
                clearProductInfo(row);
            }
        }
    });

    // Calculate totals when inputs change
    document.addEventListener('input', function(e) {
        if (e.target.matches('.quantity-input, .price-input')) {
            calculateRowTotal(e.target.closest('.item-row'));
            calculateGrandTotal();
        }
    });

    function createItemRow(index) {
        const template = document.querySelector('.item-row').cloneNode(true);
        
        // Update name attributes
        template.querySelector('.product-select').setAttribute('name', `items[${index}][product_id]`);
        template.querySelector('.quantity-input').setAttribute('name', `items[${index}][quantity]`);
        template.querySelector('.price-input').setAttribute('name', `items[${index}][price]`);
        
        // Clear values
        template.querySelector('.product-select').value = '';
        template.querySelector('.quantity-input').value = '';
        template.querySelector('.price-input').value = '';
        template.querySelector('.total-display').value = '';
        template.querySelector('.stock-display').value = '';
        template.querySelector('.product-info').textContent = '';
        
        return template;
    }

    function populateProductSelect(selectElement, branchId = null) {
        const currentBranchId = branchId || document.getElementById('branch-select').value;
        selectElement.innerHTML = '<option value="">Select Product</option>';
        
        if (currentBranchId) {
            const branchProducts = products.filter(p => p.branch_id == currentBranchId);
            branchProducts.forEach(product => {
                const option = document.createElement('option');
                option.value = product.id;
                option.textContent = `${product.name} (${product.sku})`;
                option.dataset.stock = product.stock_quantity;
                option.dataset.price = product.selling_price;
                selectElement.appendChild(option);
            });
        }
    }

    function fetchProductStock(branchId, productId, row) {
        fetch(`{{ route('api.product.stock') }}?branch_id=${branchId}&product_id=${productId}`)
            .then(response => response.json())
            .then(data => {
                if (data.available) {
                    row.querySelector('.stock-display').value = data.stock_quantity;
                    row.querySelector('.price-input').value = data.selling_price;
                    row.querySelector('.product-info').textContent = `SKU: ${data.sku} | Cost: ₵${data.cost_price}`;
                    row.querySelector('.quantity-input').max = data.stock_quantity;
                } else {
                    clearProductInfo(row);
                    alert('Product not available at this branch or out of stock');
                }
            })
            .catch(error => {
                console.error('Error fetching product stock:', error);
                clearProductInfo(row);
            });
    }

    function clearProductInfo(row) {
        row.querySelector('.stock-display').value = '';
        row.querySelector('.price-input').value = '';
        row.querySelector('.product-info').textContent = '';
        row.querySelector('.quantity-input').removeAttribute('max');
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
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const total = quantity * price;
        row.querySelector('.total-display').value = '₵' + total.toFixed(2);
    }

    function calculateGrandTotal() {
        let grandTotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            grandTotal += quantity * price;
        });
        document.getElementById('grand-total').textContent = '₵' + grandTotal.toFixed(2);
        
        // Check customer credit limit if customer is selected
        checkCustomerCredit(grandTotal);
    }

    function checkCustomerCredit(orderTotal) {
        const customerSelect = document.getElementById('customer-select');
        const selectedOption = customerSelect.options[customerSelect.selectedIndex];
        
        if (!selectedOption.value) return; // No customer selected
        
        const creditLimit = parseFloat(selectedOption.dataset.creditLimit) || 0;
        const outstanding = parseFloat(selectedOption.dataset.outstanding) || 0;
        const availableCredit = creditLimit - outstanding;
        
        if (creditLimit > 0 && orderTotal > availableCredit) {
            const warningDiv = document.getElementById('credit-warning');
            if (warningDiv) {
                warningDiv.innerHTML = `
                    <div class="bg-red-50 border border-red-200 rounded p-3 text-red-800">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Order total (₵${orderTotal.toFixed(2)}) exceeds available credit (₵${availableCredit.toFixed(2)})
                    </div>
                `;
            }
        } else {
            const warningDiv = document.getElementById('credit-warning');
            if (warningDiv) {
                warningDiv.innerHTML = '';
            }
        }
    }

    // Customer selection handler
    document.getElementById('customer-select').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const customerInfo = document.getElementById('customer-info');
        
        if (selectedOption.value) {
            const name = selectedOption.dataset.name;
            const email = selectedOption.dataset.email;
            const phone = selectedOption.dataset.phone;
            const creditLimit = parseFloat(selectedOption.dataset.creditLimit) || 0;
            const outstanding = parseFloat(selectedOption.dataset.outstanding) || 0;
            
            let details = `${name}`;
            if (email) details += ` • ${email}`;
            if (phone) details += ` • ${phone}`;
            
            let creditInfo = '';
            if (creditLimit > 0) {
                const available = creditLimit - outstanding;
                creditInfo = `Credit: ₵${available.toFixed(2)} available (₵${creditLimit.toFixed(2)} limit)`;
                if (outstanding > 0) {
                    creditInfo += ` • Outstanding: ₵${outstanding.toFixed(2)}`;
                }
            } else {
                creditInfo = 'No credit terms';
            }
            
            customerInfo.querySelector('.customer-details').textContent = details;
            customerInfo.querySelector('.credit-info').textContent = creditInfo;
            customerInfo.classList.remove('hidden');
        } else {
            customerInfo.classList.add('hidden');
        }
        
        // Recalculate to check credit limits
        calculateGrandTotal();
    });

    // Initial setup
    updateRemoveButtons();
    calculateGrandTotal();
    
    // Populate products for initially selected branch
    const initialBranchId = document.getElementById('branch-select').value;
    if (initialBranchId) {
        populateProductSelect(document.querySelector('.product-select'), initialBranchId);
    }
    
    // Trigger customer info display if customer is pre-selected
    document.getElementById('customer-select').dispatchEvent(new Event('change'));
});
</script>
@endsection