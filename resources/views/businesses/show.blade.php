@extends('layouts.app')

@section('title', $business->name . ' - Business Details')

@section('content')
<div class="p-6 space-y-6">
    <!-- Header -->
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center">
                @if($business->logo)
                    <img src="{{ asset('storage/' . $business->logo) }}" 
                         alt="{{ $business->name }}" 
                         class="h-16 w-16 rounded-full mr-4">
                @else
                    <div class="h-16 w-16 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-2xl mr-4">
                        {{ strtoupper(substr($business->name, 0, 2)) }}
                    </div>
                @endif
                <div>
                    <h1 class="text-2xl font-semibold text-gray-800">{{ $business->name }}</h1>
                    <p class="text-sm text-gray-600">Business ID: #{{ $business->id }}</p>
                </div>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('businesses.edit', $business->id) }}" 
                   class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                <a href="{{ route('businesses.index') }}" 
                   class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg inline-flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>Back
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                <p class="text-green-800"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</p>
            </div>
        @endif
    </div>

    <!-- Business Information -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Basic Info -->
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-info-circle text-blue-600 mr-2"></i>Basic Information
            </h2>
            <dl class="space-y-3">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Business Name</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $business->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Business Admin</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($business->businessAdmin)
                            <div class="flex items-center">
                                <i class="fas fa-user-tie text-blue-600 mr-2"></i>
                                <span>{{ $business->businessAdmin->name }}</span>
                            </div>
                            <div class="text-xs text-gray-500 ml-6">{{ $business->businessAdmin->email }}</div>
                        @else
                            <span class="text-gray-400">No admin assigned</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Created</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $business->created_at->format('F d, Y \a\t g:i A') }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $business->updated_at->format('F d, Y \a\t g:i A') }}</dd>
                </div>
            </dl>
        </div>

        <!-- Statistics -->
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-chart-bar text-green-600 mr-2"></i>Statistics
            </h2>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-blue-50 rounded-lg p-4">
                    <div class="text-sm text-blue-600 font-medium">Total Branches</div>
                    <div class="text-2xl font-bold text-blue-700 mt-1">{{ $business->branches->count() }}</div>
                </div>
                <div class="bg-green-50 rounded-lg p-4">
                    <div class="text-sm text-green-600 font-medium">Active Branches</div>
                    <div class="text-2xl font-bold text-green-700 mt-1">{{ $business->branches->where('status', 'active')->count() }}</div>
                </div>
                <div class="bg-purple-50 rounded-lg p-4">
                    <div class="text-sm text-purple-600 font-medium">Total Products</div>
                    <div class="text-2xl font-bold text-purple-700 mt-1">
                        {{ \App\Models\Product::where('business_id', $business->id)->count() }}
                    </div>
                </div>
                <div class="bg-orange-50 rounded-lg p-4">
                    <div class="text-sm text-orange-600 font-medium">Total Staff</div>
                    <div class="text-2xl font-bold text-orange-700 mt-1">
                        {{ \App\Models\User::where('business_id', $business->id)->count() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Branches List -->
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                <i class="fas fa-store text-indigo-600 mr-2"></i>Branches
            </h2>
            @if(Auth::user()->role === 'superadmin')
                <button type="button" 
                        onclick="showAddBranchModal()"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg inline-flex items-center text-sm">
                    <i class="fas fa-plus mr-2"></i>Add Branch
                </button>
            @endif
        </div>

        @if($business->branches->isEmpty())
            <div class="text-center py-8 text-gray-500">
                <i class="fas fa-store text-4xl text-gray-300 mb-3"></i>
                <p>No branches found for this business</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Location</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Manager</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($business->branches as $branch)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $branch->name }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm text-gray-600">{{ $branch->location ?? 'N/A' }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm text-gray-600">
                                        @if($branch->manager)
                                            {{ $branch->manager->name }}
                                        @else
                                            <span class="text-gray-400">No manager</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $branch->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ ucfirst($branch->status ?? 'active') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                    {{ $branch->created_at->format('M d, Y') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Add Branch Modal -->
    <div id="addBranchModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Add New Branch</h3>
            </div>
            <form id="addBranchForm" action="{{ route('branches.store') }}" method="POST">
                @csrf
                <input type="hidden" name="business_id" value="{{ $business->id }}">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Branch Name *</label>
                    <input type="text" name="name" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                    <textarea name="address" rows="2"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Contact</label>
                    <input type="text" name="contact"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Region</label>
                    <select name="region" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="">Select Region</option>
                        <option value="Greater Accra">Greater Accra</option>
                        <option value="Ashanti">Ashanti</option>
                        <option value="Western">Western</option>
                        <option value="Eastern">Eastern</option>
                        <option value="Central">Central</option>
                        <option value="Northern">Northern</option>
                        <option value="Upper East">Upper East</option>
                        <option value="Upper West">Upper West</option>
                        <option value="Volta">Volta</option>
                        <option value="Brong-Ahafo">Brong-Ahafo</option>
                        <option value="Western North">Western North</option>
                        <option value="Bono East">Bono East</option>
                        <option value="Ahafo">Ahafo</option>
                        <option value="Savannah">Savannah</option>
                        <option value="North East">North East</option>
                        <option value="Oti">Oti</option>
                    </select>
                </div>
                
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="hideAddBranchModal()"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg">
                        Cancel
                    </button>
                    <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                        Add Branch
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showAddBranchModal() {
    document.getElementById('addBranchModal').classList.remove('hidden');
}

function hideAddBranchModal() {
    document.getElementById('addBranchModal').classList.add('hidden');
    document.getElementById('addBranchForm').reset();
}
</script>
@endsection
