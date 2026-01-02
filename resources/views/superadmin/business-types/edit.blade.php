@extends('layouts.app')

@section('content')
<div class="p-6 max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('superadmin.business-types.index') }}" class="text-gray-500 hover:text-gray-700 text-sm flex items-center mb-2">
            <i class="fas fa-arrow-left mr-2"></i> Back to Business Types
        </a>
        <h1 class="text-2xl font-bold text-gray-900">{{ isset($businessType) ? 'Edit Business Type' : 'Create Business Type' }}</h1>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form action="{{ isset($businessType) ? route('superadmin.business-types.update', $businessType) : route('superadmin.business-types.store') }}" method="POST">
            @csrf
            @if(isset($businessType))
                @method('PUT')
            @endif

            <div class="space-y-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Type Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $businessType->name ?? '') }}" 
                        class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring focus:ring-purple-200 transition-shadow" required>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" id="description" rows="3" 
                        class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring focus:ring-purple-200 transition-shadow">{{ old('description', $businessType->description ?? '') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div class="flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $businessType->is_active ?? true) ? 'checked' : '' }}
                        class="rounded border-gray-300 text-purple-600 focus:ring-purple-500 h-4 w-4">
                    <label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label>
                </div>
            </div>

            <div class="mt-8 flex justify-end space-x-3">
                <a href="{{ route('superadmin.business-types.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700 shadow-sm transition-colors">
                    {{ isset($businessType) ? 'Update Type' : 'Create Type' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
