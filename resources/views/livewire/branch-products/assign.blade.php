<div class="space-y-6 p-6">
    <div class="bg-white rounded-lg shadow p-4 space-y-4">
        <div class="grid md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                <select wire:model="branch_id" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                    <option value="">Select branch</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
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
            <button type="button" wire:click="assign" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Assign to Branch</button>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-x-auto">
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
                            <button type="button" wire:click="updatePivot({{ $ap->id }})" class="px-3 py-1 border rounded-lg">Update</button>
                            <button type="button" wire:click="detach({{ $ap->id }})" class="px-3 py-1 border rounded-lg text-red-600">Remove</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">No products assigned to this branch.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-notifications />
</div>


