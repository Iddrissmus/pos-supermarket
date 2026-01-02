<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>No Branch Assigned - POS Supermarket</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white p-8 rounded-2xl shadow-xl max-w-md w-full text-center border border-gray-100">
        <div class="mb-6 relative">
            <div class="absolute inset-0 bg-yellow-100 rounded-full w-24 h-24 mx-auto animate-pulse opacity-50"></div>
            <div class="relative bg-yellow-50 rounded-full w-24 h-24 mx-auto flex items-center justify-center">
                <svg class="w-12 h-12 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </div>
        </div>
        
        <h1 class="text-2xl font-bold text-gray-900 mb-3">No Branch Assigned</h1>
        
        <p class="text-gray-600 mb-8 leading-relaxed">
            Your account is active, but you currently don't have a branch assignment. To access this feature, you must be associated with a specific branch location.
        </p>
        
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-8 text-left flex items-start">
            <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <h3 class="font-semibold text-blue-900 text-sm mb-1">Action Required</h3>
                <p class="text-sm text-blue-800">Please contact your Business Administrator to assign you to a branch.</p>
            </div>
        </div>
        
        <a href="{{ url('/') }}" class="inline-flex items-center justify-center w-full bg-gray-900 hover:bg-gray-800 text-white font-semibold py-3 px-6 rounded-xl transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Return to Dashboard
        </a>
    </div>
</body>
</html>
