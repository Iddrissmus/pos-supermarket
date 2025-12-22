<div class="space-y-6 p-6">
    <div class="bg-white rounded-lg shadow p-4 space-y-4">
        @if($updating_product_id)
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                <div class="flex items-center">
                    <i class="fas fa-edit text-blue-600 mr-2"></i>
                    <span class="text-blue-800 font-medium">Updating Product Assignment</span>
                </div>
                <p class="text-blue-700 text-sm mt-1">Modify the values below and click "Update Product" to save changes.</p>
            </div>
        @endif
        <div class="grid md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                <select wire:model="branch_id" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">Select branch</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->display_label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Product</label>
                <select wire:model="product_id" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">Select product</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Price</label>
                <input type="number" step="0.01" wire:model.defer="price" class="w-full border border-gray-300 rounded-lg px-3 py-2" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cost Price</label>
                <input type="number" step="0.01" wire:model.defer="cost_price" class="w-full border border-gray-300 rounded-lg px-3 py-2" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Stock</label>
                <input type="number" wire:model.defer="stock_quantity" class="w-full border border-gray-300 rounded-lg px-3 py-2" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Reorder Level</label>
                <input type="number" wire:model.defer="reorder_level" class="w-full border border-gray-300 rounded-lg px-3 py-2" />
            </div>
        </div>
        <div>
            @if($updating_product_id)
                <div class="flex space-x-2">
                    <button type="button" wire:click="updatePivot({{ $updating_product_id }})" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">Update Product</button>
                    <button type="button" wire:click="cancelUpdate" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">Cancel Update</button>
                </div>
            @else
                <button type="button" wire:click="assign" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Assign to Branch</button>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-700">
                @if($isAdmin && !$selectedBranch)
                    All Product Assignments (Select a branch to manage assignments)
                @elseif($selectedBranch)
                    Assignments for {{ $selectedBranch->display_label }}
                @else
                    Select a branch to view its product assignments
                @endif
            </h2>
        </div>
        
        @if($isAdmin && !$selectedBranch)
            <!-- Admin view: All assignments across all branches -->
            @if($assigned->count() > 0)
                @foreach($assigned as $branch)
                    <div class="border-b border-gray-100 last:border-b-0">
                        <div class="bg-gray-50 px-4 py-2">
                            <h3 class="font-medium text-gray-800">{{ $branch->display_label }}</h3>
                            <p class="text-xs text-gray-600">{{ $branch->business->name }}</p>
                        </div>
                        <table class="w-full">
                            <thead class="bg-gray-25">
                                <tr>
                                    <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Product</th>
                                    <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Price</th>
                                    <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Cost</th>
                                    <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Stock</th>
                                    <th class="text-left px-4 py-2 text-xs font-medium text-gray-500">Reorder</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($branch->products as $product)
                                    <tr class="border-t border-gray-100">
                                        <td class="px-4 py-2 text-sm">{{ $product->name }}</td>
                                        <td class="px-4 py-2 text-sm">₵{{ number_format($product->pivot->price, 2) }}</td>
                                        <td class="px-4 py-2 text-sm">₵{{ number_format($product->pivot->cost_price, 2) }}</td>
                                        <td class="px-4 py-2 text-sm">{{ $product->pivot->stock_quantity }}</td>
                                        <td class="px-4 py-2 text-sm">{{ $product->pivot->reorder_level }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            @else
                <div class="p-12 text-center text-gray-500">
                    <i class="fas fa-boxes text-4xl mb-4 text-gray-300"></i>
                    <p class="text-lg mb-2">No product assignments found</p>
                    <p class="text-sm">No products have been assigned to any branches yet.</p>
                </div>
            @endif
        @else
            <!-- Branch-specific view (both admin and manager when branch is selected) -->
            <!-- Branch-specific view (both admin and manager when branch is selected) -->
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-4 py-2">Product</th>
                        <th class="text-left px-4 py-2">Price</th>
                        <th class="text-left px-4 py-2">Cost</th>
                        <th class="text-left px-4 py-2">Stock</th>
                        <th class="text-left px-4 py-2">Reorder</th>
                        <th class="text-right px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assigned as $ap)
                        <tr class="border-t">
                            <td class="px-4 py-2">{{ $ap->name }}</td>
                            <td class="px-4 py-2">{{ number_format($ap->pivot->price, 2) }}</td>
                            <td class="px-4 py-2">{{ number_format($ap->pivot->cost_price, 2) }}</td>
                            <td class="px-4 py-2">{{ $ap->pivot->stock_quantity }}</td>
                            <td class="px-4 py-2">{{ $ap->pivot->reorder_level }}</td>
                            <td class="px-4 py-2 text-right space-x-2">
                                <button type="button" wire:click="startUpdate({{ $ap->id }})" 
                                        class="px-3 py-1 border rounded-lg {{ $updating_product_id == $ap->id ? 'bg-blue-100 border-blue-300' : '' }}">
                                    {{ $updating_product_id == $ap->id ? 'Updating...' : 'Update' }}
                                </button>
                                <button type="button" wire:click="detach({{ $ap->id }})" class="px-3 py-1 border rounded-lg text-red-600">Remove</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                @if($selectedBranch)
                                    No products assigned to this branch.
                                @else
                                    Select a branch to view its product assignments.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endif
    </div>
    <x-notifications />
</div>


