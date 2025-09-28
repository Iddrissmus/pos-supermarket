<div class="p-6 space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold">Products</h2>
        <div class="flex gap-2">
            <input type="text" wire:model.debounce.300ms="search" placeholder="Search" class="border border-gray-300 rounded-lg px-3 py-2" />
            <button type="button" wire:click="resetForm" class="px-3 py-2 border rounded-lg">Reset</button>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4">
        <form wire:submit.prevent="save" class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" wire:model.defer="name" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                @error('name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Image</label>
                <input type="file" wire:model.defer="image" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                @error('image')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea wire:model.defer="description" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                @error('description')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="md:col-span-2 flex items-center gap-2">
                <button type="submit" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg">
                    {{ $editingId ? 'Update' : 'Create' }} Product
                </button>
                @if($editingId)
                    <button type="button" wire:click="resetForm" class="px-3 py-2 border rounded-lg">Cancel</button>
                @endif
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Product Name
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Description
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Unit Price
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        In Stock
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Cost Price
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($products as $product)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $product->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ Str::limit($product->description, 80) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{$product->price}}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{$product->stock}}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{$product->cost_price}}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-right space-x-2">
                            <button type="button" wire:click="edit({{ $product->id }})" class="px-3 py-1 border rounded-lg">Edit</button>
                            <button type="button" wire:click="delete({{ $product->id }})" class="px-3 py-1 border rounded-lg text-red-600">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-6 text-center text-gray-500">No products found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-4">{{ $products->links() }}</div>
    </div>
    <x-notifications />
</div>


