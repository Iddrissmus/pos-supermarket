@extends('layouts.app')

@section('title', 'Business Created')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-16">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden text-center p-10">
        
        <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        </div>
        
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Business Created Successfully!</h1>
        <p class="text-gray-500 mb-8 max-w-lg mx-auto">
            The organization <span class="font-semibold text-gray-800">{{ $business->name }}</span> has been registered. 
            However, it is currently in <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Pending Payment</span> status.
        </p>
        
        <div class="bg-gray-50 rounded-xl p-6 mb-8 text-left max-w-lg mx-auto border border-gray-200">
            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-4">Next Steps</h3>
            <ul class="space-y-3">
                <li class="flex items-start">
                    <div class="flex-shrink-0 h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mt-0.5">
                        <span class="text-blue-600 text-xs font-bold">1</span>
                    </div>
                    <p class="ml-3 text-sm text-gray-600">An invoice for the <strong>{{ $plan->name }}</strong> plan has been sent to <strong>{{ $owner->email }}</strong>.</p>
                </li>
                <li class="flex items-start">
                    <div class="flex-shrink-0 h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mt-0.5">
                        <span class="text-blue-600 text-xs font-bold">2</span>
                    </div>
                    <p class="ml-3 text-sm text-gray-600">The owner must complete the payment to activate the business.</p>
                </li>
                <li class="flex items-start">
                    <div class="flex-shrink-0 h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mt-0.5">
                        <span class="text-blue-600 text-xs font-bold">3</span>
                    </div>
                    <p class="ml-3 text-sm text-gray-600">Once paid, the account will be automatically activated.</p>
                </li>
            </ul>
        </div>
        
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('businesses.index') }}" class="w-full sm:w-auto px-6 py-3 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">
                Return to Dashboard
            </a>
        </div>
        
    </div>
</div>
@endsection
