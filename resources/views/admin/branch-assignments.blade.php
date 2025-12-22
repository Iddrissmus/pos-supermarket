@extends('layouts.app')

@section('title', 'Branch Assignments')

@section('content')
<div class="p-6 space-y-6">
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-semibold text-gray-800">Assign Managers & Cashiers to Branches</h1>
            <a href="{{ route('dashboard.business-admin') }}" class="text-sm text-blue-600 hover:underline">Back to dashboard</a>
        </div>
        <p class="text-sm text-gray-500 mb-6">Pick a user, choose a branch, and submit. Selecting “No branch” will unassign them.</p>
        <form method="POST" action="{{ route('admin.branch-assignments.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="user_id" class="block text-sm font-medium text-gray-700 mb-1">User</label>
                    <select id="user_id" name="user_id" class="w-full border rounded px-3 py-2 @error('user_id') border-red-500 @enderror" required>
                        <option value="">Select manager or cashier</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') !== null && (string) old('user_id') === (string) $user->id ? 'selected' : '' }}>
                                {{ $user->name }} — {{ ucfirst($user->role) }}
                                @if($user->branch)
                                    ({{ $user->branch->display_label }})
                                @else
                                    (Unassigned)
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <span class="text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                    <select id="branch_id" name="branch_id" class="w-full border rounded px-3 py-2 @error('branch_id') border-red-500 @enderror">
                        <option value="">No branch (Unassign)</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id') !== null && (string) old('branch_id') === (string) $branch->id ? 'selected' : '' }}>
                                {{ $branch->display_label }}
                            </option>
                        @endforeach
                    </select>
                    @error('branch_id')
                        <span class="text-sm text-red-600">{{ $message }}</span>
                    @enderror
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">Save Assignment</button>
                </div>
            </div>
        </form>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Current Assignments</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-600">Name</th>
                        <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-600">Role</th>
                        <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-600">Branch</th>
                        <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-600">Last Updated</th>
                        <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($users as $user)
                        <tr>
                            <td class="px-4 py-3 text-gray-800">{{ $user->name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ ucfirst($user->role) }}</td>
                            <td class="px-4 py-3 {{ $user->branch ? 'text-gray-700' : 'text-yellow-600' }}">
                                @if($user->branch)
                                    <span class="font-medium">{{ $user->branch->display_label }}</span>
                                @else
                                    Unassigned
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ optional($user->updated_at)->format('M d, Y g:i a') }}</td>
                            <td class="px-4 py-3">
                                @if($user->branch)
                                    <form method="POST" action="{{ route('admin.branch-assignments.store') }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                                        <input type="hidden" name="branch_id" value="">
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-800 text-sm font-medium"
                                                onclick="return confirm('Unassign {{ $user->name }} from their branch?')">
                                            Unassign
                                        </button>
                                    </form>
                                @else
                                    <span class="text-gray-400 text-sm">No action</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-gray-500">No managers or cashiers found yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
