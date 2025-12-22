<div>
    <form wire:submit.prevent="save" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Business name</label>
            <input type="text" wire:model.defer="name" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
            @error('name')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Logo</label>
            <input type="file" wire:model="logo" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
            @error('logo')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            Create Business
        </button>

        <x-notifications />
    </form>

    @if(isset($myBusinesses) && $myBusinesses->count())
        <div class="mt-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Your businesses</h3>
            <ul class="list-disc list-inside text-gray-700">
                @foreach($myBusinesses as $biz)
                    <li>{{ $biz->name }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>


