@extends('layouts.app')

@section('title', 'Pay Subscription | ' . $business->name)

@section('content')
<div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        @if($business->logo)
            <img class="mx-auto h-20 w-auto rounded-md" src="{{ asset('storage/' . $business->logo) }}" alt="{{ $business->name }}">
        @else
            <div class="mx-auto h-20 w-20 bg-indigo-100 rounded-full flex items-center justify-center">
                <span class="text-2xl font-bold text-indigo-600">{{ substr($business->name, 0, 2) }}</span>
            </div>
        @endif
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
            Activate Your Account
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
            {{ $business->name }}
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            
            @if(session('error'))
                <div class="mb-4 bg-red-50 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="border-b border-gray-200 pb-6 mb-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Subscription Details</h3>
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">Plan</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $plan->name }}</dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">Duration</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $plan->duration_days }} Days</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-sm font-medium text-gray-500">Description</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $plan->description }}</dd>
                    </div>
                </dl>
            </div>

            <div class="flex justify-between items-center py-4 border-t border-b border-gray-200 mb-6 bg-gray-50 -mx-10 px-10">
                <span class="text-lg font-medium text-gray-900">Total Due</span>
                <span class="text-2xl font-bold text-indigo-600">GHS {{ number_format($plan->price, 2) }}</span>
            </div>

            <form action="{{ route('subscription.payment.process', $business->uuid ?: $business->id) }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                    <i class="fas fa-lock mr-2"></i> Pay Securely with Paystack
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-xs text-gray-500">
                    Your payment information is processed securely. By proceeding, you agree to our Terms of Service.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
