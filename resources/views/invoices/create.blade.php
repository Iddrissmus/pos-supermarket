@extends('layouts.app')

@section('title', 'Create Invoice')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

    <!-- Page Header -->
    <div class="mb-8 flex justify-between items-center">
        <div>
            <div class="flex items-center text-sm text-slate-500 mb-2">
                <a href="{{ route('invoices.index') }}" class="hover:text-indigo-500 transition-colors">Invoices</a>
                <span class="mx-2">/</span>
                <span class="text-slate-800">Create New</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-bold text-slate-800">New Invoice</h1>
        </div>
        <div>
             <a href="{{ route('inventory.bulk-import') }}" target="_blank" class="btn bg-white border-slate-200 hover:border-slate-300 text-indigo-500 shadow-sm">
                <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                    <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                </svg>
                <span class="hidden xs:block ml-2">Bulk Import Products</span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6">

        <!-- Main Content (Left) -->
        <div class="col-span-12 xl:col-span-8 space-y-6">
            
            <!-- Customer Section -->
            <div class="bg-white border border-slate-200 rounded-sm shadow-sm p-5">
                <header class="mb-4 pb-4 border-b border-slate-100 flex justify-between items-center">
                    <h2 class="font-semibold text-slate-800">Customer Information</h2>
                </header>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="col-span-1 md:col-span-2">
                        <div class="flex justify-between items-center mb-1">
                            <label class="block text-sm font-medium" for="customer-select">Select or Enter Customer <span class="text-rose-500">*</span></label>
                            <button onclick="openCustomerModal()" class="text-xs text-indigo-500 hover:text-indigo-600 font-medium flex items-center">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                New Customer
                            </button>
                        </div>
                        <select id="customer-select" class="form-select w-full" name="customer_id" placeholder="Select or type new name...">
                            <option value="">Select a customer...</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" data-email="{{ $customer->email }}" data-phone="{{ $customer->phone }}">
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" id="customer_name" name="customer_name">
                        <input type="hidden" id="is_new_customer" value="false">
                        <p class="text-xs text-slate-500 mt-1" id="new-customer-hint" style="display:none;">
                            <span class="text-indigo-500 font-medium">New Customer:</span> Please provide email and phone.
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" for="customer_email">Email Address <span class="text-rose-500" id="email-required">*</span></label>
                        <input id="customer_email" class="form-input w-full border-slate-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 py-2.5 rounded shadow-sm" type="email" placeholder="customer@example.com" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" for="customer_phone">Phone Number <span class="text-rose-500" id="phone-required">*</span></label>
                        <input id="customer_phone" class="form-input w-full border-slate-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 py-2.5 rounded shadow-sm" type="text" placeholder="+233..." />
                    </div>
                </div>
            </div>

            <!-- Items Section -->
            <div class="bg-white border border-slate-200 rounded-sm shadow-sm">
                <header class="px-5 py-4 border-b border-slate-100">
                    <h2 class="font-semibold text-slate-800">Invoice Items</h2>
                </header>
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="table-auto w-full">
                            <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border border-slate-200">
                                <tr>
                                    <th class="px-4 py-3 whitespace-nowrap w-1/2 text-left">Product / Service</th>
                                    <th class="px-4 py-3 whitespace-nowrap w-24 text-right">Qty</th>
                                    <th class="px-4 py-3 whitespace-nowrap w-32 text-right">Price</th>
                                    <th class="px-4 py-3 whitespace-nowrap w-32 text-right">Total</th>
                                    <th class="px-4 py-3 whitespace-nowrap w-10"></th>
                                </tr>
                            </thead>
                            <tbody id="invoice-items-body" class="text-sm divide-y divide-slate-100 border-x border-b border-slate-200">
                                <!-- Dynamic Rows -->
                                <tr id="empty-cart-msg">
                                    <td colspan="5" class="px-4 py-8 text-center text-slate-500 italic">
                                        No items added yet.
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="border border-slate-200 bg-slate-50">
                                <tr>
                                    <td colspan="3" class="px-4 py-3 text-right font-bold text-slate-800">Subtotal</td>
                                    <td class="px-4 py-3 text-right font-bold text-emerald-600" id="invoice-subtotal">0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-medium mb-1" for="notes">Notes & Terms</label>
                        <textarea id="notes" class="form-textarea w-full" rows="3" placeholder="Payment terms, special instructions, or thank you note..."></textarea>
                    </div>
                </div>
            </div>

        </div>

        <!-- Sidebar (Right) -->
        <div class="col-span-12 xl:col-span-4 space-y-6">

            <!-- Actions Card -->
            <div class="bg-white border border-slate-200 rounded-sm shadow-sm p-5">
                <h3 class="text-slate-800 font-bold mb-4">Actions</h3>
                <div class="flex flex-col gap-3">
                    <button id="save-send-invoice-btn" class="btn w-full bg-indigo-600 hover:bg-indigo-700 text-white shadow-md group inline-flex items-center justify-center px-4 py-2.5 rounded transition-all duration-150 ease-in-out">
                        <svg class="w-4 h-4 fill-current mr-2 opacity-90 group-hover:translate-x-0.5 transition-transform" viewBox="0 0 16 16">
                            <path d="M14.3 2.3L5 11.6 1.7 8.3c-.4-.4-1-.4-1.4 0-.4.4-.4 1 0 1.4l4 4c.2.2.4.3.7.3.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4-.4-.4-1-.4-1.4 0z" />
                        </svg>
                        <span class="font-semibold tracking-wide">Save & Send Invoice</span>
                    </button>
                    
                    <button id="save-invoice-btn" class="btn w-full bg-white border border-slate-300 hover:border-slate-400 text-slate-700 hover:text-slate-900 shadow-sm inline-flex items-center justify-center px-4 py-2.5 rounded transition-colors duration-150 ease-in-out">
                        <span class="font-medium">Save as Draft</span>
                    </button>

                    <a href="{{ route('invoices.index') }}" class="btn w-full bg-transparent border border-transparent hover:bg-slate-50 text-slate-500 hover:text-slate-700 inline-flex items-center justify-center px-4 py-2.5 rounded transition-colors duration-150">
                        Cancel
                    </a>
                </div>
            </div>
            
            <!-- Item Entry Card -->
            <div class="bg-white border border-slate-200 rounded-sm shadow-sm p-5">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-slate-800 font-bold">Add Items</h3>
                    <div class="flex bg-slate-100 rounded p-1" x-data="{ mode: 'product' }">
                        <button class="px-3 py-1 text-xs font-medium rounded transition-colors" 
                            :class="mode === 'product' ? 'bg-white shadow text-indigo-500' : 'text-slate-500 hover:text-slate-600'" 
                            @click="mode = 'product'; document.getElementById('item-mode-product').classList.remove('hidden'); document.getElementById('item-mode-manual').classList.add('hidden');">Product</button>
                        <button class="px-3 py-1 text-xs font-medium rounded transition-colors" 
                            :class="mode === 'manual' ? 'bg-white shadow text-indigo-500' : 'text-slate-500 hover:text-slate-600'" 
                            @click="mode = 'manual'; document.getElementById('item-mode-manual').classList.remove('hidden'); document.getElementById('item-mode-product').classList.add('hidden');">Service</button>
                    </div>
                </div>
                
                <!-- Product Mode -->
                <div id="item-mode-product">
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-sm font-medium" for="product-search">Search Inventory</label>
                        <button onclick="openProductModal()" class="text-xs text-indigo-500 hover:text-indigo-600 font-medium flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            New Product
                        </button>
                    </div>
                    <div class="relative mb-4">
                        <input type="text" id="product-search" class="form-input w-full pl-9" placeholder="Search product name..." onkeyup="filterProducts()">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 fill-current text-slate-400" viewBox="0 0 16 16">
                                <path d="M7 14c-3.86 0-7-3.14-7-7s3.14-7 7-7 7 3.14 7-7 7zM7 2C4.243 2 2 4.243 2 7s2.243 5 5 5 5-2.243 5-5-2.243-5-5-5z" />
                                <path d="M15.707 14.293L13.314 11.9a8.019 8.019 0 01-1.414 1.414l2.393 2.393a.997.997 0 001.414 0 .999.999 0 000-1.414z" />
                            </svg>
                        </div>
                    </div>
                    <div class="max-h-60 overflow-y-auto space-y-2 pr-1" id="product-list">
                        @forelse($products as $product)
                            <div class="product-item group p-3 border border-slate-200 rounded-sm hover:border-indigo-400 transition cursor-pointer flex justify-between items-center bg-slate-50 hover:bg-white"
                                 onclick="addProductToInvoice({{ json_encode($product) }})">
                                <div class="flex-1 min-w-0 mr-3">
                                    <div class="text-sm font-semibold text-slate-800 product-name truncate">{{ $product['name'] }}</div>
                                    <div class="text-xs text-slate-500">Stock: {{ $product['stock'] }}</div>
                                </div>
                                <div class="text-sm font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded">
                                    {{ number_format($product['price'], 2) }}
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-slate-500 text-sm">No products available.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Manual Mode -->
                <div id="item-mode-manual" class="hidden space-y-3">
                    <div>
                        <label class="block text-sm font-medium mb-1" for="manual_name">Description / Service Name</label>
                        <input id="manual_name" class="form-input w-full" type="text" placeholder="e.g. Consulting Fee" />
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium mb-1" for="manual_price">Price</label>
                            <input id="manual_price" class="form-input w-full" type="number" step="0.01" placeholder="0.00" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1" for="manual_qty">Quantity</label>
                            <input id="manual_qty" class="form-input w-full" type="number" step="1" value="1" />
                        </div>
                    </div>
                    <button onclick="addManualItem()" class="btn w-full bg-slate-800 hover:bg-slate-900 text-white">Add Item</button>
                    <p class="text-xs text-slate-500 text-center">Items added manually are not tracked in inventory.</p>
                </div>
            </div>

            <!-- Settings Card -->
            <div class="bg-white border border-slate-200 rounded-sm shadow-sm p-5">
                <h3 class="text-slate-800 font-bold mb-4">Invoice Settings</h3>
                <div class="space-y-4">
                     <!-- Delivery Options -->
                    <div x-data="{ deliveryType: 'instant' }">
                        <label class="block text-sm font-medium mb-2">Delivery Method</label>
                        <div class="grid grid-cols-3 gap-2 mb-3">
                            <label class="cursor-pointer">
                                <input type="radio" name="delivery_type" value="instant" class="peer sr-only" x-model="deliveryType">
                                <div class="text-center p-2 border rounded hover:bg-slate-50 peer-checked:bg-indigo-50 peer-checked:border-indigo-500 peer-checked:text-indigo-600 transition">
                                    <span class="block text-xs font-semibold">Instant</span>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="delivery_type" value="scheduled" class="peer sr-only" x-model="deliveryType">
                                <div class="text-center p-2 border rounded hover:bg-slate-50 peer-checked:bg-indigo-50 peer-checked:border-indigo-500 peer-checked:text-indigo-600 transition">
                                    <span class="block text-xs font-semibold">Schedule</span>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="delivery_type" value="recurring" class="peer sr-only" x-model="deliveryType">
                                <div class="text-center p-2 border rounded hover:bg-slate-50 peer-checked:bg-indigo-50 peer-checked:border-indigo-500 peer-checked:text-indigo-600 transition">
                                    <span class="block text-xs font-semibold">Recurring</span>
                                </div>
                            </label>
                        </div>
                        
                        <div x-show="deliveryType === 'scheduled'" class="mb-3" style="display: none;">
                             <label class="block text-xs font-medium mb-1">Date & Time</label>
                             <input id="scheduled_send_date" class="form-input w-full text-sm" type="datetime-local" />
                        </div>
                        
                        <div x-show="deliveryType === 'recurring'" class="mb-3" style="display: none;">
                             <label class="block text-xs font-medium mb-1">Frequency</label>
                             <select id="recurring_frequency" class="form-select w-full text-sm">
                                <option value="weekly">Weekly</option>
                                <option value="monthly" selected>Monthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1" for="branch">Issuing Branch</label>
                        <select id="branch" class="form-select w-full" name="branch_id">
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ count($branches) == 1 ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                     <div>
                        <label class="block text-sm font-medium mb-1" for="due_date">Due Date</label>
                        <input id="due_date" class="form-input w-full" type="date" value="{{ now()->addDays(7)->format('Y-m-d') }}" />
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Quick Create Product Modal -->
    <div id="product-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-100/50 bg-opacity-30 transition-opacity backdrop-filter backdrop-blur-sm shadow-inner" aria-hidden="true" onclick="closeProductModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-middle bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-slate-200">
                <div class="bg-white px-6 pt-5 pb-4 sm:p-10 sm:pb-8">
                    <div class="flex items-center justify-between mb-8 pb-4 border-b border-slate-100">
                        <h3 class="text-2xl font-black text-slate-900">Quick Add Product</h3>
                        <button type="button" onclick="closeProductModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-12 gap-y-6">
                        <!-- Left Column: Basic Info -->
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Product Name *</label>
                                <input type="text" id="qp-name" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 bg-white placeholder-slate-400 text-slate-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none" placeholder="e.g. Wireless Mouse">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Category *</label>
                                <div class="relative">
                                    <input type="text" id="qp-category-search" placeholder="Search categories..." class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 bg-white placeholder-slate-400 text-slate-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none">
                                    <div id="qp-category-dropdown" class="hidden absolute z-50 w-full mt-1 bg-white border-2 border-slate-100 rounded-xl shadow-2xl max-h-60 overflow-y-auto">
                                        <div id="qp-category-results"></div>
                                    </div>
                                    <select id="qp-category-id" class="hidden">
                                        <option value="">-- Select --</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" data-name="{{ strtolower($category->name) }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Description</label>
                                <textarea id="qp-description" rows="4" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 bg-white placeholder-slate-400 text-slate-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none" placeholder="Enter a brief product description..."></textarea>
                            </div>
                        </div>

                        <!-- Right Column: Pricing & Stock -->
                        <div class="space-y-6">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Selling Price *</label>
                                    <div class="relative">
                                        <span class="absolute left-4 top-3.5 text-slate-400 font-bold">₵</span>
                                        <input type="number" id="qp-price" class="w-full pl-8 pr-4 py-3.5 rounded-xl border-2 border-slate-200 bg-white placeholder-slate-400 text-slate-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none font-bold" placeholder="0.00" step="0.01">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Cost Price *</label>
                                    <div class="relative">
                                        <span class="absolute left-4 top-3.5 text-slate-400 font-bold">₵</span>
                                        <input type="number" id="qp-cost" class="w-full pl-8 pr-4 py-3.5 rounded-xl border-2 border-slate-200 bg-white placeholder-slate-400 text-slate-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none font-bold" placeholder="0.00" step="0.01">
                                    </div>
                                </div>
                            </div>

                            <div class="bg-indigo-50/50 rounded-2xl p-6 border-2 border-indigo-100/50">
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="p-2 bg-indigo-100 rounded-lg text-indigo-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                    </div>
                                    <h4 class="text-base font-black text-indigo-900 underline decoration-indigo-200 decoration-2 underline-offset-4">Stock Tracking (Box Mode)</h4>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-6 mb-6">
                                    <div>
                                        <label class="block text-xs font-black text-indigo-800 uppercase tracking-wider mb-2">Total Boxes *</label>
                                        <input type="number" id="qp-boxes" class="w-full px-4 py-3 rounded-xl border-2 border-indigo-200 bg-white text-slate-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all font-bold" value="0">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-black text-indigo-800 uppercase tracking-wider mb-2">Units / Box *</label>
                                        <input type="number" id="qp-per-box" class="w-full px-4 py-3 rounded-xl border-2 border-indigo-200 bg-white text-slate-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all font-bold" value="1">
                                    </div>
                                </div>
                                
                                <div class="flex justify-between items-center px-4 py-3 bg-white/50 rounded-xl border border-indigo-100">
                                    <span class="text-sm font-bold text-indigo-600">Initial Inventory Depth:</span>
                                    <div class="flex items-baseline gap-1">
                                        <span id="qp-total-units" class="text-xl font-black text-indigo-900">0</span>
                                        <span class="text-xs font-bold text-indigo-400">UNITS</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 px-6 py-6 sm:px-10 sm:flex sm:flex-row-reverse border-t border-slate-100 gap-4">
                    <button type="button" onclick="createProduct()" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-xl px-8 py-4 bg-indigo-600 text-base font-black text-white hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-500/20 transition-all sm:w-auto sm:text-sm uppercase tracking-widest">
                        Save & Add Product
                    </button>
                    <button type="button" onclick="closeProductModal()" class="mt-3 w-full inline-flex justify-center rounded-xl border-2 border-slate-200 shadow-sm px-8 py-4 bg-white text-base font-bold text-slate-600 hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-slate-500/10 transition-all sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Create Customer Modal -->
    <div id="customer-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-30 transition-opacity backdrop-filter backdrop-blur-sm" aria-hidden="true" onclick="closeCustomerModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-slate-900 mb-4">Quick Add Customer</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium mb-1">Customer Name *</label>
                            <input type="text" id="qc-name" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 bg-white placeholder-gray-400 text-gray-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none" placeholder="Full Name">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Email Address</label>
                            <input type="email" id="qc-email" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 bg-white placeholder-gray-400 text-gray-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none" placeholder="email@example.com">
                        </div>
                         <div>
                            <label class="block text-sm font-medium mb-1">Phone Number *</label>
                            <input type="text" id="qc-phone" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 bg-white placeholder-gray-400 text-gray-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none" placeholder="024XXXXXXX">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Customer Type</label>
                            <select id="qc-type" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none">
                                <option value="individual">Individual</option>
                                <option value="business">Business</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Payment Terms</label>
                            <select id="qc-terms" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none">
                                <option value="immediate">Immediate</option>
                                <option value="next_15">Next 15</option>
                                <option value="next_30" selected>Next 30</option>
                                <option value="next_60" >Next 60</option>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium mb-1">Company (Optional)</label>
                            <input type="text" id="qc-company" class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 bg-white placeholder-gray-400 text-gray-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none" placeholder="Company Name">
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-slate-100">
                    <button type="button" onclick="createCustomer()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Create & Select
                    </button>
                    <button type="button" onclick="closeCustomerModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Message Modal (Reused) -->
   <div id="message-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-30 transition-opacity backdrop-filter backdrop-blur-sm" aria-hidden="true" onclick="closeModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div id="modal-icon" class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-rose-100 sm:mx-0 sm:h-10 sm:w-10"></div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-slate-900" id="modal-title"></h3>
                            <div class="mt-2">
                                <p class="text-sm text-slate-500" id="modal-message"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-slate-100">
                    <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-500 text-base font-medium text-white hover:bg-indigo-600 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm" onclick="closeModal()">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
/* TomSelect custom adjustments if needed */
.ts-control { border-radius: 0.25rem; border-color: #cbd5e1; padding-top: 0.625rem; padding-bottom: 0.625rem; }
.ts-wrapper.multi .ts-control > div { background: #f1f5f9; border-radius: 0.25rem; }
</style>

<script>
    const invoiceItems = [];
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let tomSelectCustomer;

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize TomSelect for Customer
        const customerSelect = document.getElementById('customer-select');
        tomSelectCustomer = new TomSelect(customerSelect, {
            create: true,
            sortField: {
                field: "text",
                direction: "asc"
            },
            placeholder: 'Select or type name...',
            onOptionAdd: function(value, data) {
                 // Triggered when a new option is added
                 handleCustomerSelection(value, true);
            },
            onChange: function(value) {
                // Check if value is ID or Name
                // If it's a number, it's an ID. If string and not in initial options, it's new.
                // However, TomSelect uses the value provided in Create.
                // We'll inspect the selected logic in handleCustomerSelection
                if (value) handleCustomerSelection(value, false);
            }
        });
    });

    function handleCustomerSelection(value, isJustAded) {
        const isNumeric = /^\d+$/.test(value);
        const nameInput = document.getElementById('customer_name');
        const isNewInput = document.getElementById('is_new_customer');
        const hint = document.getElementById('new-customer-hint');
        const emailReq = document.getElementById('email-required');
        const phoneReq = document.getElementById('phone-required');

        if (!isNumeric) {
            // New Customer
            nameInput.value = value;
            isNewInput.value = 'true';
            hint.style.display = 'block';
            
            // Clear fields for entry
            document.getElementById('customer_email').value = '';
            document.getElementById('customer_phone').value = '';
            
            // Highlight requirements
            emailReq.classList.add('text-rose-600');
            phoneReq.classList.add('text-rose-600');
        } else {
            // Existing Customer
            isNewInput.value = 'false';
            nameInput.value = ''; // Not needed
            hint.style.display = 'none';

            // Find data in original options (or TomSelect options)
            // TomSelect stores options in its instance
            const option = tomSelectCustomer.options[value];
            if (option) {
                // We stored data attributes in options, TomSelect reads them? 
                // TomSelect options are objects. We need to scrape the DOM or store data elsewhere.
                // Or just use the original select options if not removed?
                // Easier: Use a lookup object injected from backend
                // But for now, let's try to find it in the original DOM which TomSelect syncs with?
                // Actually TomSelect hides original.
                
                // Let's use a workaround: The Foreach loop created options with data-attributes.
                // We can query the original select (even if hidden)
                const originalOption = document.querySelector(`#customer-select option[value="${value}"]`);
                if (originalOption) {
                    document.getElementById('customer_email').value = originalOption.dataset.email || '';
                    document.getElementById('customer_phone').value = originalOption.dataset.phone || '';
                }
            }
        }
    }

    // Modal Logic
    function showModal(title, message, type = 'error') {
        const modal = document.getElementById('message-modal');
        const iconDiv = document.getElementById('modal-icon');
        const titleEl = document.getElementById('modal-title');
        const messageEl = document.getElementById('modal-message');

        titleEl.textContent = title;
        messageEl.textContent = message;

        if (type === 'success') {
            iconDiv.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-emerald-100 sm:mx-0 sm:h-10 sm:w-10';
            iconDiv.innerHTML = '<svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
        } else {
            iconDiv.className = 'mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-rose-100 sm:mx-0 sm:h-10 sm:w-10';
            iconDiv.innerHTML = '<svg class="h-6 w-6 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>';
        }

        modal.classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('message-modal').classList.add('hidden');
    }
    
    // Product Modal Logic
    function openProductModal() {
        document.getElementById('product-modal').classList.remove('hidden');
    }
    
    function closeProductModal() {
        document.getElementById('product-modal').classList.add('hidden');
        // Clear fields logic handled in createProduct or manually
        document.getElementById('qp-name').value = '';
        document.getElementById('qp-category-id').value = '';
        document.getElementById('qp-category-search').value = '';
        document.getElementById('qp-description').value = '';
        document.getElementById('qp-price').value = '';
        document.getElementById('qp-cost').value = '';
        document.getElementById('qp-boxes').value = '0';
        document.getElementById('qp-per-box').value = '1';
        document.getElementById('qp-total-units').innerText = '0';
    }

    // Customer Modal Logic
    function openCustomerModal() {
        document.getElementById('customer-modal').classList.remove('hidden');
    }
    
    function closeCustomerModal() {
        document.getElementById('customer-modal').classList.add('hidden');
        // Clear fields
        document.getElementById('qc-name').value = '';
        document.getElementById('qc-email').value = '';
        document.getElementById('qc-phone').value = '';
        document.getElementById('qc-company').value = '';
    }

    function createCustomer() {
        const name = document.getElementById('qc-name').value;
        const email = document.getElementById('qc-email').value;
        const phone = document.getElementById('qc-phone').value;
        const type = document.getElementById('qc-type').value;
        const terms = document.getElementById('qc-terms').value;
        const company = document.getElementById('qc-company').value;
        
        if(!name || !phone) {
            alert('Name and Phone are required');
            return;
        }
        
        // Disable button
        const btn = document.querySelector('#customer-modal button[onclick="createCustomer()"]');
        const originalText = btn.innerText;
        btn.disabled = true;
        btn.innerText = 'Creating...';
        
        fetch('{{ route("customers.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                name: name,
                email: email,
                phone: phone,
                customer_type: type,
                payment_terms: terms,
                company: company
            })
        })
        .then(response => {
            if(!response.ok) return response.json().then(data => { throw new Error(data.message || 'Create failed') });
            return response.json();
        })
        .then(data => {
            const customer = data.customer;
            
            // Add to TomSelect
            tomSelectCustomer.addOption({
                value: customer.id,
                text: customer.name,
                email: customer.email,
                phone: customer.phone
            });
            
            // Select it
            tomSelectCustomer.setValue(customer.id);
            
            // Update fields
            document.getElementById('customer_email').value = customer.email || '';
            document.getElementById('customer_phone').value = customer.phone || '';
            
            closeCustomerModal();
            showModal('Success', 'Customer created and selected.', 'success');
        })
        .catch(err => {
            console.error(err);
            alert('Failed to create customer: ' + err.message);
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerText = originalText;
        });
    }
    
    let isCreatingProductFlag = false;
    function createProduct() {
        if (isCreatingProductFlag) return;
        
        const name = document.getElementById('qp-name').value;
        const categoryId = document.getElementById('qp-category-id').value;
        const description = document.getElementById('qp-description').value;
        const price = document.getElementById('qp-price').value;
        const cost = document.getElementById('qp-cost').value;
        const boxes = document.getElementById('qp-boxes').value;
        const perBox = document.getElementById('qp-per-box').value;
        const branchId = document.getElementById('branch').value;
        
        if(!name || !price || !categoryId || !cost) {
            showModal('Error', 'Name, Category, Price, and Cost are required', 'error');
            return;
        }
        
        isCreatingProductFlag = true;
        
        // Disable button
        const btn = document.querySelector('#product-modal button[onclick="createProduct()"]');
        const originalText = btn.innerText;
        btn.disabled = true;
        btn.innerText = 'Creating...';
        
        fetch('{{ route("product.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                name: name,
                category_id: categoryId,
                description: description,
                price: price,
                cost_price: cost,
                quantity_of_boxes: boxes || 0,
                quantity_per_box: perBox || 1,
                stock_quantity: (boxes || 0) * (perBox || 1),
                // Removed branch_id to keep product in warehouse until paid
            })
        })
        .then(response => {
            if(!response.ok) return response.json().then(data => { throw new Error(data.message || 'Create failed'); });
            return response.json();
        })
        .then(data => {
            closeProductModal();
            
            // Add directly to invoice
            const product = data.product;
            const newProduct = {
                id: product.id,
                name: product.name,
                price: parseFloat(product.price),
                stock: product.total_units || product.stock_quantity
            };
            
            addProductToInvoice(newProduct);
            showModal('Success', 'Product created and added to invoice.', 'success');
        })
        .catch(err => {
            console.error(err);
            showModal('Error', err.message || 'Failed to create product. Please check required fields.', 'error');
        })
        .finally(() => {
            isCreatingProductFlag = false;
            btn.disabled = false;
            btn.innerText = originalText;
        });
    }

    // Modal Field Listeners
    document.addEventListener('DOMContentLoaded', function() {
        const boxesInput = document.getElementById('qp-boxes');
        const perBoxInput = document.getElementById('qp-per-box');
        const totalDisplay = document.getElementById('qp-total-units');

        function updateTotal() {
            const total = (parseInt(boxesInput.value) || 0) * (parseInt(perBoxInput.value) || 0);
            totalDisplay.innerText = total;
        }

        boxesInput.addEventListener('input', updateTotal);
        perBoxInput.addEventListener('input', updateTotal);

        // Category Search
        const catSearch = document.getElementById('qp-category-search');
        const catDropdown = document.getElementById('qp-category-dropdown');
        const catResults = document.getElementById('qp-category-results');
        const catSelect = document.getElementById('qp-category-id');

        const catOptions = Array.from(catSelect.options)
            .filter(opt => opt.value !== '')
            .map(opt => ({
                id: opt.value,
                text: opt.text,
                name: opt.dataset.name
            }));

        function showAllCategories() {
            const term = catSearch.value.toLowerCase();
            const filtered = term ? catOptions.filter(opt => opt.name.includes(term)) : catOptions;
            
            if(filtered.length > 0) {
                catResults.innerHTML = filtered.map(opt => `
                    <div class="px-4 py-2 hover:bg-indigo-50 cursor-pointer text-sm text-gray-700" onclick="selectQuickCategory('${opt.id}', '${opt.text}')">
                        ${opt.text}
                    </div>
                `).join('');
                catDropdown.classList.remove('hidden');
            } else {
                catResults.innerHTML = '<div class="px-4 py-2 text-xs text-gray-400">No results found</div>';
                catDropdown.classList.remove('hidden');
            }
        }

        catSearch.addEventListener('input', showAllCategories);
        catSearch.addEventListener('focus', showAllCategories);
        catSearch.addEventListener('click', (e) => {
            e.stopPropagation();
            showAllCategories();
        });

        // Close dropdown on click outside
        document.addEventListener('click', function(e) {
            if(!catSearch.contains(e.target) && !catDropdown.contains(e.target)) {
                catDropdown.classList.add('hidden');
            }
        });
    });

    function selectQuickCategory(id, text) {
        document.getElementById('qp-category-id').value = id;
        document.getElementById('qp-category-search').value = text;
        document.getElementById('qp-category-dropdown').classList.add('hidden');
    }

    // Filter Logic
    function filterProducts() {
        const input = document.getElementById('product-search');
        const filter = input.value.toLowerCase();
        const nodes = document.querySelectorAll('.product-item');

        nodes.forEach(node => {
            const text = node.querySelector('.product-name').textContent.toLowerCase();
            if (text.includes(filter)) {
                node.style.display = "flex";
            } else {
                node.style.display = "none";
            }
        });
    }

    // Item Management
    function addProductToInvoice(product) {
        // Check if item already exists
        const existingItem = invoiceItems.find(i => i.product_id === product.id);
        if (existingItem) {
            existingItem.quantity++;
        } else {
            invoiceItems.push({
                product_id: product.id,
                name: product.name,
                quantity: 1,
                price: parseFloat(product.price)
            });
        }
        renderTable();
    }
    
    function addManualItem() {
        const name = document.getElementById('manual_name').value;
        const price = parseFloat(document.getElementById('manual_price').value);
        const qty = parseInt(document.getElementById('manual_qty').value);
        
        if(!name || isNaN(price) || isNaN(qty) || qty < 1) {
            showModal('Invalid Input', 'Please provide a valid name, price, and quantity.', 'error');
            return;
        }
        
        invoiceItems.push({
            product_id: null,
            name: name,
            quantity: qty,
            price: price
        });
        
        // Reset inputs
        document.getElementById('manual_name').value = '';
        document.getElementById('manual_price').value = '';
        document.getElementById('manual_qty').value = '1';
        
        renderTable();
        showModal('Item Added', 'Service/Manual item added successfully.', 'success');
    }

    function updateQuantity(index, newQty) {
        if (newQty < 1) return;
        invoiceItems[index].quantity = parseInt(newQty);
        renderTable();
    }
    
    function removeItem(index) {
        invoiceItems.splice(index, 1);
        renderTable();
    }

    function renderTable() {
        const tbody = document.getElementById('invoice-items-body');
        tbody.innerHTML = '';
        let total = 0;

        if (invoiceItems.length === 0) {
            tbody.innerHTML = `
                <tr id="empty-cart-msg">
                    <td colspan="5" class="px-4 py-8 text-center text-slate-500 italic">
                        No items added yet.
                    </td>
                </tr>
            `;
        }

        invoiceItems.forEach((item, index) => {
            const lineTotal = item.quantity * item.price;
            total += lineTotal;

            const row = `
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="font-medium text-slate-800">${item.name} <span class="text-xs text-slate-400 font-normal">${item.product_id ? '(Product)' : '(Service)'}</span></div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="flex justify-end">
                            <input type="number" min="1" class="form-input w-20 py-1 px-2 text-right border-slate-300 focus:border-indigo-300" 
                                value="${item.quantity}" onchange="updateQuantity(${index}, this.value)">
                        </div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-right text-slate-600">
                        ${item.price.toFixed(2)}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-right font-medium text-slate-800">
                        ${lineTotal.toFixed(2)}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-center">
                        <button class="text-rose-400 hover:text-rose-600 transition-colors" onclick="removeItem(${index})">
                            <svg class="w-4 h-4 shrink-0 fill-current" viewBox="0 0 16 16">
                                <path d="M5 7h2v6H5V7zm4 0h2v6H9V7zm3-6v2h4v2h-1v10c0 .6-.4 1-1 1H2c-.6 0-1-.4-1-1V5H0V1h4V0h8v1zM2 5v10h12V5H2z" />
                            </svg>
                        </button>
                    </td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', row);
        });

        document.getElementById('invoice-subtotal').textContent = total.toFixed(2);
    }

    function saveInvoice(sendImmediately = false) {
        // Collect Customer Data
        let customerId = document.getElementById('customer-select').value;
        const customerName = document.getElementById('customer_name').value;
        const isNewCustomer = document.getElementById('is_new_customer').value === 'true';
        const customerEmail = document.getElementById('customer_email').value;
        const customerPhone = document.getElementById('customer_phone').value;
        
        // If TomSelect create, value might be the text typed
        if (isNewCustomer && !customerId) {
            // Logic handled in backend: send name, email, phone with empty ID
             customerId = null;
        } else if (isNewCustomer && customerId === customerName) {
            // TomSelect might set value = text for new items
            customerId = null;
        }
        
        // Validation
        if (!customerId && !customerName) {
            showModal('Missing Information', 'Please select or enter a customer.', 'error');
            return;
        }

        if (isNewCustomer || !customerId) {
            if (!customerEmail || !customerPhone) {
                 showModal('Missing Information', 'New customers require an Email and Phone number.', 'error');
                 return;
            }
        }
        
        if (invoiceItems.length === 0) {
            showModal('Empty Invoice', 'Please add at least one item.', 'error');
            return;
        }

        const deliveryType = document.querySelector('input[name="delivery_type"]:checked').value;
        const scheduledDate = document.getElementById('scheduled_send_date').value;

        if (deliveryType === 'scheduled' && !scheduledDate) {
             showModal('Missing Information', 'Please select a date and time for scheduled delivery.', 'error');
             return;
        }

        const data = {
            customer_id: customerId,
            customer_name: customerName,
            customer_email: customerEmail,
            customer_phone: customerPhone,
            branch_id: document.getElementById('branch').value,
            due_date: document.getElementById('due_date').value,
            notes: document.getElementById('notes').value,
            items: invoiceItems,
            send_now: sendImmediately,
            delivery_type: deliveryType,
            scheduled_send_date: scheduledDate,
            is_recurring: deliveryType === 'recurring',
            recurring_frequency: document.getElementById('recurring_frequency').value,
        };
        
        // Button State
        const saveBtn = document.getElementById('save-invoice-btn');
        const sendBtn = document.getElementById('save-send-invoice-btn');
        saveBtn.disabled = true;
        sendBtn.disabled = true;
        // ... (Spinner logic could be added here similar to previous file)

        fetch('{{ route("invoices.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect_url;
            } else {
                showModal('Failed to Save', data.message || 'Error occurred.', 'error');
                saveBtn.disabled = false;
                sendBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showModal('System Error', 'An unexpected error occurred.', 'error');
            saveBtn.disabled = false;
            sendBtn.disabled = false;
        });
    }

    document.getElementById('save-invoice-btn').addEventListener('click', function() {
        saveInvoice(false);
    });

    document.getElementById('save-send-invoice-btn').addEventListener('click', function() {
        saveInvoice(true);
    });
</script>
@endsection
