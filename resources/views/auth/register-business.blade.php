<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Start Your Business - POS Supermarket</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body class="bg-gray-50">

    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="{{ url('/') }}" class="flex items-center space-x-2">
                    <div class="w-10 h-10 bg-gradient-to-r from-purple-600 to-indigo-600 rounded-lg flex items-center justify-center">
                        <i class="fas fa-shopping-cart text-white text-xl"></i>
                    </div>
                    <span class="text-xl font-bold gradient-text">POS Supermarket</span>
                </a>
                
                <a href="{{ route('login') }}" class="text-gray-600 hover:text-purple-600 font-medium">
                    Already have an account? Login
                </a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Start Your Journey</h1>
            <p class="text-xl text-gray-600">Choose a plan to register your business</p>
        </div>

        @if (session('success'))
            <div class="max-w-3xl mx-auto mb-8">
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm flex items-start space-x-3">
                    <i class="fas fa-check-circle mt-0.5"></i>
                    <p>{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if ($errors->has('general'))
            <div class="max-w-3xl mx-auto mb-8">
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm flex items-start space-x-3">
                    <i class="fas fa-exclamation-triangle mt-0.5"></i>
                    <p>{{ $errors->first('general') }}</p>
                </div>
            </div>
        @endif

        <!-- Pricing Section -->
            @foreach($plans as $plan)
                @php
                    $isGrowth = $plan->slug === 'growth';
                @endphp
                <div class="bg-white rounded-2xl shadow-sm border {{ $isGrowth ? 'border-purple-100 shadow-xl ring-2 ring-transparent hover:ring-purple-400 transform md:-translate-y-2' : 'border-gray-100 hover:shadow-md ring-2 ring-transparent hover:ring-purple-200' }} p-8 flex flex-col relative cursor-pointer transition-all" onclick="selectPlan('{{ $plan->slug }}', '{{ $plan->duration_days }}', '{{ $plan->price }}', '{{ $plan->name }}')">
                    @if($isGrowth)
                    <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-purple-600 text-white px-4 py-1 rounded-full text-xs font-bold uppercase">
                        Most Popular
                    </div>
                    @endif
                    <h3 class="text-xl font-bold text-gray-800">{{ $plan->name }}</h3>
                    <p class="text-gray-500 text-sm mt-1">{{ $plan->description }}</p>
                    <div class="my-6">
                        <span class="text-4xl font-bold text-gray-900">GHS {{ number_format($plan->price, 2) }}</span>
                        <span class="text-gray-500">
                            @if($plan->duration_days == 30) /mo
                            @elseif($plan->duration_days == 365) /yr
                            @else / {{ $plan->duration_days }} days
                            @endif
                        </span>
                    </div>
                    <ul class="space-y-3 mb-8 flex-1">
                        @if(is_array($plan->features))
                            @foreach($plan->features as $feature)
                            <li class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-check text-green-500 mr-2"></i> {{ $feature }}
                            </li>
                            @endforeach
                        @endif
                    </ul>
                    <button class="w-full py-3 {{ $isGrowth ? 'bg-purple-600 hover:bg-purple-700 text-white shadow-lg' : 'bg-gray-50 hover:bg-gray-100 text-gray-800 border border-gray-200' }} font-semibold rounded-xl transition-colors plan-btn" data-plan="{{ $plan->slug }}">
                        Select {{ $plan->name }}
                    </button>
                </div>
            @endforeach

    <!-- Registration Modal (Wizard) -->
    <div id="registration-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeModal()"></div>

            <!-- Modal Panel -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                
                <!-- Modal Header with Steps -->
                <div class="bg-gray-50 px-4 py-5 border-b border-gray-200 sm:px-6">
                    <div class="flex justify-between items-center mb-5">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Set up your <span id="selected-plan-name" class="font-bold text-purple-600">Starter</span> Plan
                        </h3>
                        <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeModal()">
                            <span class="sr-only">Close</span>
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    
                    <!-- Stepper -->
                    <div class="flex items-center justify-between max-w-2xl mx-auto">
                        <div class="step-indicator flex flex-col items-center cursor-pointer" onclick="goToStep(1)">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center bg-purple-600 text-white font-bold text-sm shadow ring-4 ring-purple-100 transition-colors" id="step-icon-1">1</div>
                            <span class="text-xs font-medium mt-1 text-purple-800" id="step-text-1">Business</span>
                        </div>
                        <div class="flex-1 h-0.5 bg-gray-200 mx-2" id="line-1"></div>
                        
                        <div class="step-indicator flex flex-col items-center cursor-pointer" onclick="goToStep(2)">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center bg-white text-gray-500 border-2 border-gray-300 font-bold text-sm transition-colors" id="step-icon-2">2</div>
                            <span class="text-xs font-medium mt-1 text-gray-500" id="step-text-2">Location</span>
                        </div>
                        <div class="flex-1 h-0.5 bg-gray-200 mx-2" id="line-2"></div>

                        <div class="step-indicator flex flex-col items-center cursor-pointer" onclick="goToStep(3)">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center bg-white text-gray-500 border-2 border-gray-300 font-bold text-sm transition-colors" id="step-icon-3">3</div>
                            <span class="text-xs font-medium mt-1 text-gray-500" id="step-text-3">Owner</span>
                        </div>
                         <div class="flex-1 h-0.5 bg-gray-200 mx-2" id="line-3"></div>

                        <div class="step-indicator flex flex-col items-center cursor-pointer" onclick="goToStep(4)">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center bg-white text-gray-500 border-2 border-gray-300 font-bold text-sm transition-colors" id="step-icon-4">4</div>
                            <span class="text-xs font-medium mt-1 text-gray-500" id="step-text-4">Review</span>
                        </div>
                    </div>
                </div>

                <form id="business-signup-form" action="{{ route('business-signup.store') }}" method="POST" enctype="multipart/form-data" novalidate>
                    @csrf
                    <input type="hidden" id="plan_type" name="plan_type" value="">
                    <input type="hidden" id="plan_duration" name="plan_duration" value="">
                    <input type="hidden" id="plan_price" name="plan_price" value="">
                    
                    <div class="px-4 py-3 sm:px-6">
                        @if($errors->any())
                            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4 rounded shadow-sm">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-exclamation-circle text-red-400"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-red-800">There were errors with your submission</h3>
                                        <div class="mt-2 text-sm text-red-700">
                                            <ul class="list-disc pl-5 space-y-1">
                                                @foreach($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <div class="px-4 py-5 sm:p-6 min-h-[400px]">
                        
                        <!-- Step 1: Business Info -->
                        <div class="step-content" id="step-1">
                            <h4 class="text-lg font-medium text-gray-900 mb-4">Business Details</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="col-span-2 md:col-span-1">
                                    <label class="block text-sm font-medium text-gray-700">Business Name *</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-building text-gray-400"></i>
                                        </div>
                                        <input type="text" name="business_name" id="business_name" required value="{{ old('business_name') }}"
                                            class="focus:ring-4 focus:ring-purple-500/10 focus:border-purple-500 block w-full pl-10 text-base border-2 border-gray-200 rounded-xl py-3 bg-white transition-all outline-none">
                                    </div>
                                </div>
                                
                                <div class="col-span-2 md:col-span-1">
                                    <label class="block text-sm font-medium text-gray-700">Business Type *</label>
                                    <select name="business_type_id" id="business_type_id" required
                                        class="mt-1 block w-full pl-3 pr-10 py-3 text-base border-2 border-gray-200 focus:outline-none focus:ring-4 focus:ring-purple-500/10 focus:border-purple-500 rounded-xl bg-white transition-all">
                                        <option value="">Select Type</option>
                                        @foreach($businessTypes as $type)
                                            <option value="{{ $type->id }}" {{ old('business_type_id') == $type->id ? 'selected' : '' }}>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">Logo (Optional)</label>
                                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:bg-gray-50 transition-colors cursor-pointer" onclick="document.getElementById('logo').click()">
                                        <div class="space-y-1 text-center">
                                            <i class="fas fa-image text-gray-400 text-3xl mb-2"></i>
                                            <div class="flex text-sm text-gray-600">
                                                <label for="logo" class="relative cursor-pointer rounded-md font-medium text-purple-600 hover:text-purple-500 focus-within:outline-none">
                                                    <span>Upload a file</span>
                                                    <input id="logo" name="logo" type="file" class="sr-only" accept="image/*" onchange="previewLogo(this)">
                                                </label>
                                                <p class="pl-1">or drag and drop</p>
                                            </div>
                                            <p class="text-xs text-gray-500">PNG, JPG, GIF up to 2MB</p>
                                            <p id="logo-filename" class="text-xs text-green-600 font-semibold mt-2 hidden"></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                         <!-- Step 2: Location -->
                        <div class="step-content hidden" id="step-2">
                            <h4 class="text-lg font-medium text-gray-900 mb-4">Branch Location</h4>
                            <div class="grid grid-cols-1 gap-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Main Branch Name *</label>
                                        <input type="text" name="branch_name" id="branch_name" required value="{{ old('branch_name', 'Main Branch') }}"
                                            class="mt-1 focus:ring-4 focus:ring-purple-500/10 focus:border-purple-500 block w-full text-base border-2 border-gray-200 rounded-xl py-3 bg-white transition-all outline-none">
                                    </div>
                                    <div>
                                         <label class="block text-sm font-medium text-gray-700">Region *</label>
                                         <select id="region" name="region" required
                                            class="mt-1 block w-full pl-3 pr-10 py-3 text-base border-2 border-gray-200 focus:outline-none focus:ring-4 focus:ring-purple-500/10 focus:border-purple-500 rounded-xl bg-white transition-all">
                                            <option value="">Select Region</option>
                                            @foreach(['Greater Accra', 'Ashanti', 'Western', 'Eastern', 'Central', 'Northern', 'Upper East', 'Upper West', 'Volta', 'Brong-Ahafo', 'Western North', 'Bono East', 'Ahafo', 'Savannah', 'North East', 'Oti'] as $ghanaRegion)
                                                <option value="{{ $ghanaRegion }}" {{ old('region') == $ghanaRegion ? 'selected' : '' }}>{{ $ghanaRegion }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Pin Location on Map *</label>
                                    <div class="relative">
                                        <div class="absolute top-2 left-2 right-12 z-[1000]">
                                             <input type="text" id="search-location" placeholder="Search town..." class="w-full bg-white border border-gray-400 rounded shadow-sm px-3 py-1.5 text-sm">
                                        </div>
                                        <button type="button" onclick="searchLocation()" class="absolute top-2 right-2 z-[1000] bg-purple-600 text-white p-1.5 rounded shadow-sm">
                                            <i class="fas fa-search"></i>
                                        </button>
                                        <div id="map" class="h-64 w-full rounded-lg border border-gray-300"></div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1"><i class="fas fa-info-circle"></i> Click map to pin exact location.</p>
                                    <input type="hidden" id="latitude" name="latitude" value="{{ old('latitude') }}">
                                    <input type="hidden" id="longitude" name="longitude" value="{{ old('longitude') }}">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Address / Digital Address *</label>
                                    <textarea name="address" id="address" rows="2" required placeholder="e.g. GPS Address or Street Name"
                                        class="mt-1 focus:ring-purple-500 focus:border-purple-500 block w-full sm:text-sm border-gray-400 rounded-md bg-white">{{ old('address') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Owner Info -->
                        <div class="step-content hidden" id="step-3">
                            <h4 class="text-lg font-medium text-gray-900 mb-4">Owner Information</h4>
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Full Name *</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-user text-gray-400"></i>
                                        </div>
                                        <input type="text" name="owner_name" id="owner_name" required value="{{ old('owner_name') }}"
                                            class="focus:ring-purple-500 focus:border-purple-500 block w-full pl-10 sm:text-sm border-gray-400 rounded-md py-2.5 bg-white">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Email Address *</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-envelope text-gray-400"></i>
                                        </div>
                                        <input type="email" name="owner_email" id="owner_email" required value="{{ old('owner_email') }}"
                                            class="focus:ring-purple-500 focus:border-purple-500 block w-full pl-10 sm:text-sm border-gray-400 rounded-md py-2.5 bg-white">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Phone Number *</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fas fa-phone text-gray-400"></i>
                                        </div>
                                        <input type="tel" name="owner_phone" id="owner_phone" required value="{{ old('owner_phone') }}" placeholder="024xxxxxxx"
                                            class="focus:ring-purple-500 focus:border-purple-500 block w-full pl-10 sm:text-sm border-gray-400 rounded-md py-2.5 bg-white">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 4: Review -->
                        <div class="step-content hidden" id="step-4">
                            <div class="text-center mb-6">
                                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-check text-green-600 text-2xl"></i>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900">Almost There!</h3>
                                <p class="text-gray-500">Please review your details before proceeding to payment.</p>
                            </div>

                            <div class="bg-gray-50 rounded-lg p-4 text-sm border border-gray-200">
                                <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                                    <div>
                                        <dt class="text-gray-500">Business</dt>
                                        <dd class="font-medium text-gray-900" id="preview-business">N/A</dd>
                                    </div>
                                    <div>
                                        <dt class="text-gray-500">Plan</dt>
                                        <dd class="font-medium text-gray-900 uppercase" id="preview-plan">N/A</dd>
                                    </div>
                                    <div>
                                        <dt class="text-gray-500">Amount to Pay</dt>
                                        <dd class="font-bold text-purple-600 text-lg" id="preview-price">GHS 0.00</dd>
                                    </div>
                                    <div>
                                        <dt class="text-gray-500">Owner</dt>
                                        <dd class="font-medium text-gray-900" id="preview-owner">N/A</dd>
                                    </div>
                                    <div>
                                        <dt class="text-gray-500">Contact</dt>
                                        <dd class="font-medium text-gray-900" id="preview-contact">N/A</dd>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <dt class="text-gray-500">Location</dt>
                                        <dd class="font-medium text-gray-900" id="preview-location">N/A</dd>
                                    </div>
                                </dl>
                            </div>
                            
                            <div class="mt-6 space-y-3">
                                <div class="flex items-center p-4 bg-blue-50 rounded-lg text-blue-800 text-sm">
                                    <i class="fas fa-clock mr-3 text-lg"></i>
                                    <div>
                                        <p class="font-semibold">Subscription Validity</p>
                                        <p id="preview-duration-msg">Your plan is valid for X days.</p>
                                        <p class="text-xs mt-1 text-blue-600">You will receive an email notification when your package time is almost due.</p>
                                    </div>
                                </div>

                                <div class="flex items-center p-4 bg-purple-50 rounded-lg text-purple-700 text-sm">
                                    <i class="fas fa-lock mr-3 text-lg"></i>
                                    <span>You will be redirected to Paystack for secure payment.</span>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Footer Actions -->
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" id="next-btn" onclick="nextStep()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-purple-600 text-base font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Next <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                        <button type="submit" id="submit-btn" class="hidden w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Pay & Register <i class="fas fa-check ml-2"></i>
                        </button>
                        <button type="button" id="prev-btn" onclick="prevStep()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm hidden">
                            Back
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        let currentStep = 1;
        const totalSteps = 4;
        let map, marker;
        const mapInitialized = false;

        function openModal() {
            document.getElementById('registration-modal').classList.remove('hidden');
            setTimeout(() => {
                 if (!mapInitialized && currentStep === 2) {
                    initMap();
                }
            }, 100);
        }

        function closeModal() {
            document.getElementById('registration-modal').classList.add('hidden');
            
            // Reset Form - More thorough
            const form = document.getElementById('business-signup-form');
            form.reset();
            
            // Clear all validation styles
            form.querySelectorAll('.border-red-500').forEach(el => el.classList.remove('border-red-500'));
            
            // Reset Step to 1
            document.getElementById(`step-${currentStep}`).classList.add('hidden');
            currentStep = 1;
            document.getElementById('step-1').classList.remove('hidden');
            updateStepperUI();
            
            // Reset Buttons
            document.getElementById('prev-btn').classList.add('hidden');
            document.getElementById('next-btn').classList.remove('hidden');
            document.getElementById('submit-btn').classList.add('hidden');
            
            // Reset Map Marker if exists
            if (marker && map) {
                map.removeLayer(marker);
                marker = null;
            }
            // Clear location fields manually
            document.getElementById('latitude').value = '';
            document.getElementById('longitude').value = '';
            
            // Specifically clear logo info
            document.getElementById('logo-filename').classList.add('hidden');
            document.getElementById('logo-filename').textContent = '';
        }

        function selectPlan(plan, duration, price, name) {
            // Clear previous data first if it was already used
            const form = document.getElementById('business-signup-form');
            form.reset();
            
            document.getElementById('plan_type').value = plan;
            document.getElementById('plan_duration').value = duration;
            document.getElementById('plan_price').value = price;
            document.getElementById('selected-plan-name').textContent = name;
            openModal();
        }

        function goToStep(step) {
            // Validate before jumping forward? For now, allow navigation if fields populated, but simple restriction:
            if(step > currentStep && !validateStep(currentStep)) return;
            
            // Hide current
            document.getElementById(`step-${currentStep}`).classList.add('hidden');
            
            // Show new
            currentStep = step;
            document.getElementById(`step-${currentStep}`).classList.remove('hidden');
            // Initialize
        updateStepperUI();

        // Auto-reopen modal if there are validation errors
        @if($errors->any())
            setTimeout(() => {
                document.getElementById('registration-modal').classList.remove('hidden');
                
                // Restore plan information from old input
                @if(old('plan_type'))
                    document.getElementById('plan_type').value = "{{ old('plan_type') }}";
                    document.getElementById('plan_duration').value = "{{ old('plan_duration') }}";
                    document.getElementById('plan_price').value = "{{ old('plan_price') }}";
                    
                    const planName = "{{ old('plan_type') }}".charAt(0).toUpperCase() + "{{ old('plan_type') }}".slice(1);
                    document.getElementById('selected-plan-name').textContent = planName;
                @endif
                
                // Scroll to error alert
                setTimeout(() => {
                    const errorAlert = document.querySelector('.bg-red-50');
                    if (errorAlert) {
                        errorAlert.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }, 200);
            }, 100);
        @endif
            if(currentStep === 2 && !map) {
                setTimeout(initMap, 200); // Small delay for render
            } else if (currentStep === 2 && map) {
                setTimeout(() => map.invalidateSize(), 200);
            }

            if(currentStep === 4) updatePreview();

            // Update Buttons
            document.getElementById('prev-btn').classList.toggle('hidden', currentStep === 1);
            document.getElementById('next-btn').classList.toggle('hidden', currentStep === 4);
            document.getElementById('submit-btn').classList.toggle('hidden', currentStep !== 4);
        }

        function nextStep() {
            if (validateStep(currentStep)) {
                goToStep(currentStep + 1);
            }
        }

        function prevStep() {
             goToStep(currentStep - 1);
        }

        function validateStep(step) {
            const currentDiv = document.getElementById(`step-${step}`);
            const inputs = currentDiv.querySelectorAll('input[required], select[required], textarea[required]');
            let valid = true;
            let missingFields = [];

            inputs.forEach(input => {
                if (!input.value) {
                    input.classList.add('border-red-500');
                    valid = false;
                    // Try to find label
                    const label = input.closest('div')?.querySelector('label')?.textContent || input.name || 'Required field';
                    missingFields.push(label.replace('*', '').trim());
                } else {
                    input.classList.remove('border-red-500');
                }
            });

            // Special check for Map in step 2
            if (step === 2) {
                const lat = document.getElementById('latitude').value;
                if (!lat) {
                    valid = false;
                    missingFields.push('Exact location on map');
                }
            }

            if(!valid) {
                alert('Please complete the following:\n- ' + missingFields.join('\n- '));
            }
            return valid;
        }

        document.getElementById('business-signup-form').onsubmit = function(e) {
            // Final check of all steps
            for(let i=1; i<=3; i++) {
                if(!validateStep(i)) {
                    goToStep(i);
                    return false;
                }
            }

            const btn = document.getElementById('submit-btn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Redirecting to Paystack...';
            return true;
        };

        function updateStepperUI() {
            for(let i = 1; i <= totalSteps; i++) {
                const icon = document.getElementById(`step-icon-${i}`);
                const text = document.getElementById(`step-text-${i}`);
                const line = document.getElementById(`line-${i-1}`); // Line before this step

                if (i < currentStep) {
                    // Completed
                    icon.classList.remove('bg-white', 'text-gray-500', 'border-gray-300', 'bg-purple-600', 'ring-4', 'ring-purple-100');
                    icon.classList.add('bg-green-500', 'text-white', 'border-transparent');
                    icon.innerHTML = '<i class="fas fa-check"></i>';
                    text.classList.remove('text-gray-500', 'text-purple-800');
                    text.classList.add('text-green-600');
                    if(line) line.classList.replace('bg-gray-200', 'bg-green-500');
                } else if (i === currentStep) {
                    // Current
                    icon.classList.remove('bg-white', 'text-gray-500', 'bg-green-500', 'text-white', 'border-transparent');
                    icon.classList.add('bg-purple-600', 'text-white', 'ring-4', 'ring-purple-100');
                    icon.textContent = i;
                    text.classList.remove('text-gray-500', 'text-green-600');
                    text.classList.add('text-purple-800');
                     if(line) line.classList.replace('bg-green-500', 'bg-gray-200');
                } else {
                    // Future
                    icon.classList.remove('bg-purple-600', 'text-white', 'bg-green-500', 'ring-4', 'ring-purple-100', 'border-transparent');
                    icon.classList.add('bg-white', 'text-gray-500', 'border-2', 'border-gray-300');
                    icon.textContent = i;
                    text.classList.remove('text-purple-800', 'text-green-600');
                    text.classList.add('text-gray-500');
                     if(line) line.classList.replace('bg-green-500', 'bg-gray-200');
                }
            }
        }

        function updatePreview() {
            document.getElementById('preview-business').textContent = document.getElementById('business_name').value;
            const typeSelect = document.getElementById('business_type_id');
            const typeName = typeSelect.options[typeSelect.selectedIndex]?.text || 'N/A';
             // document.getElementById('preview-type').textContent = typeName; 
            
            document.getElementById('preview-plan').textContent = document.getElementById('plan_type').value;
            document.getElementById('preview-owner').textContent = document.getElementById('owner_name').value;
             document.getElementById('preview-contact').textContent = document.getElementById('owner_email').value + ' | ' + document.getElementById('owner_phone').value;
            document.getElementById('preview-location').textContent = document.getElementById('address').value + ', ' + document.getElementById('region').value;
            
            // Update Price
            const price = document.getElementById('plan_price').value;
            document.getElementById('preview-price').textContent = `GHS ${parseFloat(price).toFixed(2)}`;
            
            // Update Duration Message
            const duration = document.getElementById('plan_duration').value;
            let durationText = '';
            if (duration == 30) durationText = '1 Month';
            else if (duration == 365) durationText = '1 Year';
            else durationText = duration + ' Days';
            
            document.getElementById('preview-duration-msg').textContent = `Your package is valid for ${durationText}.`;
        }

        function previewLogo(input) {
            if (input.files && input.files[0]) {
                document.getElementById('logo-filename').textContent = input.files[0].name;
                document.getElementById('logo-filename').classList.remove('hidden');
            }
        }

        // Map Functions
        function initMap() {
             if(map) return;
            const center = [6.6885, -1.6244];
            map = L.map('map').setView(center, 7);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap', maxZoom: 19
            }).addTo(map);

            map.on('click', function(e) {
                setMarker(e.latlng.lat, e.latlng.lng);
            });
            
             if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    map.setView([lat, lng], 13);
                    setMarker(lat, lng);
                });
            }
        }

        function setMarker(lat, lng) {
            if (marker) map.removeLayer(marker);
            marker = L.marker([lat, lng], {draggable: true}).addTo(map);
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;
            
            reverseGeocode(lat, lng);
            marker.on('dragend', function(e) {
                const pos = marker.getLatLng();
                document.getElementById('latitude').value = pos.lat;
                document.getElementById('longitude').value = pos.lng;
                reverseGeocode(pos.lat, pos.lng);
            });
        }

        function reverseGeocode(lat, lng) {
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                .then(res => res.json())
                .then(data => {
                    console.log('Reverse Geocode:', data); // Debug
                    if(data.display_name) {
                        document.getElementById('address').value = data.display_name;
                    }
                    
                    // Autofill Region
                    if (data.address) {
                        const state = data.address.state || data.address.region;
                        if (state) {
                            const regionSelect = document.getElementById('region');
                            // Clean string (e.g., "Greater Accra Region" -> "Greater Accra")
                            let cleanState = state.replace(' Region', '');
                            
                            // Try to find matching option
                            for (let i = 0; i < regionSelect.options.length; i++) {
                                if (regionSelect.options[i].value.toLowerCase() === cleanState.toLowerCase()) {
                                    regionSelect.selectedIndex = i;
                                    break;
                                }
                            }
                        }
                    }
                })
                .catch(err => console.error('Geocode error:', err));
        }
        
         function searchLocation() {
            const query = document.getElementById('search-location').value;
            if(!query) return;
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&countrycodes=gh`)
                .then(res => res.json())
                .then(data => {
                    if(data && data.length > 0) {
                        const lat = parseFloat(data[0].lat);
                        const lon = parseFloat(data[0].lon);
                        map.setView([lat, lon], 13);
                        setMarker(lat, lon);
                    }
                });
        }
    </script>
</body>
</html>
