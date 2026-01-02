@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-user-edit mr-3 text-blue-600"></i>Edit Customer - {{ $customer->display_name }}
                    </h1>
                    <div class="flex space-x-3">
                        <a href="{{ route('customers.show', $customer) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Customer
                        </a>
                        <a href="{{ route('customers.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-list mr-2"></i>All Customers
                        </a>
                    </div>
                </div>
            </div>

            <form action="{{ route('customers.update', $customer) }}" method="POST" class="p-6">
                @csrf
                @method('PUT')
                
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Customer Number</label>
                            <input type="text" value="{{ $customer->customer_number }}" disabled
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100 text-gray-500">
                            <p class="mt-1 text-sm text-gray-500">Customer number cannot be changed</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Customer Type *</label>
                            <select name="customer_type" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="individual" {{ old('customer_type', $customer->customer_type) === 'individual' ? 'selected' : '' }}>Individual</option>
                                <option value="business" {{ old('customer_type', $customer->customer_type) === 'business' ? 'selected' : '' }}>Business</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                            <input type="text" name="name" value="{{ old('name', $customer->name) }}" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Enter full name">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Company/Business Name</label>
                            <input type="text" name="company" value="{{ old('company', $customer->company) }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Enter company or business name">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                            <input type="email" name="email" value="{{ old('email', $customer->email) }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Enter email address">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                            <input type="tel" name="phone" value="{{ old('phone', $customer->phone) }}"
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
                            <input type="text" name="address" value="{{ old('address', $customer->address) }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Enter street address">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">City</label>
                            <input type="text" name="city" value="{{ old('city', $customer->city) }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Enter city">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">State/Province</label>
                            <input type="text" name="state" value="{{ old('state', $customer->state) }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Enter state or province">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Country</label>
                            <input type="text" name="country" value="{{ old('country', $customer->country) }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Enter country">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Postal Code</label>
                            <input type="text" name="postal_code" value="{{ old('postal_code', $customer->postal_code) }}"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Enter postal code">
                        </div>
                    </div>
                </div>

                <!-- Payment & Credit Information -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Payment & Credit Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Payment Terms *</label>
                            <select name="payment_terms" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="immediate" {{ old('payment_terms', $customer->payment_terms) === 'immediate' ? 'selected' : '' }}>Immediate</option>
                                <option value="next_15" {{ old('payment_terms', $customer->payment_terms) === 'next_15' ? 'selected' : '' }}>Next 15 Days</option>
                                <option value="next_30" {{ old('payment_terms', $customer->payment_terms) === 'next_30' ? 'selected' : '' }}>Next 30 Days</option>
                                <option value="next_60" {{ old('payment_terms', $customer->payment_terms) === 'next_60' ? 'selected' : '' }}>Next 60 Days</option>
                            </select>
                        </div>

                        <!-- Credit Limit Removed -->

                        @if($customer->outstanding_balance > 0)
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Outstanding Balance</label>
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                <p class="text-yellow-800 text-sm">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>
                                    This customer has an outstanding balance of ₵{{ number_format($customer->outstanding_balance, 2) }}.
                                    This balance is managed through invoices and payments.
                                </p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Status & Notes -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Status & Additional Information</h3>
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $customer->is_active) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2 text-sm font-medium text-gray-700">Customer is active</span>
                            </label>
                            <p class="mt-1 text-sm text-gray-500">Inactive customers cannot make new purchases or receive invoices</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                            <textarea name="notes" rows="4" 
                                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Enter any additional notes about this customer...">{{ old('notes', $customer->notes) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-4 pt-6 border-t">
                    <a href="{{ route('customers.show', $customer) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg">
                        Cancel
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                        <i class="fas fa-save mr-2"></i>Update Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection