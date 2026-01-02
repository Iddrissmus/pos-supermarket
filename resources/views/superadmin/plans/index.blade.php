@extends('layouts.app')

@section('title', 'Subscription Plans')

@section('content')
<div class="px-6 py-6 border-b border-gray-200">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Subscription Plans</h1>
            <p class="text-sm text-gray-500 mt-1">Manage pricing and features for business plans</p>
        </div>
    </div>
</div>

<div class="px-6 py-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-xs uppercase text-gray-500 font-semibold">
                        <th class="px-6 py-4">Name</th>
                        <th class="px-6 py-4">Slug</th>
                        <th class="px-6 py-4">Price (GHS)</th>
                        <th class="px-6 py-4">Max Branches</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($plans as $plan)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $plan->name }}</td>
                        <td class="px-6 py-4">
                            <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded text-xs font-mono">{{ $plan->slug }}</span>
                        </td>
                        <td class="px-6 py-4 font-bold text-gray-900">{{ number_format($plan->price, 2) }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $plan->max_branches }}</td>
                        <td class="px-6 py-4">
                            @if($plan->is_active)
                                <span class="bg-green-100 text-green-700 px-2.5 py-0.5 rounded-full text-xs font-medium">Active</span>
                            @else
                                <span class="bg-gray-100 text-gray-600 px-2.5 py-0.5 rounded-full text-xs font-medium">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('superadmin.plans.edit', $plan) }}" class="text-indigo-600 hover:text-indigo-800 font-medium text-sm">Edit</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
