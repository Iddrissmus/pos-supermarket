@extends('layouts.app')

@section('title', 'Create New Business')

@section('content')
<div class="p-6 max-w-2xl mx-auto">
    <div class="bg-white shadow rounded-lg p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Create New Business</h1>
            <p class="text-sm text-gray-600">Add a new business to the system</p>
        </div>

        <form action="{{ route('businesses.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Business Name -->
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Business Name <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       value="{{ old('name') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror"
                       required>
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Business Admin Selection -->
            <div class="mb-4">
                <label for="business_admin_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Business Admin <span class="text-red-500">*</span>
                </label>
                <select id="business_admin_id" 
                        name="business_admin_id" 
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('business_admin_id') border-red-500 @enderror"
                        required>
                    <option value="">Select a Business Admin</option>
                    @foreach($availableAdmins as $admin)
                        <option value="{{ $admin->id }}" {{ old('business_admin_id') == $admin->id ? 'selected' : '' }}>
                            {{ $admin->name }} ({{ $admin->email }})
                            @if($admin->business_id)
                                - Already assigned to: {{ $admin->managedBusiness->name ?? 'Business #' . $admin->business_id }}
                            @endif
                        </option>
                    @endforeach
                </select>
                @error('business_admin_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                @if($availableAdmins->isEmpty())
                    <p class="text-amber-600 text-sm mt-1">
                        <i class="fas fa-exclamation-triangle"></i> No business admins found. Create a business admin user first.
                    </p>
                @else
                    <p class="text-blue-600 text-xs mt-1">
                        <i class="fas fa-info-circle"></i> Note: Assigning a business admin who is already assigned to another business will reassign them to this new business.
                    </p>
                @endif
            </div>

            <!-- Logo Upload -->
            <div class="mb-6">
                <label for="logo" class="block text-sm font-medium text-gray-700 mb-2">
                    Business Logo (Optional)
                </label>
                <input type="file" 
                       id="logo" 
                       name="logo" 
                       accept="image/*"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('logo') border-red-500 @enderror">
                <p class="text-gray-500 text-xs mt-1">Maximum file size: 2MB. Supported formats: JPG, PNG, GIF</p>
                @error('logo')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-4 border-t">
                <a href="{{ route('businesses.index') }}" 
                   class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-arrow-left mr-2"></i>Cancel
                </a>
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg inline-flex items-center"
                        {{ $availableAdmins->isEmpty() ? 'disabled' : '' }}>
                    <i class="fas fa-save mr-2"></i>Create Business
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
