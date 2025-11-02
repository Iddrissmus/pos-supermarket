@extends('layouts.app')

@section('title', 'Bulk Import Products')

@section('content')
<div class="p-6 max-w-4xl mx-auto">
    <!-- Success/Error Messages -->
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    
    @if (session('warning'))
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('warning') }}</span>
        </div>
    @endif
    
    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    @if (session('import_errors'))
        <div class="bg-yellow-50 border border-yellow-400 text-yellow-800 px-4 py-3 rounded relative mb-4" role="alert">
            <p class="font-medium mb-2">Import Errors:</p>
            <ul class="list-disc list-inside text-sm">
                @foreach (session('import_errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Bulk Import Products</h1>
                <p class="text-sm text-gray-600 mt-1">Import multiple products from an Excel file</p>
            </div>
            <a href="{{ route('layouts.product') }}" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-times text-xl"></i>
            </a>
        </div>

        <!-- Instructions -->
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-blue-500 mr-3 mt-1"></i>
                <div>
                    <h3 class="font-medium text-blue-900 mb-2">How to use bulk import:</h3>
                    <ol class="list-decimal list-inside text-sm text-blue-800 space-y-1">
                        <li>Download the Excel template below</li>
                        <li>Fill in your product data (Product Name, Category, Boxes, Units per Box, etc.)</li>
                        <li>Select the destination branch</li>
                        <li>Upload the completed Excel file</li>
                    </ol>
                    <p class="text-sm text-blue-700 mt-3">
                        <strong>Note:</strong> Products with the same name will have their quantities updated. New products will be created automatically.
                    </p>
                </div>
            </div>
        </div>

        <!-- Download Template Button -->
        <div class="mb-6">
            <a href="{{ route('inventory.template') }}" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                <i class="fas fa-download mr-2"></i>
                Download Excel Template
            </a>
            <p class="text-xs text-gray-500 mt-2">The template includes sample data and proper column headers</p>
        </div>

        <!-- Upload Form -->
        <form action="{{ route('inventory.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <!-- Branch Selection -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    {{ count($branches) > 1 ? 'Select Branch *' : 'Branch' }}
                </label>
                @if(count($branches) > 1)
                    {{-- Superadmin can select any branch --}}
                    <select name="branch_id" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Select Branch --</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Products will be added to the selected branch's inventory</p>
                @else
                    {{-- Business admin/manager has only one branch - show it as read-only --}}
                    <input 
                        type="text" 
                        value="{{ $branches->first()->name }}" 
                        readonly 
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 bg-gray-50 text-gray-700 cursor-not-allowed"
                    >
                    <input type="hidden" name="branch_id" value="{{ $branches->first()->id }}">
                    <p class="text-xs text-gray-500 mt-1">Products will be added to your assigned branch</p>
                @endif
            </div>

            <!-- File Upload -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Upload Excel File *
                </label>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                    <input 
                        type="file" 
                        name="file" 
                        accept=".xlsx,.xls,.csv" 
                        required
                        class="hidden"
                        id="fileInput"
                    >
                    <label for="fileInput" class="cursor-pointer">
                        <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-2"></i>
                        <p class="text-sm text-gray-600">Click to browse or drag and drop</p>
                        <p class="text-xs text-gray-500 mt-1">Excel files only (.xlsx, .xls, .csv) - Max 5MB</p>
                    </label>
                    <p id="fileName" class="text-sm text-blue-600 mt-2 hidden"></p>
                </div>
            </div>

            <!-- Expected Format Info -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="font-medium text-gray-800 mb-2">Expected Excel Columns:</h4>
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div>
                        <span class="font-medium text-gray-700">Product Name *</span>
                        <p class="text-xs text-gray-500">Required</p>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">Category</span>
                        <p class="text-xs text-gray-500">Optional (must exist)</p>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">Description</span>
                        <p class="text-xs text-gray-500">Optional</p>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">Quantity of Boxes *</span>
                        <p class="text-xs text-gray-500">Required</p>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">Units per Box *</span>
                        <p class="text-xs text-gray-500">Required</p>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">Selling Price</span>
                        <p class="text-xs text-gray-500">Optional</p>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">Cost Price</span>
                        <p class="text-xs text-gray-500">Optional</p>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">Reorder Level</span>
                        <p class="text-xs text-gray-500">Optional</p>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-end space-x-3 mt-6">
                <a href="{{ route('layouts.product') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                    <i class="fas fa-upload mr-2"></i>
                    Import Products
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Show selected file name
    document.getElementById('fileInput').addEventListener('change', function(e) {
        const fileName = document.getElementById('fileName');
        if (this.files.length > 0) {
            fileName.textContent = 'Selected: ' + this.files[0].name;
            fileName.classList.remove('hidden');
        } else {
            fileName.classList.add('hidden');
        }
    });
</script>
@endsection
