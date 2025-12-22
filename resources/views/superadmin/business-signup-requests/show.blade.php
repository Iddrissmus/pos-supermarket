@extends('layouts.app')

@section('title', 'Business Signup Request Details')

@section('content')
<div class="p-6 max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-800 mb-1">Business Signup Request</h1>
        <p class="text-sm text-gray-600">Review details and approve or reject this business onboarding request.</p>
    </div>

    @if ($errors->has('general'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">
            {{ $errors->first('general') }}
        </div>
    @endif

    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Business & Owner Details</h2>

        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-sm">
            <div>
                <dt class="font-medium text-gray-700">Business Name</dt>
                <dd class="text-gray-900">{{ $request->business_name }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-700">Status</dt>
                <dd class="text-gray-900">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        @if($request->status === 'pending') bg-yellow-100 text-yellow-800
                        @elseif($request->status === 'approved') bg-green-100 text-green-800
                        @else bg-red-100 text-red-800 @endif">
                        {{ ucfirst($request->status) }}
                    </span>
                </dd>
            </div>
            <div>
                <dt class="font-medium text-gray-700">Owner / Contact Name</dt>
                <dd class="text-gray-900">{{ $request->owner_name }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-700">Owner Email</dt>
                <dd class="text-gray-900">{{ $request->owner_email }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-700">Owner Phone</dt>
                <dd class="text-gray-900">{{ $request->owner_phone }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-700">Submitted At</dt>
                <dd class="text-gray-900">{{ $request->created_at->format('Y-m-d H:i') }}</dd>
            </div>
            @if($request->logo)
                <div class="md:col-span-2">
                    <dt class="font-medium text-gray-700 mb-1">Logo</dt>
                    <dd>
                        <img src="{{ asset('storage/'.$request->logo) }}" alt="Business Logo" class="h-16 rounded border border-gray-200 bg-white">
                    </dd>
                </div>
            @endif
        </dl>
    </div>

    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Main Branch Details</h2>
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-sm">
            <div>
                <dt class="font-medium text-gray-700">Branch Name</dt>
                <dd class="text-gray-900">{{ $request->branch_name }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-700">Region</dt>
                <dd class="text-gray-900">{{ $request->region }}</dd>
            </div>
            <div class="md:col-span-2">
                <dt class="font-medium text-gray-700">Address / Location</dt>
                <dd class="text-gray-900 whitespace-pre-line">{{ $request->address }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-700">Branch Contact</dt>
                <dd class="text-gray-900">{{ $request->branch_contact ?? '—' }}</dd>
            </div>
            <div>
                <dt class="font-medium text-gray-700">Coordinates</dt>
                <dd class="text-gray-900">
                    @if($request->latitude && $request->longitude)
                        {{ $request->latitude }}, {{ $request->longitude }}
                    @else
                        Not provided
                    @endif
                </dd>
            </div>
        </dl>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Approval</h2>

        <form method="POST" action="{{ route('superadmin.business-signup-requests.approve', $request) }}" class="space-y-4">
            @csrf
            <label for="approval_note" class="block text-sm font-medium text-gray-700 mb-1">
                Approval / Rejection Note (optional)
            </label>
            <textarea id="approval_note" name="approval_note" rows="3"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('approval_note') border-red-500 @enderror">{{ old('approval_note', $request->approval_note) }}</textarea>
            @error('approval_note')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror

            <div class="flex items-center justify-between mt-4">
                <a href="{{ route('superadmin.business-signup-requests.index') }}" class="text-sm text-gray-600 hover:text-gray-800">
                    ← Back to list
                </a>

                @if($request->status === 'pending')
                    <div class="space-x-2">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1">
                            Approve & Create Business
                        </button>
        </form>

        <form method="POST" action="{{ route('superadmin.business-signup-requests.reject', $request) }}" class="inline">
            @csrf
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1">
                            Reject Request
                        </button>
        </form>
                @else
                    <p class="text-sm text-gray-500">
                        This request has already been {{ $request->status }}.
                    </p>
                @endif
            </div>
    </div>
</div>
@endsection





