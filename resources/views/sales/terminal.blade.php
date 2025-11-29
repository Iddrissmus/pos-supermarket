@extends('layouts.app')

@section('title', 'Sales Terminal')

@section('content')
@php
    $branchOptions = $branches->map(fn($branch) => [
        'id' => $branch->id,
        'name' => $branch->display_label,
    ])->values();

    $paymentMethods = [
        ['value' => 'cash', 'label' => 'Cash'],
        ['value' => 'card', 'label' => 'Card'],
        ['value' => 'mobile_money', 'label' => 'Mobile Money'],
    ];

    $defaultBranchId = optional(auth()->user())->branch_id ?? ($branches->first()->id ?? null);
@endphp

<!-- Cash Drawer Opening Modal -->
@if(auth()->user()->role === 'cashier' && !$hasActiveSession)
<div id="cash-drawer-modal" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 rounded-t-xl">
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <i class="fas fa-cash-register"></i>
                Open Cash Drawer
            </h2>
            <p class="text-blue-100 text-sm mt-1">Start your shift by recording your opening cash amount</p>
        </div>
        
        <form id="open-drawer-form" class="p-6 space-y-5">
            @csrf
            
            <!-- Date Display -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-calendar-day text-gray-400"></i> Date
                </label>
                <input 
                    type="text" 
                    value="{{ \Carbon\Carbon::today()->format('l, F j, Y') }}" 
                    readonly 
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 bg-gray-50 text-gray-700 font-medium"
                >
            </div>

            <!-- Opening Amount -->
            <div>
                <label for="opening_amount" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-money-bill-wave text-green-600"></i> Opening Amount (Cash in Drawer) <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-3 flex items-center text-gray-500 font-semibold">GH₵</span>
                    <input 
                        type="number" 
                        id="opening_amount" 
                        name="opening_amount" 
                        step="0.01" 
                        min="0" 
                        required 
                        class="w-full border border-gray-300 rounded-lg pl-12 pr-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="0.00"
                        autofocus
                    >
                </div>
                <p class="text-xs text-gray-500 mt-1">Count all cash in your drawer before starting</p>
            </div>

            <!-- Opening Notes -->
            <div>
                <label for="opening_notes" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fas fa-sticky-note text-yellow-600"></i> Notes (Optional)
                </label>
                <textarea 
                    id="opening_notes" 
                    name="opening_notes" 
                    rows="3" 
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                    placeholder="E.g., Change denominations, special instructions..."
                ></textarea>
            </div>

            <!-- Error Display -->
            <div id="drawer-error" class="hidden bg-red-50 border border-red-200 rounded-lg p-3 text-sm text-red-700">
                <i class="fas fa-exclamation-circle"></i> <span id="drawer-error-text"></span>
            </div>

            <!-- Submit Button -->
            <button 
                type="submit" 
                id="open-drawer-btn"
                class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold py-3 px-4 rounded-lg shadow-md transition duration-200 flex items-center justify-center gap-2"
            >
                <i class="fas fa-lock-open"></i>
                <span>Open Drawer & Start Shift</span>
            </button>
        </form>
    </div>
</div>
@endif

<div class="min-h-screen bg-gray-100 py-6">
    <div class="max-w-7xl mx-auto space-y-6">
        <header class="bg-white rounded-xl shadow px-6 py-4 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">POS Terminal</h1>
                <p class="text-sm text-gray-500">Process in-store transactions quickly and accurately</p>
            </div>

            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-500 uppercase tracking-wide">Branch</span>
                    <select id="pos-branch" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ $defaultBranchId === $branch->id ? 'selected' : '' }}>
                                {{ $branch->display_label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="hidden md:flex items-center gap-3 bg-gray-50 px-4 py-2 rounded-lg border border-gray-200">
                    <div class="w-2 h-2 rounded-full bg-green-500"></div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Cashier</p>
                        <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name ?? 'Cashier' }}</p>
                    </div>
                </div>

                {{-- <button id="hold-order-button" type="button" class="inline-flex items-center gap-2 bg-gray-200 text-gray-500 px-4 py-2 rounded-lg cursor-not-allowed" title="Hold order is coming soon" disabled>
                    <i class="fas fa-pause-circle"></i>
                    <span>Hold Order</span>
                </button> --}}
                {{-- <button id="new-order-button" type="button" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-file-circle-plus"></i>
                    <span>New Order</span>
                </button> --}}
            </div>
        </header>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <section class="bg-white rounded-xl shadow p-6 xl:col-span-2 space-y-4">
                {{-- <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Product Catalog</h2>
                        <p id="catalog-branch-label" class="text-sm text-gray-500">Select a branch to view available stock.</p>
                    </div>
                    
                    <div class="relative w-full lg:w-80">
                        <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                            <i class="fas fa-search"></i>
                        </span>
                        <input
                            id="product-search"
                            type="search"
                            placeholder="Search by name or SKU"
                            class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                        >
                    </div>
                </div> --}}
                <div class="p-6 border-b border-gray-200 bg-gray-50">
                    <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Product Catalog</h2>
                            <p id="catalog-branch-label" class="text-sm text-gray-500">Select a branch to view available stock. <span id="catalog-count" class="ml-2 text-sm text-gray-600"></span></p>
                        </div>
                        <div class="relative w-full lg:w-80">
                            <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                                <i class="fas fa-search"></i>
                            </span>
                            <input
                                id="product-search"
                                type="search"
                                placeholder="Search by name or barcode"
                                class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                            >
                        </div>
                    </div>
                    <!-- Category filter buttons -->
                    <div class="mt-4">
                        <form method="GET" action="{{ route('sales.terminal') }}" id="terminalCategoryForm">
                            <div class="flex flex-wrap gap-2">
                                <button type="button" 
                                        onclick="filterTerminalByCategory('')" 
                                        class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors {{ !$selectedCategory ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                                    <i class="fas fa-th mr-2"></i>
                                    All Categories
                                </button>
                                @foreach($categories as $cat)
                                    <button type="button" 
                                            onclick="filterTerminalByCategory('{{ $cat->id }}')" 
                                            class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors {{ $selectedCategory == $cat->id ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                                        <i class="fas {{ $cat->icon ?? 'fa-tag' }} mr-2"></i>
                                        {{ $cat->name }}
                                        <span class="ml-2 text-xs {{ $selectedCategory == $cat->id ? 'bg-white/20' : 'bg-gray-300' }} px-2 py-0.5 rounded-full">
                                            {{ $cat->products_count }}
                                        </span>
                                    </button>
                                @endforeach
                                @if($uncategorizedCount > 0)
                                    <button type="button" 
                                            onclick="filterTerminalByCategory('null')" 
                                            class="flex items-center px-3 py-2 text-sm rounded-lg transition-colors {{ $selectedCategory === 'null' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                                        <i class="fas fa-question-circle mr-2"></i>
                                        Uncategorized
                                        <span class="ml-2 text-xs {{ $selectedCategory === 'null' ? 'bg-white/20' : 'bg-gray-300' }} px-2 py-0.5 rounded-full">
                                            {{ $uncategorizedCount }}
                                        </span>
                                    </button>
                                @endif
                            </div>
                            <input type="hidden" name="category_id" id="terminal_category_id" value="{{ $selectedCategory }}">
                        </form>
                    </div>
                </div>

                <div id="product-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 min-h-[200px]" data-drawer-active="{{ auth()->user()->role === 'cashier' ? ($hasActiveSession ? 'true' : 'false') : 'true' }}"></div>
                
                @if(auth()->user()->role === 'cashier' && !$hasActiveSession)
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                    <i class="fas fa-exclamation-triangle text-yellow-600 text-2xl mb-2"></i>
                    <p class="text-sm text-yellow-800 font-medium">Cash drawer must be opened before processing sales</p>
                </div>
                @endif
            </section>

            <section class="bg-white rounded-xl shadow p-6 space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Current Sale</h2>
                        <p id="cart-summary" class="text-sm text-gray-500">No items yet</p>
                    </div>
                    <button id="clear-cart" type="button" class="text-sm text-red-600 hover:text-red-700">
                        Clear
                    </button>
                </div>

                <div id="cart-empty" class="text-sm text-gray-500 text-center py-6 border border-dashed border-gray-200 rounded-lg">
                    Your cart is empty. Add items from the catalog to get started.
                </div>

                <div id="cart-table-container" class="hidden overflow-hidden border border-gray-200 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-left">Item</th>
                                <th class="px-4 py-3 text-right">Qty</th>
                                <th class="px-4 py-3 text-right">Price</th>
                                <th class="px-4 py-3 text-right">Total</th>
                                <th class="px-4 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="cart-items" class="bg-white divide-y divide-gray-200 text-sm"></tbody>
                    </table>
                </div>

                <div class="space-y-4">
                    <div>
                        <p class="text-sm font-medium text-gray-700 mb-2">Payment Method</p>
                        <div class="flex flex-wrap gap-2" id="payment-methods">
                            @foreach($paymentMethods as $method)
                                <label class="flex items-center gap-2 px-3 py-2 border border-gray-200 rounded-lg cursor-pointer text-sm text-gray-700">
                                    <input type="radio" name="payment_method" value="{{ $method['value'] }}" class="text-blue-600 focus:ring-blue-500" {{ $loop->first ? 'checked' : '' }}>
                                    <span>{{ $method['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-4 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal</span>
                            <span id="cart-subtotal" class="font-medium text-gray-900">₵0.00</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tax</span>
                            <span id="cart-tax" class="text-gray-500">₵0.00</span>
                        </div>
                        <div class="flex justify-between text-lg font-semibold text-gray-900">
                            <span>Total</span>
                            <span id="cart-total">₵0.00</span>
                        </div>
                    </div>

                    <div id="checkout-error" class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-2 rounded-lg text-sm"></div>
                    <div class="mt-4">
                        <label for="amount-tendered" class="block text-sm font-medium text-gray-700">Amount Tendered</label>
                        <input type="number" id="amount-tendered" min="0" step="0.01" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Enter amount tendered">
                        <div id="change-display" class="mt-2 text-green-600 font-bold text-lg"></div>
                    </div>

                    <button id="checkout-button" type="button" class="w-full inline-flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white rounded-lg py-3 font-semibold transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
                        <i class="fas fa-cash-register"></i>
                        <span>Complete Sale</span>
                    </button>
                </div>
            </section>
        </div>
    </div>
</div>

<div id="receipt-modal" class="hidden fixed inset-0 z-50">
    <div class="flex items-center justify-center min-h-screen px-4">
    <div class="fixed inset-0 bg-gray-900/60" data-close-modal></div>
    <div data-modal-panel class="relative z-10 bg-white rounded-xl shadow-xl max-w-lg w-full p-6 space-y-4">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-xl font-semibold text-gray-900">Sale completed</h3>
                    <p class="text-sm text-gray-500">Receipt summary below. You can open the detailed receipt for printing.</p>
                </div>
                <button type="button" class="text-gray-400 hover:text-gray-600" data-close-modal>
                    <span class="sr-only">Close receipt modal</span>
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div id="receipt-summary" class="space-y-3 text-sm text-gray-700"></div>

            <div class="flex flex-col sm:flex-row sm:justify-end gap-3 pt-2">
                <a id="print-receipt-link" href="#" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 border border-blue-600 text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-50">
                    <i class="fas fa-receipt"></i>
                    <span>View & Print Receipt</span>
                </a>
                <button type="button" class="inline-flex items-center justify-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg" data-close-modal id="done-receipt-btn">
                    <i class="fas fa-check"></i>
                    <span>Done</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const amountTenderedInput = document.getElementById('amount-tendered');
    const changeDisplay = document.getElementById('change-display');
    const checkoutButton = document.getElementById('checkout-button');
    const cartTotalEl = document.getElementById('cart-total');

    function getCartTotal() {
        // Remove currency symbol and parse
        return parseFloat(cartTotalEl.textContent.replace(/[^\d.]/g, '')) || 0;
    }

    function updateChange() {
        const tendered = parseFloat(amountTenderedInput.value) || 0;
        const total = getCartTotal();
        const change = tendered - total;
        changeDisplay.textContent = change >= 0 ? `Change: GH₵${change.toFixed(2)}` : 'Insufficient amount';
        checkoutButton.disabled = tendered < total || total === 0;
    }

    if (amountTenderedInput) {
        amountTenderedInput.addEventListener('input', updateChange);
    }
    // Update change on cart total change (if cart updates)
    if (cartTotalEl) {
        const observer = new MutationObserver(updateChange);
        observer.observe(cartTotalEl, { childList: true });
    }
    // Refresh page after sale completion and clear receipt modal
    const doneReceiptBtn = document.getElementById('done-receipt-btn');
    if (doneReceiptBtn) {
        doneReceiptBtn.addEventListener('click', function() {
            window.location.reload();
        });
    }

    // Clear receipt modal content on close
    const receiptModal = document.getElementById('receipt-modal');
    const receiptSummary = document.getElementById('receipt-summary');
    document.querySelectorAll('[data-close-modal]').forEach(btn => {
        btn.addEventListener('click', function() {
            if (receiptModal) receiptModal.classList.add('hidden');
            if (receiptSummary) receiptSummary.innerHTML = '';
        });
    });

    // Cash Drawer Opening Modal Handler
    const openDrawerForm = document.getElementById('open-drawer-form');
    if (openDrawerForm) {
        openDrawerForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('open-drawer-btn');
            const errorDiv = document.getElementById('drawer-error');
            const errorText = document.getElementById('drawer-error-text');
            const openingAmount = document.getElementById('opening_amount').value;
            const openingNotes = document.getElementById('opening_notes').value;
            
            // Disable button and show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Opening...</span>';
            errorDiv.classList.add('hidden');
            
            try {
                const response = await fetch('{{ route("cash-drawer.open") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        opening_amount: openingAmount,
                        opening_notes: openingNotes
                    })
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    // Hide modal and reload page to refresh session status
                    document.getElementById('cash-drawer-modal').remove();
                    window.location.reload();
                } else {
                    // Show error
                    errorText.textContent = data.error || 'Failed to open cash drawer. Please try again.';
                    errorDiv.classList.remove('hidden');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-lock-open"></i> <span>Open Drawer & Start Shift</span>';
                }
            } catch (error) {
                console.error('Error opening cash drawer:', error);
                errorText.textContent = 'Network error. Please check your connection and try again.';
                errorDiv.classList.remove('hidden');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-lock-open"></i> <span>Open Drawer & Start Shift</span>';
            }
        });
    }
});
    
// Category filter function for POS terminal (now updates state directly)
window.filterTerminalByCategory = function(categoryId) {
    // Find the state object from the POS terminal scope
    if (typeof renderProducts !== 'undefined') {
        // Update hidden input
        document.getElementById('terminal_category_id').value = categoryId;
        // Update state
        window.posTerminalState.selectedCategory = categoryId;
        // Re-render products
        renderProducts();
    } else {
        // Fallback: submit form if renderProducts not available
        document.getElementById('terminalCategoryForm').submit();
    }
};

    window.POS_TERMINAL = {
        branches: @json($branchOptions),
        catalog: @json($catalog),
        paymentMethods: @json($paymentMethods),
        storeUrl: "{{ route('sales.store') }}",
        taxCalculateUrl: "{{ route('api.calculate.taxes') }}",
        csrfToken: "{{ csrf_token() }}",
        cashier: {
            name: "{{ auth()->user()->name ?? 'Cashier' }}",
        },
        defaultBranchId: @json($defaultBranchId),
    };
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const posData = window.POS_TERMINAL;
    // debug: ensure catalog exists and is an array
    console.debug('POS_TERMINAL', posData);
    if (!posData) {
        console.warn('POS_TERMINAL not found on window');
    }
    posData.catalog = Array.isArray(posData.catalog) ? posData.catalog : (posData.catalog ? Object.values(posData.catalog) : []);
    console.debug('POS catalog length', posData.catalog.length);

    const branchSelect = document.getElementById('pos-branch');
    const searchInput = document.getElementById('product-search');
    const productGrid = document.getElementById('product-grid');
    const catalogLabel = document.getElementById('catalog-branch-label');
    const cartBody = document.getElementById('cart-items');
    const cartEmpty = document.getElementById('cart-empty');
    const cartTableContainer = document.getElementById('cart-table-container');
    const cartSummary = document.getElementById('cart-summary');
    const cartSubtotal = document.getElementById('cart-subtotal');
    const cartTotal = document.getElementById('cart-total');
    const checkoutButton = document.getElementById('checkout-button');
    const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
    const errorBanner = document.getElementById('checkout-error');
    const clearCartButton = document.getElementById('clear-cart');
    const newOrderButton = document.getElementById('new-order-button');
    const receiptModal = document.getElementById('receipt-modal');
    const receiptSummary = document.getElementById('receipt-summary');
    const printReceiptLink = document.getElementById('print-receipt-link');

    const categoryInput = document.getElementById('terminal_category_id');
    const state = {
        branchId: Number.isFinite(parseInt(posData.defaultBranchId, 10)) ? parseInt(posData.defaultBranchId, 10) : (posData.branches[0]?.id ?? null),
        cart: [],
        search: '',
        paymentMethod: paymentRadios.length ? paymentRadios[0].value : 'cash',
        currentTaxData: null,
        selectedCategory: categoryInput ? categoryInput.value : '',
    };
    
    // Expose state to global scope for category button handlers
    window.posTerminalState = state;

    const formatMoney = (value) => `₵${(Number(value ?? 0)).toFixed(2)}`;

    const escapeHtml = (value = '') => String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    const notify = (message, type = 'success') => {
        if (window.ProductManagement && typeof window.ProductManagement.showNotification === 'function') {
            window.ProductManagement.showNotification(message, type);
        } else {
            console.log(`[${type}]`, message);
        }
    };

    const getProduct = (productId) => posData.catalog.find(product => Number(product.branch_id) === Number(state.branchId) && Number(product.id) === Number(productId));

    const renderProducts = () => {
        const branch = posData.branches.find(b => Number(b.id) === Number(state.branchId));
        if (catalogLabel) {
            catalogLabel.textContent = branch
                ? `Showing products for ${branch.name}`
                : 'Select a branch to view available stock.';
        }

        productGrid.innerHTML = '';

        try {
            if (!state.branchId) {
                productGrid.innerHTML = '<div class="col-span-full text-center text-sm text-gray-500 py-10">Please select a branch to view products.</div>';
                return;
            }

            const searchTerm = state.search.trim().toLowerCase();
            const filtered = posData.catalog.filter(product => {
            // Branch must match
            if (Number(product.branch_id) !== Number(state.branchId)) {
                return false;
            }

            // Category filter (if selected)
            if (state.selectedCategory && String(product.category_id) !== String(state.selectedCategory)) {
                return false;
            }

            if (!searchTerm) {
                return true;
            }

            return (
                (product.name || '').toLowerCase().includes(searchTerm) ||
                (product.barcode || '').toLowerCase().includes(searchTerm)
            );
        });

        if (!filtered.length) {
            productGrid.innerHTML = '<div class="col-span-full text-center text-sm text-gray-500 py-10">No products match your search.</div>';
            return;
        }

        filtered.forEach(product => {
            const card = document.createElement('button');
            card.type = 'button';
            const inStock = Number(product.stock_quantity) > 0;
            card.className = `rounded-lg border transition-all px-4 py-3 text-left ${inStock ? 'border-gray-200 hover:border-blue-500 hover:shadow cursor-pointer' : 'border-gray-200 bg-gray-50 cursor-not-allowed opacity-70'}`;
            card.dataset.productId = product.id;
            card.dataset.price = product.price ?? product.selling_price ?? 0;
            card.dataset.stock = product.stock_quantity ?? 0;
            card.disabled = !inStock;
            card.innerHTML = `
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">${escapeHtml(product.name)}</p>
                        <p class="text-xs text-gray-500">Barcode: ${escapeHtml(product.barcode ?? 'N/A')}</p>
                    </div>
                    <span class="text-sm font-semibold text-gray-900">${formatMoney(product.price ?? product.selling_price ?? 0)}</span>
                </div>
                <div class="mt-3 flex items-center justify-between text-xs">
                    <span class="text-gray-500">Stock: ${product.stock_quantity ?? 0}</span>
                    ${inStock ? '<span class="inline-flex items-center gap-1 text-blue-600"><i class="fas fa-plus"></i>Add</span>' : '<span class="text-red-500 font-medium">Out of stock</span>'}
                </div>
            `;

            productGrid.appendChild(card);
        });
        } catch (err) {
            console.error('Failed to render products', err);
            productGrid.innerHTML = '<div class="col-span-full text-center text-sm text-red-500 py-10">Failed to render products. Check console for errors.</div>';
        } finally {
            // update catalog count indicator
            const catalogCountEl = document.getElementById('catalog-count');
            if (catalogCountEl) {
                const branchProducts = posData.catalog.filter(p => Number(p.branch_id) === Number(state.branchId));
                catalogCountEl.textContent = `(${branchProducts.length} products in this branch)`;
            }
        }
    };
    
    // Expose renderProducts globally for category button handlers
    window.renderProducts = renderProducts;

    const updateCategoryFilter = () => {
        if (!categorySelect) return;

        // Get unique categories for products in the selected branch
        const branchProducts = posData.catalog.filter(p => Number(p.branch_id) === Number(state.branchId));
        const categoriesInBranch = {};
        
        branchProducts.forEach(product => {
            if (product.category_id && product.category_name) {
                if (!categoriesInBranch[product.category_id]) {
                    categoriesInBranch[product.category_id] = {
                        id: product.category_id,
                        name: product.category_name,
                        count: 0
                    };
                }
                categoriesInBranch[product.category_id].count++;
            }
        });

        // Rebuild category select options
        const currentValue = state.selectedCategory;
        categorySelect.innerHTML = '<option value="">All Categories</option>';
        
        Object.values(categoriesInBranch).sort((a, b) => a.name.localeCompare(b.name)).forEach(cat => {
            const option = document.createElement('option');
            option.value = cat.id;
            option.textContent = `${cat.name} (${cat.count})`;
            if (String(cat.id) === String(currentValue)) {
                option.selected = true;
            }
            categorySelect.appendChild(option);
        });

        // Reset category filter if current category not in this branch
        if (currentValue && !categoriesInBranch[currentValue]) {
            state.selectedCategory = '';
            categorySelect.value = '';
        }
    };

    const updateCartUI = async () => {
        const itemCount = state.cart.reduce((sum, item) => sum + item.quantity, 0);
        const subtotal = state.cart.reduce((sum, item) => sum + (item.quantity * item.price), 0);

        if (cartSummary) {
            cartSummary.textContent = itemCount ? `${itemCount} item${itemCount === 1 ? '' : 's'} in cart` : 'No items yet';
        }

        if (cartSubtotal) {
            cartSubtotal.textContent = formatMoney(subtotal);
        }

        // Calculate tax from server if there are items
        let taxAmount = 0;
        let total = subtotal;
        
        if (subtotal > 0) {
            try {
                const response = await fetch(posData.taxCalculateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': posData.csrfToken,
                    },
                    body: JSON.stringify({ subtotal }),
                });

                if (response.ok) {
                    const taxData = await response.json();
                    taxAmount = taxData.tax_amount || 0;
                    total = taxData.total || subtotal;
                    
                    // Store tax data for checkout
                    state.currentTaxData = taxData;
                }
            } catch (error) {
                console.warn('Failed to calculate tax, using subtotal only:', error);
                // Fallback to subtotal only if tax calculation fails
                state.currentTaxData = null;
            }
        } else {
            state.currentTaxData = null;
        }

        // Update tax display
        const cartTax = document.getElementById('cart-tax');
        if (cartTax) {
            cartTax.textContent = formatMoney(taxAmount);
        }

        if (cartTotal) {
            cartTotal.textContent = formatMoney(total);
        }

        if (checkoutButton) {
            checkoutButton.disabled = !itemCount || !state.branchId;
        }

        if (cartEmpty && cartTableContainer) {
            if (!state.cart.length) {
                cartEmpty.classList.remove('hidden');
                cartTableContainer.classList.add('hidden');
            } else {
                cartEmpty.classList.add('hidden');
                cartTableContainer.classList.remove('hidden');
            }
        }
    };

    const renderCart = async () => {
        cartBody.innerHTML = '';

        state.cart.forEach(item => {
            const disableIncrement = item.quantity >= item.stock_quantity;
            const incrementButtonClass = disableIncrement
                ? 'h-6 w-6 flex items-center justify-center rounded border border-gray-300 text-gray-300 bg-gray-50 cursor-not-allowed'
                : 'h-6 w-6 flex items-center justify-center rounded border border-gray-300 text-gray-600 hover:bg-gray-100';
            const decrementButtonClass = 'h-6 w-6 flex items-center justify-center rounded border border-gray-300 text-gray-600 hover:bg-gray-100';
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="px-4 py-3">
                    <p class="font-medium text-gray-900">${escapeHtml(item.name)}</p>
                    <p class="text-xs text-gray-500">Barcode: ${escapeHtml(item.barcode)}</p>
                </td>
                <td class="px-4 py-3">
                    <div class="flex items-center justify-end gap-2">
                        <button type="button" class="${decrementButtonClass}" data-action="decrement" data-product-id="${item.product_id}">-</button>
                        <span class="w-8 text-center">${item.quantity}</span>
                        <button type="button" class="${incrementButtonClass}" data-action="increment" data-product-id="${item.product_id}" ${disableIncrement ? 'disabled' : ''}>+</button>
                    </div>
                    <p class="text-xs text-gray-400 text-right">Max ${item.stock_quantity}</p>
                </td>
                <td class="px-4 py-3 text-right">${formatMoney(item.price)}</td>
                <td class="px-4 py-3 text-right font-medium text-gray-900">${formatMoney(item.quantity * item.price)}</td>
                <td class="px-4 py-3 text-center">
                    <button type="button" class="text-red-600 hover:text-red-700" data-action="remove" data-product-id="${item.product_id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `;
            cartBody.appendChild(row);
        });

        await updateCartUI();
    };

    const addToCart = async (productId) => {
        // Check if cash drawer is active (for cashiers only)
        const productGrid = document.getElementById('product-grid');
        const drawerActive = productGrid?.dataset.drawerActive;
        
        if (drawerActive === 'false') {
            notify('Please open your cash drawer before adding items to cart.', 'error');
            return;
        }
        
        const product = getProduct(productId);
        if (!product) {
            notify('Product not available for this branch.', 'error');
            return;
        }

        const stock = Number(product.stock_quantity ?? 0);
        if (stock <= 0) {
            notify('This product is out of stock.', 'error');
            return;
        }

        const price = Number(product.price ?? product.selling_price ?? 0);
        const existing = state.cart.find(item => Number(item.product_id) === Number(productId));

        if (existing) {
            if (existing.quantity >= stock) {
                notify('No more stock available for this item.', 'error');
                return;
            }
            existing.quantity += 1;
            existing.stock_quantity = stock;
        } else {
            state.cart.push({
                product_id: Number(product.id),
                name: product.name ?? 'Product',
                barcode: product.barcode ?? 'N/A',
                quantity: 1,
                price,
                stock_quantity: stock,
            });
        }

        await renderCart();
        notify('Item added to cart.');
    };

    const updateQuantity = async (productId, delta) => {
        const item = state.cart.find(entry => Number(entry.product_id) === Number(productId));
        if (!item) {
            return;
        }

        const product = getProduct(productId);
        const stock = Number(product?.stock_quantity ?? item.stock_quantity ?? 0);
        const newQuantity = item.quantity + delta;

        if (newQuantity <= 0) {
            await removeFromCart(productId);
            return;
        }

        if (newQuantity > stock) {
            notify('Requested quantity exceeds available stock.', 'error');
            return;
        }

        item.quantity = newQuantity;
        item.stock_quantity = stock;
        await renderCart();
    };

    const removeFromCart = async (productId) => {
        state.cart = state.cart.filter(item => Number(item.product_id) !== Number(productId));
        await renderCart();
    };

    const clearCart = async () => {
        state.cart = [];
        await renderCart();
    };

    const showError = (message) => {
        if (!errorBanner) {
            return;
        }
        errorBanner.textContent = message;
        errorBanner.classList.remove('hidden');
    };

    const clearError = () => {
        if (!errorBanner) {
            return;
        }
        errorBanner.textContent = '';
        errorBanner.classList.add('hidden');
    };

    const toggleCheckoutLoading = (isLoading) => {
        if (!checkoutButton) {
            return;
        }
        checkoutButton.disabled = isLoading || !state.cart.length;
        checkoutButton.classList.toggle('opacity-70', isLoading);
        checkoutButton.querySelector('span').textContent = isLoading ? 'Processing...' : 'Complete Sale';
    };

    const decrementCatalogStock = (items) => {
        items.forEach(item => {
            const product = getProduct(item.product_id);
            if (product) {
                product.stock_quantity = Math.max(0, Number(product.stock_quantity ?? 0) - item.quantity);
            }
        });
    };

    const closeModal = () => {
        if (receiptModal) {
            receiptModal.classList.add('hidden');
        }
    };

    const showReceipt = (data, itemsSnapshot) => {
        if (!receiptModal || !receiptSummary || !printReceiptLink) {
            return;
        }

        const sale = data.sale || {};
        const timestamp = sale.created_at ? new Date(sale.created_at) : new Date();
        const branchName = sale.branch || (posData.branches.find(b => Number(b.id) === Number(state.branchId))?.name ?? 'Branch');
        const cashierName = sale.cashier || posData.cashier.name;

        const itemsRows = itemsSnapshot.map(item => `
            <tr class="border-b border-dashed border-gray-200 last:border-none">
                <td class="py-2 pr-4">
                    <p class="font-medium text-gray-900">${escapeHtml(item.name)}</p>
                    <p class="text-xs text-gray-500">Barcode: ${escapeHtml(item.barcode)}</p>
                </td>
                <td class="py-2 text-right text-xs text-gray-500">x${item.quantity}</td>
                <td class="py-2 text-right text-sm font-medium text-gray-900">${formatMoney(item.quantity * item.price)}</td>
            </tr>
        `).join('');

        const subtotal = itemsSnapshot.reduce((sum, item) => sum + (item.quantity * item.price), 0);
        
        // Use actual tax data from sale response or current state
        const taxData = sale.tax_amount !== undefined ? {
            tax_amount: sale.tax_amount,
            tax_rate: sale.tax_rate || 0,
            total: sale.total
        } : state.currentTaxData;
        
        const taxAmount = taxData?.tax_amount || 0;
        const taxRate = taxData?.tax_rate || 0;
        const calculatedTotal = taxData?.total || subtotal;

        receiptSummary.innerHTML = `
            <div class="space-y-1">
                <p class="text-sm text-gray-500">Sale #${escapeHtml(sale.id ?? '—')}</p>
                <p class="text-sm text-gray-500">${timestamp.toLocaleString()}</p>
            </div>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <p class="text-xs text-gray-400 uppercase">Branch</p>
                    <p class="font-medium text-gray-900">${escapeHtml(branchName)}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase">Cashier</p>
                    <p class="font-medium text-gray-900">${escapeHtml(cashierName)}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 uppercase">Payment</p>
                    <p class="font-medium text-gray-900 capitalize">${escapeHtml(sale.payment_method ?? state.paymentMethod)}</p>
                </div>
            </div>
            <div class="border border-gray-200 rounded-lg divide-y divide-gray-100">
                <table class="w-full text-sm">
                    <tbody>${itemsRows}</tbody>
                </table>
            </div>
            <div class="pt-2 space-y-1 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Subtotal</span>
                    <span class="font-medium text-gray-900">${formatMoney(subtotal)}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Tax${taxRate > 0 ? ' (' + taxRate + '%)' : ''}</span>
                    <span class="text-gray-500">${formatMoney(taxAmount)}</span>
                </div>
                <div class="flex justify-between text-lg font-semibold text-gray-900">
                    <span>Total</span>
                    <span>${formatMoney(calculatedTotal)}</span>
                </div>
                <div class="flex justify-between">
                    <span>Amount Tendered</span>
                    <span>${formatMoney(sale.amount_tendered ?? 0)}</span>
                </div>
                <div class="flex justify-between">
                    <span>Change</span>
                    <span>${formatMoney(sale.change ?? 0)}</span>
                </div>
            </div>
        `;

        // Set receipt URL and handle button state
        if (data.receipt_url) {
            printReceiptLink.href = data.receipt_url;
            printReceiptLink.classList.remove('opacity-50', 'cursor-not-allowed');
            printReceiptLink.removeAttribute('onclick');
        } else {
            printReceiptLink.href = '#';
            printReceiptLink.classList.add('opacity-50', 'cursor-not-allowed');
            printReceiptLink.setAttribute('onclick', 'event.preventDefault(); alert("Receipt URL not available");');
        }
        
        receiptModal.classList.remove('hidden');
    };

    const checkout = async () => {
        if (!state.branchId) {
            notify('Select a branch before completing the sale.', 'error');
            return;
        }

        if (!state.cart.length) {
            notify('Add items to the cart before completing the sale.', 'error');
            return;
        }

        clearError();
        toggleCheckoutLoading(true);

        const amountTenderedInput = document.getElementById('amount-tendered');
        const amountTendered = amountTenderedInput ? parseFloat(amountTenderedInput.value) || 0 : 0;
        const payload = {
            branch_id: state.branchId,
            payment_method: state.paymentMethod,
            items: state.cart.map(item => ({
                product_id: item.product_id,
                quantity: item.quantity,
                price: item.price,
            })),
            amount_tendered: amountTendered,
        };

        try {
            const response = await fetch(posData.storeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': posData.csrfToken,
                },
                body: JSON.stringify(payload),
            });

            const data = await response.json();

            if (!response.ok) {
                const message = data?.message || 'Unable to complete sale. Please try again.';
                showError(message);
                notify(message, 'error');
                return;
            }

            const snapshot = state.cart.map(item => ({ ...item }));
            decrementCatalogStock(snapshot);
            clearCart();
            renderProducts();
            showReceipt(data, snapshot);
            notify('Sale completed successfully!');
        } catch (error) {
            console.error(error);
            showError('A network error occurred. Please try again.');
            notify('A network error occurred. Please try again.', 'error');
        } finally {
            toggleCheckoutLoading(false);
        }
    };

    if (branchSelect) {
        branchSelect.addEventListener('change', async (event) => {
            const selected = parseInt(event.target.value, 10);
            state.branchId = Number.isFinite(selected) ? selected : null;
            await clearCart();
            renderProducts();
            updateCategoryFilter(); // Update categories based on selected branch
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', (event) => {
            state.search = event.target.value;
            renderProducts();
        });
    }

    // Note: Category filtering is now handled by button clicks via filterTerminalByCategory()
    // Old dropdown handler removed since we now use category buttons

    if (productGrid) {
        productGrid.addEventListener('click', async (event) => {
            const button = event.target.closest('button[data-product-id]');
            if (!button || button.disabled) {
                return;
            }
            const productId = parseInt(button.dataset.productId, 10);
            if (!Number.isFinite(productId)) {
                return;
            }
            await addToCart(productId);
        });
    }

    if (cartBody) {
        cartBody.addEventListener('click', async (event) => {
            const control = event.target.closest('[data-action]');
            if (!control) {
                return;
            }
            const productId = parseInt(control.dataset.productId, 10);
            if (!Number.isFinite(productId)) {
                return;
            }
            const action = control.dataset.action;
            if (action === 'increment') {
                await updateQuantity(productId, 1);
            } else if (action === 'decrement') {
                await updateQuantity(productId, -1);
            } else if (action === 'remove') {
                await removeFromCart(productId);
            }
        });
    }

    if (paymentRadios.length) {
        paymentRadios.forEach(radio => {
            radio.addEventListener('change', (event) => {
                if (event.target.checked) {
                    state.paymentMethod = event.target.value;
                }
            });
        });
    }

    if (clearCartButton) {
        clearCartButton.addEventListener('click', async () => {
            if (!state.cart.length) {
                return;
            }
            await clearCart();
            notify('Cart cleared.');
        });
    }

    if (newOrderButton) {
        newOrderButton.addEventListener('click', async () => {
            await clearCart();
            notify('Ready for a new order.');
        });
    }

    if (checkoutButton) {
        checkoutButton.addEventListener('click', checkout);
    }

    if (receiptModal) {
        const registerModalClosers = () => {
            const triggers = receiptModal.querySelectorAll('[data-close-modal]');
            triggers.forEach(trigger => {
                trigger.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    closeModal();
                }, { once: false });
            });
        };

        registerModalClosers();
    }

    renderProducts();
    renderCart();
    updateCategoryFilter(); // Initialize category filter on page load

    // category filter handled above with client-side filtering
});
</script>
@endsection