@extends('layouts.app')

@section('title', $business->name . ' - Business Details')

@section('content')
<div class="p-6 space-y-6">
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 rounded-r-lg p-4 shadow-md">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                <p class="text-green-800 font-medium">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <!-- Header Card with Logo and Actions -->
    <div class="bg-gradient-to-br from-white to-gray-50 shadow-xl rounded-2xl p-8 border border-gray-100">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-6">
                @if($business->logo)
                    <img src="{{ asset('storage/' . $business->logo) }}" 
                         alt="{{ $business->name }}" 
                         class="h-24 w-24 rounded-2xl shadow-lg border-4 border-white object-cover">
                @else
                    <div class="h-24 w-24 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center text-white font-bold text-3xl shadow-lg border-4 border-white">
                        {{ strtoupper(substr($business->name, 0, 2)) }}
                    </div>
                @endif
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $business->name }}</h1>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600 bg-gray-100 px-3 py-1 rounded-full font-medium">
                            <i class="fas fa-hashtag text-gray-400 mr-1"></i>ID: {{ $business->id }}
                        </span>
                        @if($business->status === 'active')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800 border border-green-300">
                                <i class="fas fa-check-circle mr-1"></i>ACTIVE
                            </span>
                        @elseif($business->status === 'inactive')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800 border border-yellow-300">
                                <i class="fas fa-pause-circle mr-1"></i>INACTIVE
                            </span>
                        @elseif($business->status === 'blocked')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-300">
                                <i class="fas fa-ban mr-1"></i>BLOCKED
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('businesses.edit', $business->id) }}" 
                   class="bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white px-6 py-3 rounded-xl inline-flex items-center shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 font-semibold">
                    <i class="fas fa-edit mr-2"></i>Edit Business
                </a>
                <a href="{{ route('businesses.index') }}" 
                   class="bg-white hover:bg-gray-50 text-gray-700 px-6 py-3 rounded-xl inline-flex items-center shadow-md hover:shadow-lg border-2 border-gray-200 transform hover:-translate-y-0.5 transition-all duration-200 font-semibold">
                    <i class="fas fa-arrow-left mr-2"></i>Back
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl p-6 text-white shadow-xl transform hover:scale-105 transition-transform duration-200">
            <div class="flex items-center justify-between mb-2">
                <div class="text-blue-100 text-sm font-semibold uppercase tracking-wide">Total Branches</div>
                <i class="fas fa-store text-3xl text-blue-200"></i>
            </div>
            <div class="text-4xl font-bold">{{ $business->branches->count() }}</div>
            <div class="text-blue-100 text-xs mt-2">All locations</div>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl p-6 text-white shadow-xl transform hover:scale-105 transition-transform duration-200">
            <div class="flex items-center justify-between mb-2">
                <div class="text-green-100 text-sm font-semibold uppercase tracking-wide">Active Branches</div>
                <i class="fas fa-check-circle text-3xl text-green-200"></i>
            </div>
            <div class="text-4xl font-bold">{{ $business->branches->where('status', 'active')->count() }}</div>
            <div class="text-green-100 text-xs mt-2">Currently operating</div>
        </div>

        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl p-6 text-white shadow-xl transform hover:scale-105 transition-transform duration-200">
            <div class="flex items-center justify-between mb-2">
                <div class="text-purple-100 text-sm font-semibold uppercase tracking-wide">Total Products</div>
                <i class="fas fa-box text-3xl text-purple-200"></i>
            </div>
            <div class="text-4xl font-bold">{{ \App\Models\Product::where('business_id', $business->id)->count() }}</div>
            <div class="text-purple-100 text-xs mt-2">In catalog</div>
        </div>
        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl p-6 text-white shadow-xl transform hover:scale-105 transition-transform duration-200">
            <div class="flex items-center justify-between mb-2">
                <div class="text-orange-100 text-sm font-semibold uppercase tracking-wide">Total Staff</div>
                <i class="fas fa-users text-3xl text-orange-200"></i>
            </div>
            <div class="text-4xl font-bold">{{ \App\Models\User::where('business_id', $business->id)->count() }}</div>
            <div class="text-orange-100 text-xs mt-2">Employees</div>
        </div>
    </div>

    <!-- Business Information & Admin Details -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Business Information Card -->
        <div class="bg-white shadow-xl rounded-2xl p-6 border border-gray-100">
            <div class="flex items-center mb-6 pb-4 border-b border-gray-200">
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-3 mr-4">
                    <i class="fas fa-info-circle text-white text-2xl"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-900">Business Information</h2>
            </div>
            <dl class="space-y-4">
                <div class="flex items-start p-3 bg-gray-50 rounded-lg">
                    <div class="flex-1">
                        <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Business Name</dt>
                        <dd class="text-base font-semibold text-gray-900">{{ $business->name }}</dd>
                    </div>
                    <i class="fas fa-building text-gray-400 mt-1"></i>
                </div>
                <div class="flex items-start p-3 bg-gray-50 rounded-lg">
                    <div class="flex-1">
                        <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Business ID</dt>
                        <dd class="text-base font-semibold text-gray-900">#{{ $business->id }}</dd>
                    </div>
                    <i class="fas fa-hashtag text-gray-400 mt-1"></i>
                </div>
                <div class="flex items-start p-3 bg-gray-50 rounded-lg">
                    <div class="flex-1">
                        <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Created Date</dt>
                        <dd class="text-base font-semibold text-gray-900">{{ $business->created_at->format('M d, Y') }}</dd>
                        <dd class="text-xs text-gray-500">{{ $business->created_at->format('g:i A') }}</dd>
                    </div>
                    <i class="fas fa-calendar-plus text-gray-400 mt-1"></i>
                </div>
                <div class="flex items-start p-3 bg-gray-50 rounded-lg">
                    <div class="flex-1">
                        <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Last Updated</dt>
                        <dd class="text-base font-semibold text-gray-900">{{ $business->updated_at->format('M d, Y') }}</dd>
                        <dd class="text-xs text-gray-500">{{ $business->updated_at->format('g:i A') }}</dd>
                    </div>
                    <i class="fas fa-clock text-gray-400 mt-1"></i>
                </div>
            </dl>
        </div>

        <!-- Business Admin Card -->
        <div class="bg-white shadow-xl rounded-2xl p-6 border border-gray-100">
            <div class="flex items-center mb-6 pb-4 border-b border-gray-200">
                <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl p-3 mr-4">
                    <i class="fas fa-user-tie text-white text-2xl"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-900">Business Administrator</h2>
            </div>
            
            @if($business->primaryBusinessAdmin)
                <div class="bg-gradient-to-br from-indigo-50 to-blue-50 rounded-xl p-6 border-2 border-indigo-100">
                    <div class="flex items-center mb-4">
                        <div class="h-16 w-16 rounded-full bg-gradient-to-br from-indigo-600 to-indigo-700 flex items-center justify-center text-white font-bold text-xl shadow-lg mr-4">
                            {{ strtoupper(substr($business->primaryBusinessAdmin->name, 0, 2)) }}
                        </div>
                        <div>
                            <div class="text-lg font-bold text-gray-900">{{ $business->primaryBusinessAdmin->name }}</div>
                            <div class="text-sm text-indigo-600 font-semibold">Business Administrator</div>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center text-sm bg-white bg-opacity-60 rounded-lg p-3">
                            <i class="fas fa-envelope text-indigo-600 mr-3"></i>
                            <span class="text-gray-700">{{ $business->primaryBusinessAdmin->email }}</span>
                        </div>
                        @if($business->primaryBusinessAdmin->phone)
                            <div class="flex items-center text-sm bg-white bg-opacity-60 rounded-lg p-3">
                                <i class="fas fa-phone text-indigo-600 mr-3"></i>
                                <span class="text-gray-700">{{ $business->primaryBusinessAdmin->phone }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="text-center py-12">
                    <div class="bg-gray-100 rounded-full h-20 w-20 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-user-slash text-gray-400 text-3xl"></i>
                    </div>
                    <p class="text-gray-500 font-medium mb-2">No administrator assigned</p>
                    <p class="text-gray-400 text-sm">Assign an admin in the edit page</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Branches List -->
    <div class="bg-white shadow-xl rounded-2xl p-6 border border-gray-100">
        <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200">
            <div class="flex items-center">
                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-3 mr-4">
                    <i class="fas fa-store text-white text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Branch Locations</h2>
                    <p class="text-sm text-gray-500 mt-1">{{ $business->branches->count() }} {{ Str::plural('branch', $business->branches->count()) }} total</p>
                </div>
            </div>
            @if(Auth::user()->role === 'superadmin')
                <button type="button" 
                        onclick="showAddBranchModal()"
                        class="bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white px-5 py-3 rounded-xl inline-flex items-center text-sm font-semibold shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                    <i class="fas fa-plus mr-2"></i>Add Branch
                </button>
            @endif
        </div>

        @if($business->branches->isEmpty())
            <div class="text-center py-16">
                <div class="bg-gray-100 rounded-full h-24 w-24 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-store text-gray-300 text-4xl"></i>
                </div>
                <p class="text-gray-500 font-semibold text-lg mb-2">No branches found</p>
                <p class="text-gray-400">Add a branch to get started</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($business->branches as $branch)
                    <div class="bg-gradient-to-br from-gray-50 to-white border-2 border-gray-200 rounded-xl p-5 hover:shadow-lg hover:border-indigo-300 transition-all duration-200 transform hover:-translate-y-1">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-gray-900 mb-1">{{ $branch->name }}</h3>
                                @if($branch->location)
                                    <p class="text-xs text-gray-500 flex items-center">
                                        <i class="fas fa-map-marker-alt mr-1 text-gray-400"></i>{{ Str::limit($branch->location, 30) }}
                                    </p>
                                @endif
                            </div>
                            @if($branch->status === 'active')
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                    <i class="fas fa-circle text-green-500 text-xs mr-1"></i>Active
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                    <i class="fas fa-circle text-red-500 text-xs mr-1"></i>Inactive
                                </span>
                            @endif
                        </div>
                        
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-user-circle w-5 text-gray-400"></i>
                                @if($branch->manager)
                                    <span class="ml-2">{{ $branch->manager->name }}</span>
                                @else
                                    <span class="ml-2 text-gray-400 italic">No manager</span>
                                @endif
                            </div>
                            <div class="flex items-center text-gray-600">
                                <i class="fas fa-calendar w-5 text-gray-400"></i>
                                <span class="ml-2">{{ $branch->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
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
