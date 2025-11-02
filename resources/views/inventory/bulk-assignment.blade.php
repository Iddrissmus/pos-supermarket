@extends('layouts.app')

@section('title', 'Bulk Product Assignment')

@section('content')
<div class="p-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Bulk Product Assignment via Excel</h1>
                <p class="text-sm text-gray-600 mt-1">Upload an Excel file to assign multiple products to branches quickly</p>
            </div>
            <a href="{{ route('layouts.product') }}" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-times text-xl"></i>
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-3"></i>
                    <p class="text-green-800">{{ session('success') }}</p>
                </div>
                @if(session('details'))
                    <div class="mt-2 text-sm text-green-700">
                        <p><strong>Assigned:</strong> {{ session('details')['success'] }} products</p>
                        @if(session('details')['skipped'] > 0)
                            <p class="text-orange-700"><strong>Skipped:</strong> {{ session('details')['skipped'] }} rows</p>
                        @endif
                    </div>
                @endif
            </div>
        @endif

        @if($errors->any() || session('import_errors'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-circle text-red-500 mr-3 mt-0.5"></i>
                    <div class="flex-1">
                        <p class="text-red-800 font-medium">Import Errors:</p>
                        <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                            @if(session('import_errors'))
                                @foreach(session('import_errors') as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Instructions -->
        <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
            <div class="flex items-start">
                <i class="fas fa-info-circle text-blue-500 text-lg mt-0.5 mr-3"></i>
                <div class="flex-1">
                    <h3 class="text-sm font-medium text-blue-800 mb-2">How to use Bulk Assignment</h3>
                    <ol class="text-sm text-blue-700 list-decimal list-inside space-y-1">
                        <li>Download the Excel template below</li>
                        <li>Fill in product names/barcodes and branch names</li>
                        <li>Enter quantities (boxes, units per box) and prices</li>
                        <li>Upload the completed file</li>
                        <li>Review results and check for any errors</li>
                    </ol>
                    <p class="mt-2 text-xs text-blue-600">
                        <strong>Note:</strong> 
                        @if($userRole === 'superadmin')
                            As a superadmin, you can assign to any branch.
                        @else
                            You can only assign products to your branch: <strong>{{ $userBranchName }}</strong>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Download Template -->
        <div class="mb-6">
            <a href="{{ route('inventory.assignment-template') }}" 
               class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                <i class="fas fa-download mr-2"></i>
                Download Excel Template
            </a>
            <p class="text-xs text-gray-500 mt-2">
                The template includes sample data and shows the required format
            </p>
        </div>

        <!-- Upload Form -->
        <form action="{{ route('inventory.bulk-assignment-upload') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- File Upload -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Upload Excel File *
                </label>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 transition-colors">
                    <input 
                        type="file" 
                        name="file" 
                        id="fileInput"
                        accept=".xlsx,.xls,.csv"
                        required
                        class="hidden"
                    >
                    <label for="fileInput" class="cursor-pointer">
                        <div class="space-y-2">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400"></i>
                            <p class="text-sm text-gray-600">
                                <span class="text-blue-600 font-medium">Click to upload</span> or drag and drop
                            </p>
                            <p class="text-xs text-gray-500">
                                Excel files only (XLSX, XLS, CSV) - Max 5MB
                            </p>
                            <p id="fileName" class="text-sm font-medium text-gray-700 mt-2"></p>
                        </div>
                    </label>
                </div>
                @error('file')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Expected Format -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-800 mb-3">Expected Excel Format:</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs">
                        <thead class="bg-blue-100">
                            <tr>
                                <th class="px-3 py-2 text-left font-medium text-gray-700">Product Name or Barcode *</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-700">Branch Name *</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-700">Quantity of Boxes *</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-700">Units per Box *</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-700">Selling Price</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-700">Cost Price</th>
                                <th class="px-3 py-2 text-left font-medium text-gray-700">Reorder Level</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            <tr class="border-b">
                                <td class="px-3 py-2 text-gray-600">Royal Rice 25kg</td>
                                <td class="px-3 py-2 text-gray-600">Main Branch</td>
                                <td class="px-3 py-2 text-gray-600">10</td>
                                <td class="px-3 py-2 text-gray-600">1</td>
                                <td class="px-3 py-2 text-gray-600">150.00</td>
                                <td class="px-3 py-2 text-gray-600">120.00</td>
                                <td class="px-3 py-2 text-gray-600">5</td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 text-gray-600">PRD-001-20251102</td>
                                <td class="px-3 py-2 text-gray-600">East Branch</td>
                                <td class="px-3 py-2 text-gray-600">20</td>
                                <td class="px-3 py-2 text-gray-600">12</td>
                                <td class="px-3 py-2 text-gray-600">25.50</td>
                                <td class="px-3 py-2 text-gray-600">18.00</td>
                                <td class="px-3 py-2 text-gray-600">10</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="text-xs text-gray-500 mt-3">
                    * Required fields. You can use either product name or barcode to identify products.
                </p>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-between pt-4 border-t">
                <a href="{{ route('layouts.product') }}" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Products
                </a>
                <button 
                    type="submit" 
                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors"
                >
                    <i class="fas fa-upload mr-2"></i>
                    Upload and Assign
                </button>
            </div>
        </form>

        <!-- Alternative: Manual Assignment -->
        <div class="mt-8 pt-6 border-t">
            <p class="text-sm text-gray-600 mb-3">
                Need to assign just a few products with more control?
            </p>
            <a href="{{ route('inventory.assign') }}" 
               class="inline-flex items-center px-4 py-2 border-2 border-gray-300 hover:border-gray-400 text-gray-700 rounded-lg font-medium transition-colors">
                <i class="fas fa-edit mr-2"></i>
                Use Manual Assignment Form
            </a>
        </div>
    </div>
</div>

<script>
    // File input handling
    const fileInput = document.getElementById('fileInput');
    const fileName = document.getElementById('fileName');

    fileInput.addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            fileName.textContent = '📄 ' + this.files[0].name;
        }
    });

    // Drag and drop
    const dropZone = document.querySelector('.border-dashed');
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.add('border-blue-500', 'bg-blue-50');
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.remove('border-blue-500', 'bg-blue-50');
        });
    });

    dropZone.addEventListener('drop', function(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        fileInput.files = files;
        if (files.length > 0) {
            fileName.textContent = '📄 ' + files[0].name;
        }
    });
</script>
@endsection
