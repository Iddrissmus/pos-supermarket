@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-user-plus mr-3 text-blue-600"></i>Add New Customer
                    </h1>
                    <a href="{{ route('customers.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-list mr-2"></i>View All Customers
                    </a>
                </div>
            </div>

            <form action="{{ route('customers.store') }}" method="POST" class="p-6">
                @csrf
                
                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                        <h3 class="text-red-800 font-medium mb-2">Please correct the following errors:</h3>
                        <ul class="text-red-700 list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Basic Information -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Basic Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Customer Type *</label>
                            <select name="customer_type" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="individual" {{ old('customer_type') === 'individual' ? 'selected' : '' }}>Individual</option>
                                <option value="business" {{ old('customer_type') === 'business' ? 'selected' : '' }}>Business</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                            <input type="text" name="name" value="{{ old('name') }}" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Enter full name">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Company/Business Name</label>
                            <input type="text" name="company" value="{{ old('company') }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Enter company or business name">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Enter email address">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                            <input type="tel" name="phone" value="{{ old('phone') }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Enter phone number">
                        </div>
                    </div>
                </div>

                <!-- Address Information -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Address Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Street Address</label>
                            <input type="text" name="address" value="{{ old('address') }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Enter street address">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                            <input type="text" name="city" value="{{ old('city') }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Enter city">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">State/Province</label>
                            <input type="text" name="state" value="{{ old('state') }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Enter state or province">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                            <input type="text" name="country" value="{{ old('country', 'Ghana') }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Enter country">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Postal Code</label>
                            <input type="text" name="postal_code" value="{{ old('postal_code') }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Enter postal code">
                        </div>
                    </div>
                </div>

                <!-- Payment & Credit Information -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Terms *</label>
                            <select name="payment_terms" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="immediate" {{ old('payment_terms') === 'immediate' ? 'selected' : '' }}>Immediate Payment</option>
                                <option value="net_15" {{ old('payment_terms') === 'net_15' ? 'selected' : '' }}>Net 15 Days</option>
                                <option value="net_30" {{ old('payment_terms') === 'net_30' ? 'selected' : '' }}>Net 30 Days</option>
                                <option value="net_60" {{ old('payment_terms') === 'net_60' ? 'selected' : '' }}>Net 60 Days</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Additional Notes -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Additional Information</h3>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                        <textarea name="notes" rows="4" 
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Enter any additional notes about this customer...">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-4 pt-6 border-t">
                    <a href="{{ route('customers.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                        <i class="fas fa-save mr-2"></i>Create Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection