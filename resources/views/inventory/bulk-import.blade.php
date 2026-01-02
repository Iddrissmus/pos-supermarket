@extends('layouts.app')

@section('title', 'Bulk Import Products')

@section('content')
<div class="min-h-screen py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto space-y-6">
        
        <!-- Header -->
        <div class="text-center">
            <h1 class="text-3xl font-extrabold text-gray-900">Bulk Product Import</h1>
            <p class="mt-2 text-lg text-gray-600">Add multiple products to your warehouse inventory from a spreadsheet.</p>
        </div>

        <!-- Alerts -->
        @if (session('success'))
            <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-md shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-md shadow-sm">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-times-circle text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif
        @if(session('import_errors'))
             <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-md shadow-sm">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-red-400"></i>
                    </div>
                    <div class="ml-3 w-full">
                         <p class="text-sm text-red-700 font-bold mb-2">Import Errors:</p>
                         <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                            @foreach (session('import_errors') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Main Card -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
            <!-- Step Guide -->
            <div class="bg-blue-50/50 p-6 border-b border-gray-100">
                <h3 class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-4">Quick Guide</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                     <div class="flex items-start">
                        <div class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-sm">1</div>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-gray-900">Download Template</h4>
                            <p class="text-xs text-gray-500 mt-1">Get the properly formatted Excel file.</p>
                        </div>
                    </div>
                     <div class="flex items-start">
                        <div class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-sm">2</div>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-gray-900">Fill Data</h4>
                            <p class="text-xs text-gray-500 mt-1">Add product names, categories, and stock.</p>
                        </div>
                    </div>
                     <div class="flex items-start">
                        <div class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-sm">3</div>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-gray-900">Upload</h4>
                            <p class="text-xs text-gray-500 mt-1">Drag and drop your file below.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-8">
                 <!-- Template Download -->
                <div class="mb-8 flex items-center justify-between bg-gray-50 p-4 rounded-xl border border-gray-200">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Need the template?</h3>
                        <p class="text-xs text-gray-500">Includes sample data and required columns.</p>
                    </div>
                     <a href="{{ route('inventory.template') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                        <i class="fas fa-download mr-2 text-green-600"></i>
                        Download .XLSX
                    </a>
                </div>

                 <!-- Upload Form -->
                <form action="{{ route('inventory.import') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    
                    <div class="w-full">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Upload Excel File</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:border-blue-500 hover:bg-blue-50 transition-all cursor-pointer group relative" id="drop-zone">
                            <div class="space-y-1 text-center">
                                <div class="mx-auto h-12 w-12 text-gray-400 group-hover:text-blue-500 transition-colors">
                                    <i class="fas fa-cloud-upload-alt text-4xl"></i>
                                </div>
                                <div class="flex text-sm text-gray-600 justify-center">
                                    <label for="file-upload" class="relative cursor-pointer bg-transparent rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none">
                                        <span>Upload a file</span>
                                        <input id="file-upload" name="file" type="file" class="sr-only" accept=".xlsx,.xls,.csv" required>
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">
                                    XLSX, XLS, CSV up to 5MB
                                </p>
                                <p id="file-name" class="text-sm font-bold text-green-600 mt-2 hidden"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-100">
                         <a href="{{ url()->previous() ?: route('layouts.product') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700">Cancel</a>
                        <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <i class="fas fa-upload mr-2"></i> Import Products
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Format Reference -->
             <div class="bg-gray-50 px-8 py-6 border-t border-gray-200">
                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Column Reference</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <span class="block font-medium text-gray-900">Product Name*</span>
                        <span class="text-xs text-gray-500">Unique identifier</span>
                    </div>
                    <div>
                        <span class="block font-medium text-gray-900">Category*</span>
                        <span class="text-xs text-gray-500">Must exist in system</span>
                    </div>
                    <div>
                        <span class="block font-medium text-gray-900">Units per Box*</span>
                        <span class="text-xs text-gray-500">Number of items</span>
                    </div>
                     <div>
                        <span class="block font-medium text-gray-900">Total Boxes</span>
                        <span class="text-xs text-gray-500">Initial stock</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const fileInput = document.getElementById('file-upload');
    const fileNameDisplay = document.getElementById('file-name');
    const dropZone = document.getElementById('drop-zone');

    fileInput.addEventListener('change', function() {
        if(this.files && this.files.length > 0) {
            fileNameDisplay.textContent = 'Selected: ' + this.files[0].name;
            fileNameDisplay.classList.remove('hidden');
            dropZone.classList.add('border-green-500', 'bg-green-50');
        }
    });

    // Drag and Drop Logic
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });

    function highlight(e) {
        dropZone.classList.add('border-blue-500', 'bg-blue-50');
    }

    function unhighlight(e) {
        dropZone.classList.remove('border-blue-500', 'bg-blue-50');
    }

    dropZone.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        fileInput.files = files;
        
        if(files.length > 0) {
            fileNameDisplay.textContent = 'Selected: ' + files[0].name;
            fileNameDisplay.classList.remove('hidden');
            dropZone.classList.add('border-green-500', 'bg-green-50');
        }
    }
</script>
@endsection
