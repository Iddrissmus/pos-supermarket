<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Include Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    @extends('layouts.app')

    @section('title', 'Login')

    @section('content')
    <div class="max-w-md mx-auto bg-white p-8 rounded shadow">
        <h2 class="text-2xl font-bold mb-6">Login</h2>
        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="mb-4">
                <label class="block mb-1">Email</label>
                <input type="email" 
                       name="email" 
                       value="{{ old('email') }}"
                       class="w-full border rounded px-3 py-2 @error('email') border-red-500 @enderror" 
                       required 
                       autofocus>
                @error('email') 
                    <span class="text-red-600 text-sm">{{ $message }}</span> 
                @enderror
            </div>
            <div class="mb-4">
                <label class="block mb-1">Password</label>
                <input type="password" 
                       name="password" 
                       class="w-full border rounded px-3 py-2 @error('password') border-red-500 @enderror" 
                       required>
                @error('password') 
                    <span class="text-red-600 text-sm">{{ $message }}</span> 
                @enderror
            </div>
            <div class="mb-4 flex items-center">
                <input type="checkbox" name="remember" id="remember" class="mr-2">
                <label for="remember">Remember Me</label>
            </div>
            <button type="submit" 
                    class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition-colors">
                Login
            </button>
        </form>
        <div class="mt-4 text-center">
            <a href="{{ route('register') }}" 
               class="text-blue-600 hover:underline">
                Don't have an account? Register
            </a>
        </div>
    </div>
    @endsection
</body>
</html>