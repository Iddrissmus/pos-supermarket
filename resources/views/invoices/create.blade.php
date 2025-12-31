@extends('layouts.app')

@section('title', 'Create Invoice')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">

    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center text-sm text-slate-500 mb-2">
            <a href="{{ route('invoices.index') }}" class="hover:text-indigo-500 transition-colors">Invoices</a>
            <span class="mx-2">/</span>
            <span class="text-slate-800">Create New</span>
        </div>
        <h1 class="text-2xl md:text-3xl font-bold text-slate-800">New Invoice</h1>
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
                        <label class="block text-sm font-medium mb-1" for="customer">Select Customer <span class="text-rose-500">*</span></label>
                        <select id="customer" class="form-select w-full border-slate-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 py-2.5 rounded shadow-sm" name="customer_id">
                            <option value="">Select a customer...</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" data-email="{{ $customer->email }}" data-phone="{{ $customer->phone }}">
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" for="customer_email">Email Address <span class="text-rose-500">*</span></label>
                        <input id="customer_email" class="form-input w-full border-slate-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 py-2.5 rounded shadow-sm" type="email" placeholder="customer@example.com" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1" for="customer_phone">Phone Number</label>
                        <input id="customer_phone" class="form-input w-full border-slate-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 py-2.5 rounded shadow-sm" type="text" placeholder="+233..." />
                    </div>
                </div>
            </div>

            <!-- Items Section -->
            <div class="bg-white border border-slate-200 rounded-sm shadow-sm">
                <header class="px-5 py-4 border-b border-slate-100 flex justify-between items-center">
                    <h2 class="font-semibold text-slate-800">Invoice Items</h2>
                    <div class="text-sm text-slate-500">
                        Select products from the list to add them.
                    </div>
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
                                        No items added yet. Click on products in the list to add them.
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
                        <svg class="w-4 h-4 fill-current mr-2 text-slate-400" viewBox="0 0 16 16">
                            <path d="M12 0H4a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V2a2 2 0 00-2-2zM4 2h8v2H4V2zm0 12V9h8v5H4z" />
                        </svg>
                        <span class="font-medium">Save as Draft</span>
                    </button>

                    <a href="{{ route('invoices.index') }}" class="btn w-full bg-transparent border border-transparent hover:bg-slate-50 text-slate-500 hover:text-slate-700 inline-flex items-center justify-center px-4 py-2.5 rounded transition-colors duration-150">
                        Cancel
                    </a>
                </div>
            </div>

            <!-- Settings Card -->
            <div class="bg-white border border-slate-200 rounded-sm shadow-sm p-5">
                <h3 class="text-slate-800 font-bold mb-4">Settings</h3>
                <div class="space-y-4">
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

            <!-- Product Selector -->
            <div class="bg-white border border-slate-200 rounded-sm shadow-sm p-5">
                <h3 class="text-slate-800 font-bold mb-4">Add Products</h3>
                <div class="mb-4 relative">
                    <input type="text" id="product-search" class="form-input w-full pl-9" placeholder="Search..." onkeyup="filterProducts()">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 fill-current text-slate-400" viewBox="0 0 16 16">
                            <path d="M7 14c-3.86 0-7-3.14-7-7s3.14-7 7-7 7 3.14 7 7-3.14 7-7 7zM7 2C4.243 2 2 4.243 2 7s2.243 5 5 5 5-2.243 5-5-2.243-5-5-5z" />
                            <path d="M15.707 14.293L13.314 11.9a8.019 8.019 0 01-1.414 1.414l2.393 2.393a.997.997 0 001.414 0 .999.999 0 000-1.414z" />
                        </svg>
                    </div>
                </div>
                <div class="max-h-80 overflow-y-auto space-y-2 pr-1" id="product-list">
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

        </div>

    </div>

    <!-- Message Modal (Reused) -->
   <div id="message-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900 bg-opacity-30 transition-opacity backdrop-filter backdrop-blur-sm" aria-hidden="true" onclick="closeModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div id="modal-icon" class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-rose-100 sm:mx-0 sm:h-10 sm:w-10">
                            <!-- Icon injected via JS -->
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-slate-900" id="modal-title">
                                <!-- Title injected via JS -->
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-slate-500" id="modal-message">
                                    <!-- Message injected via JS -->
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-slate-100">
                    <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-500 text-base font-medium text-white hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm" onclick="closeModal()">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    const invoiceItems = [];
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

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

    document.getElementById('customer').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        if (selected.value) {
            document.getElementById('customer_email').value = selected.dataset.email || '';
            document.getElementById('customer_phone').value = selected.dataset.phone || '';
        } else {
            document.getElementById('customer_email').value = '';
            document.getElementById('customer_phone').value = '';
        }
    });

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
                        No items added yet. Click on products in the list to add them.
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
                        <div class="font-medium text-slate-800">${item.name}</div>
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
        const customerId = document.getElementById('customer').value;
        const customerEmail = document.getElementById('customer_email').value;
        const customerPhone = document.getElementById('customer_phone').value;
        const branchId = document.getElementById('branch').value;
        const dueDate = document.getElementById('due_date').value;
        const notes = document.getElementById('notes').value;

        if (!customerId) {
             showModal('Missing Information', 'Please select a customer for this invoice.', 'error');
            return;
        }

        if (!customerEmail) {
            showModal('Missing Information', 'Please ensure the selected customer has a valid email address.', 'error');
            return;
        }
        
        if (invoiceItems.length === 0) {
            showModal('Empty Invoice', 'Please add at least one item to the invoice before saving.', 'error');
            return;
        }

        const data = {
            customer_id: customerId || null,
            customer_email: customerEmail,
            customer_phone: customerPhone,
            branch_id: branchId,
            due_date: dueDate,
            notes: notes,
            items: invoiceItems,
            send_now: sendImmediately
        };

        const saveBtn = document.getElementById('save-invoice-btn');
        const sendBtn = document.getElementById('save-send-invoice-btn');
        
        // Disable buttons
        saveBtn.disabled = true;
        sendBtn.disabled = true;
        
        // Update button state (more robust with full HTML replacement)
        if(sendImmediately) {
            const originalText = sendBtn.innerHTML;
            sendBtn.innerHTML = '<span class="flex items-center justify-center"><svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Sending...</span>';
        } else {
             const originalText = saveBtn.innerHTML;
             saveBtn.innerHTML = '<span class="flex items-center justify-center"><svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Saving...</span>';
        }

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
                showModal('Failed to Save', data.message || 'An error occurred while saving the invoice.', 'error');
                resetButtons(saveBtn, sendBtn);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showModal('System Error', 'An unexpected error occurred. Please try again or contact support.', 'error');
            resetButtons(saveBtn, sendBtn);
        });
    }

    function resetButtons(saveBtn, sendBtn) {
        saveBtn.disabled = false;
        sendBtn.disabled = false;
        
        saveBtn.innerHTML = `
             <svg class="w-4 h-4 fill-current mr-2 text-slate-400" viewBox="0 0 16 16">
                <path d="M12 0H4a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V2a2 2 0 00-2-2zM4 2h8v2H4V2zm0 12V9h8v5H4z" />
            </svg>
            <span class="font-medium">Save as Draft</span>
        `;
        
        sendBtn.innerHTML = `
            <svg class="w-4 h-4 fill-current mr-2 opacity-90 group-hover:translate-x-0.5 transition-transform" viewBox="0 0 16 16">
                <path d="M14.3 2.3L5 11.6 1.7 8.3c-.4-.4-1-.4-1.4 0-.4.4-.4 1 0 1.4l4 4c.2.2.4.3.7.3.3 0 .5-.1.7-.3l10-10c.4-.4.4-1 0-1.4-.4-.4-1-.4-1.4 0z" />
            </svg>
            <span class="font-semibold tracking-wide">Save & Send Invoice</span>
        `;
    }

    document.getElementById('save-invoice-btn').addEventListener('click', function() {
        saveInvoice(false);
    });

    document.getElementById('save-send-invoice-btn').addEventListener('click', function() {
        saveInvoice(true);
    });
</script>
@endsection
