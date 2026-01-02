@extends('layouts.app')

@section('title', 'Item Requests')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    <!-- Header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-slate-800">Stock Requests</h1>
            <p class="text-slate-500 mt-1">Request inventory from other branches for <span class="font-medium text-green-600">{{ $manager->branch->display_label }}</span></p>
        </div>
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <button onclick="document.getElementById('bulkUploadModal').classList.remove('hidden')" class="btn bg-white border-slate-200 hover:border-slate-300 text-slate-600 hover:text-green-600">
                <i class="fas fa-file-excel mr-2 text-green-600"></i> Bulk Request
            </button>
            <a href="{{ route('dashboard.manager') }}" class="btn bg-white border-slate-200 hover:border-slate-300 text-slate-600 hover:text-green-600">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg flex items-center shadow-sm">
            <i class="fas fa-check-circle mr-3 text-lg"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-lg flex items-center shadow-sm">
            <i class="fas fa-exclamation-circle mr-3 text-lg"></i> {{ session('error') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="mb-6 p-4 bg-amber-50 border border-amber-200 text-amber-700 rounded-lg shadow-sm">
            <div class="flex items-start">
                <i class="fas fa-exclamation-triangle mr-3 text-lg mt-0.5"></i>
                <div>
                     <p class="font-medium">{{ session('warning') }}</p>
                    @if(session('import_errors'))
                        <div class="mt-2 text-sm">
                            <details class="cursor-pointer">
                                <summary class="font-semibold hover:text-amber-800">View Errors ({{ count(session('import_errors')) }})</summary>
                                <ul class="list-disc list-inside mt-2 pl-2 space-y-1 text-xs text-amber-800">
                                    @foreach(session('import_errors') as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </details>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        
        <!-- Left: Create Request Form -->
        <div class="xl:col-span-1">
            @if($availableProducts->count() > 0)
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden sticky top-8">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                    <h2 class="font-bold text-slate-800">New Request</h2>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-600">Step 1</span>
                </div>
                <div class="p-6">
                    <form method="POST" action="{{ route('manager.item-requests.store') }}" class="space-y-4">
                        @csrf
                        
                        <!-- Product -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Product</label>
                            <select id="product_id" name="product_id" 
                                    class="form-select w-full h-12 text-lg" 
                                    required>
                                <option value="">Select a product...</option>
                                @foreach($availableProducts as $product)
                                    <option value="{{ $product->id }}">
                                        {{ $product->name }} ({{ $product->barcode }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Branch -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Source Branch</label>
                            <select id="from_branch_id" name="from_branch_id" 
                                    class="form-select w-full h-12 text-lg" 
                                    required disabled>
                                <option value="">Select product first...</option>
                            </select>
                            <p id="stock-info" class="text-sm text-emerald-600 font-medium mt-2 min-h-[1.5rem]"></p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Boxes</label>
                                <div class="relative">
                                    <input type="number" id="quantity_of_boxes" name="quantity_of_boxes" min="1" 
                                           class="form-input w-full pr-8 h-12 text-lg font-bold text-slate-800" required placeholder="0">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="fas fa-box text-slate-400"></i>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Units/Box</label>
                                <input type="number" id="quantity_per_box" name="quantity_per_box" min="1" 
                                       class="form-input w-full bg-slate-50 h-12 text-lg text-slate-600" required readonly>
                            </div>
                        </div>

                        <!-- Calculator Result -->
                        <div class="bg-green-50/50 rounded-lg p-4 text-center border border-green-100">
                             <p class="text-xs text-slate-500 mb-1">Total Requsted Quantity</p>
                             <p class="text-2xl font-bold text-green-600" id="total-units-display">0 units</p>
                             <p class="text-xs text-slate-400" id="calc-breakdown">(0 boxes × 0 units)</p>
                        </div>

                        <!-- Reason -->
                         <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Reason (Optional)</label>
                            <textarea name="reason" rows="3" class="form-textarea w-full text-base" placeholder="e.g. Low stock trigger"></textarea>
                        </div>

                        <button type="submit" class="btn w-full bg-green-600 hover:bg-green-700 text-white shadow-lg hover:shadow-xl rounded-full py-4 text-lg transform hover:scale-[1.02] transition-all duration-200 group font-bold tracking-wide flex items-center justify-center">
                            Submit Request <i class="fas fa-paper-plane ml-2 group-hover:translate-x-1 transition-transform"></i>
                        </button>
                    </form>
                </div>
            </div>
            @else
            <div class="bg-amber-50 rounded-xl p-6 border border-amber-200 text-center">
                 <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-3 text-amber-600">
                     <i class="fas fa-box-open text-xl"></i>
                 </div>
                 <h3 class="text-lg font-bold text-amber-800">No Products Available</h3>
                 <p class="text-sm text-amber-700 mt-2">There are currently no products available in other branches (or warehouse) to request.</p>
            </div>
            @endif
        </div>

        <!-- Right: Recent & Pending Requests -->
        <div class="xl:col-span-2 space-y-8">
            
            <!-- Pending Section -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                    <div class="flex items-center gap-2">
                         <div class="w-2 h-2 rounded-full bg-amber-500"></div>
                        <h2 class="font-bold text-slate-800">Pending Requests</h2>
                    </div>
                    <span class="text-xs font-medium bg-slate-200 text-slate-600 px-2 py-0.5 rounded-full">{{ $pendingRequests->count() }}</span>
                </div>
                
                <div class="overflow-x-auto">
                    @if($pendingRequests->count() > 0)
                    <table class="w-full text-left">
                        <thead class="bg-slate-50/50 border-b border-slate-100 text-xs text-slate-500 uppercase">
                            <tr>
                                <th class="px-6 py-3 font-semibold">Product</th>
                                <th class="px-6 py-3 font-semibold">From</th>
                                <th class="px-6 py-3 font-semibold text-center">Qty</th>
                                <th class="px-6 py-3 font-semibold text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($pendingRequests as $request)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-800">{{ $request->product->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $request->product->barcode }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    @if($request->from_branch_id)
                                        <i class="fas fa-store text-slate-400 mr-1.5"></i>
                                        {{ optional($request->fromBranch)->display_label }}
                                    @else
                                        <i class="fas fa-warehouse text-purple-400 mr-1.5"></i>
                                        <span class="px-2 py-0.5 rounded bg-purple-100 text-purple-700 font-semibold text-xs">Central Warehouse</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="font-bold text-slate-800">{{ $request->quantity_of_boxes }}</span> <span class="text-xs text-slate-500">boxes</span>
                                    <div class="text-[10px] text-slate-400">Total: {{ $request->quantity }}</div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <form method="POST" action="{{ route('manager.item-requests.cancel', $request) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-xs font-medium text-rose-500 hover:text-rose-700 hover:underline" onclick="return confirm('Cancel this request?')">
                                            Cancel
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                     <div class="px-6 py-4 border-t border-slate-100">
                        {{ $pendingRequests->links() }}
                    </div>
                    @else
                    <div class="p-8 text-center text-slate-500 text-sm">
                        No pending requests at the moment.
                    </div>
                    @endif
                </div>
            </div>

            <!-- History Section -->
            @if($completedRequests->count() > 0)
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                    <h2 class="font-bold text-slate-800">Request History</h2>
                    <a href="#" class="text-xs font-medium text-green-600 hover:text-green-800">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50/50 border-b border-slate-100 text-xs text-slate-500 uppercase">
                            <tr>
                                <th class="px-6 py-3 font-semibold">Product</th>
                                <th class="px-6 py-3 font-semibold">From</th>
                                <th class="px-6 py-3 font-semibold text-center">Qty</th>
                                <th class="px-6 py-3 font-semibold">Status</th>
                                <th class="px-6 py-3 font-semibold text-right">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($completedRequests as $request)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-800">{{ $request->product->name }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    @if($request->from_branch_id)
                                        {{ optional($request->fromBranch)->name }}
                                    @else
                                        Warehouse
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm font-medium text-slate-700">{{ $request->quantity_of_boxes }} boxes</span>
                                </td>
                                <td class="px-6 py-4">
                                     @if($request->status === 'approved')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">Approved</span>
                                    @elseif($request->status === 'rejected')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-rose-100 text-rose-800">Rejected</span>
                                    @elseif($request->status === 'cancelled')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600">Canceled</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right text-xs text-slate-500">
                                    {{ $request->updated_at->format('M d') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                     <div class="px-6 py-4 border-t border-slate-100">
                        {{ $completedRequests->links() }}
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>

<!-- Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Data passed from blade
    const productBranchData = {};

    @foreach($availableProducts as $product)
        productBranchData[{{ $product->id }}] = {
            quantity_per_box: {{ $product->quantity_per_box ?? 1 }},
            branches: [
                @if(($product->total_units - $product->assigned_units) > 0)
                {
                    branch_id: "", 
                    branch_name: "Central Warehouse",
                    stock: {{ $product->total_units - $product->assigned_units }},
                    boxes: {{ floor(($product->total_units - $product->assigned_units) / max(1, $product->quantity_per_box ?? 1)) }}
                },
                @endif
                @foreach($product->branchProducts as $bp)
                @if($bp->branch_id != $manager->branch_id)
                {
                    branch_id: "{{ $bp->branch_id }}", // Key as string for compatibility
                    branch_name: "{{ $bp->branch->display_label }}",
                    stock: {{ $bp->stock_quantity }},
                    boxes: {{ $bp->quantity_of_boxes ?? 0 }}
                },
                @endif
                @endforeach
            ]
        };
    @endforeach

    // Initialize TomSelect for Product
    let productTom = new TomSelect('#product_id', {
        create: false,
        placeholder: "Search for a product...",
        onChange: function(value) {
            updateBranchOptions(value);
        }
    });

    // Initialize TomSelect for Branch
    let branchTom = new TomSelect('#from_branch_id', {
        create: false,
        placeholder: "Select source...",
        valueField: 'id',
        labelField: 'name',
        searchField: ['name'],
        options: [],
        render: {
            option: function(data, escape) {
                return '<div class="py-1"><div>' + escape(data.name) + '</div><div class="text-xs text-slate-500">' + escape(data.info) + '</div></div>';
            },
            item: function(data, escape) {
                return '<div>' + escape(data.name) + ' <span class="text-slate-500 text-xs">(' + escape(data.stock_info) + ')</span></div>';
            }
        },
        onChange: function(value) {
            updateStockInfo(value);
        }
    });
    branchTom.disable();

    // DOM Elements
    const boxesInput = document.getElementById('quantity_of_boxes');
    const unitsPerBoxInput = document.getElementById('quantity_per_box');
    const stockInfo = document.getElementById('stock-info');

    function updateBranchOptions(productId) {
        // Reset Inputs
        branchTom.clear();
        branchTom.clearOptions();
        branchTom.disable();
        
        boxesInput.value = '';
        boxesInput.max = '';
        unitsPerBoxInput.value = '';
        stockInfo.textContent = '';
        updateTotal();

        if (productId && productBranchData[productId]) {
             const data = productBranchData[productId];
             
             // Set Units
             unitsPerBoxInput.value = data.quantity_per_box;

             // Populate Branches
             data.branches.forEach(branch => {
                 branchTom.addOption({
                     id: branch.branch_id,
                     name: branch.branch_name,
                     info: `${branch.boxes} boxes`,
                     stock_info: `${branch.boxes} boxes`,
                     boxes: branch.boxes,
                     stock: branch.stock
                 });
             });
             
             branchTom.enable();
        }
    }

    function updateStockInfo(branchId) {
        // Handle warehouse (empty string id) or branch id
        // TomSelect values are strings usually
        
        // Find the selected option data from TomSelect instance logic or just look up in our data if needed,
        // but easier to pull from the option object if we stored it? 
        // TomSelect options are stored in branchTom.options[value]
        
        // Note: branchId might be "" for warehouse, need to handle key carefully
        let option = branchTom.options[branchId];
        
        if (option) {
            stockInfo.textContent = `Available: ${option.boxes} boxes (${option.stock} units)`;
            boxesInput.max = option.boxes;
        } else {
            stockInfo.textContent = '';
            boxesInput.max = '';
        }
    }

    // Calculator Logic
    function updateTotal() {
        const boxes = parseInt(boxesInput.value) || 0;
        const units = parseInt(unitsPerBoxInput.value) || 0;
        const total = boxes * units;
        
        document.getElementById('total-units-display').textContent = `${total} units`;
        document.getElementById('calc-breakdown').textContent = `(${boxes} boxes × ${units} units/box)`;
    }

    boxesInput.addEventListener('input', updateTotal);
});
</script>

<!-- Bulk Upload Modal -->
<div id="bulkUploadModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('bulkUploadModal').classList.add('hidden')"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-file-upload text-green-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-slate-900" id="modal-title">Bulk Request Upload</h3>
                        <div class="mt-2 text-sm text-slate-500">
                            Upload an Excel file to request multiple items at once.
                        </div>
                        
                        <div class="mt-4 p-4 bg-slate-50 rounded-lg border border-slate-100">
                            <h4 class="text-xs font-bold text-slate-700 uppercase mb-2">Steps</h4>
                            <ol class="list-decimal list-inside text-xs text-slate-600 space-y-1">
                                <li>Download the template.</li>
                                <li>Fill in product details and quantities.</li>
                                <li>Upload the saved file.</li>
                            </ol>
                            <div class="mt-3">
                                <a href="{{ route('manager.item-requests.download-template') }}" class="text-sm font-medium text-green-600 hover:text-green-500 hover:underline">
                                    <i class="fas fa-download mr-1"></i> Download Template
                                </a>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('manager.item-requests.bulk-upload') }}" enctype="multipart/form-data" class="mt-4">
                            @csrf
                            <label class="block text-sm font-medium text-slate-700 mb-2">Upload File</label>
                            <input type="file" name="file" accept=".xlsx,.xls" required class="block w-full text-sm text-slate-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-full file:border-0
                                file:text-sm file:font-semibold
                                file:bg-green-50 file:text-green-700
                                hover:file:bg-green-100 rounded-lg border border-slate-300">
                            
                            <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:col-start-2 sm:text-sm">
                                    Upload
                                </button>
                                <button type="button" onclick="document.getElementById('bulkUploadModal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
