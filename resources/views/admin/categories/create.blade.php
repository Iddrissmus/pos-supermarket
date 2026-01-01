@extends('layouts.app')

@section('title', 'Create Category')

@section('content')
<div class="p-6">
    <div class="bg-white rounded-lg shadow-md p-6 max-w-2xl mx-auto min-h-[500px]">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Create New Category</h1>
                <p class="text-sm text-gray-600 mt-1">Add a new product category to your business</p>
            </div>
            <a href="{{ route('categories.index') }}" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-times text-xl"></i>
            </a>
        </div>

        @if($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
                <ul class="list-disc list-inside text-red-700">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('categories.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Category Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" required 
                    class="w-full border rounded-md p-2 focus:ring-2 focus:ring-blue-500" 
                    placeholder="e.g., Electronics, Food & Groceries">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="description" rows="3" 
                    class="w-full border rounded-md p-2 focus:ring-2 focus:ring-blue-500" 
                    placeholder="Brief description of this category">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Parent Category</label>
                <select name="parent_id" class="tom-select w-full border rounded-md p-2 focus:ring-2 focus:ring-blue-500">
                    <option value="">-- None (Main Category) --</option>
                    @foreach($parentCategories as $parent)
                        <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                            {{ $parent->name }}
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">Leave empty to create a main category, or select a parent to create a subcategory</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Display Order</label>
                <input type="number" name="display_order" value="{{ old('display_order', 999) }}" min="0" 
                    class="w-full border rounded-md p-2 focus:ring-2 focus:ring-blue-500" 
                    placeholder="999">
                <p class="text-xs text-gray-500 mt-1">Lower numbers appear first (default: 999)</p>
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <label for="is_active" class="ml-2 text-sm text-gray-700">Active (visible in product forms)</label>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
                <a href="{{ route('categories.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-save"></i> Create Category
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
