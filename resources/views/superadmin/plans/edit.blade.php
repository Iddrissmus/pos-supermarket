@extends('layouts.app')

@section('title', 'Edit Plan')

@section('content')
<div class="max-w-3xl mx-auto px-6 py-6">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Plan: {{ $plan->name }}</h1>
            <p class="text-sm text-gray-500 mt-1">Update plan configuration</p>
        </div>
        <a href="{{ route('superadmin.plans.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Back to Plans</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <form action="{{ route('superadmin.plans.update', $plan) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Plan Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $plan->name) }}" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" required>
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <!-- Price -->
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price (GHS)</label>
                        <input type="number" step="0.01" name="price" id="price" value="{{ old('price', $plan->price) }}" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" required>
                        @error('price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Max Branches -->
                    <div>
                        <label for="max_branches" class="block text-sm font-medium text-gray-700 mb-1">Max Branches</label>
                        <input type="number" name="max_branches" id="max_branches" value="{{ old('max_branches', $plan->max_branches) }}" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" required>
                        @error('max_branches') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Duration -->
                    <div>
                        <label for="duration_days" class="block text-sm font-medium text-gray-700 mb-1">Duration (Days)</label>
                        <input type="number" name="duration_days" id="duration_days" value="{{ old('duration_days', $plan->duration_days) }}" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" required min="1">
                        @error('duration_days') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        <p class="text-xs text-gray-500 mt-1">e.g., 30 for Monthly, 365 for Yearly</p>
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Short Description</label>
                    <input type="text" name="description" id="description" value="{{ old('description', $plan->description) }}" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm">
                    @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Features (Checkboxes) -->
                <div class="col-span-1 md:col-span-2">
                    <h3 class="block text-sm font-medium text-gray-700 mb-3">Plan Features</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 p-6 rounded-xl border border-gray-200">
                        @foreach($featureGroups as $groupKey => $group)
                        <div class="bg-white rounded-lg p-4 border border-gray-100 shadow-sm relative overflow-hidden group hover:border-indigo-200 transition-colors">
                            <h4 class="font-bold text-gray-800 mb-3 text-xs uppercase tracking-wider border-b border-gray-100 pb-2 flex items-center">
                                @if($groupKey == 'sales') <i class="fas fa-cash-register text-indigo-400 mr-2"></i>
                                @elseif($groupKey == 'inventory') <i class="fas fa-boxes text-blue-400 mr-2"></i>
                                @elseif($groupKey == 'reporting') <i class="fas fa-chart-line text-green-400 mr-2"></i>
                                @elseif($groupKey == 'multi_branch') <i class="fas fa-network-wired text-purple-400 mr-2"></i>
                                @elseif($groupKey == 'users') <i class="fas fa-users text-orange-400 mr-2"></i>
                                @else <i class="fas fa-star text-yellow-400 mr-2"></i>
                                @endif
                                {{ $group['name'] }}
                                <div class="ml-auto flex items-center">
                                    <input type="checkbox" onchange="toggleGroup('{{ $groupKey }}', this.checked)" 
                                        class="h-3 w-3 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 cursor-pointer"
                                        title="Select All">
                                    <span class="ml-1 text-[10px] text-gray-400 font-normal normal-case tracking-normal">All</span>
                                </div>
                            </h4>
                            <div class="space-y-2">
                                @foreach($group['features'] as $key => $label)
                                <label class="flex items-start cursor-pointer group/item">
                                    <div class="flex items-center h-5">
                                        <input id="feature-{{ $key }}" name="features[]" type="checkbox" value="{{ $label }}" 
                                            {{ in_array($label, $plan->features ?? []) ? 'checked' : '' }}
                                            class="feature-checkbox group-{{ $groupKey }} focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded transition duration-150 ease-in-out">
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <span class="font-medium text-gray-600 group-hover/item:text-indigo-700 transition-colors">{{ $label }}</span>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Select the features included in this plan.</p>
                </div>

                <!-- Active Status -->
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $plan->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                    <label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label>
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 font-medium transition-colors shadow-sm">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleGroup(groupKey, isChecked) {
        const checkboxes = document.querySelectorAll(`.group-${groupKey}`);
        checkboxes.forEach(cb => {
            cb.checked = isChecked;
        });
    }
</script>
@endpush
