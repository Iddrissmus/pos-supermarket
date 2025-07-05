@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="max-w-md mx-auto bg-white p-8 rounded shadow">
    <h2 class="text-2xl font-bold mb-6">Login</h2>
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-4">
            <label class="block mb-1">Email</label>
            <input type="email" name="email" class="w-full border rounded px-3 py-2" required autofocus>
            @error('email') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
        <div class="mb-4">
            <label class="block mb-1">Password</label>
            <input type="password" name="password" class="w-full border rounded px-3 py-2" required>
            @error('password') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
        <div class="mb-4 flex items-center">
            <input type="checkbox" name="remember" id="remember" class="mr-2">
            <label for="remember">Remember Me</label>
        </div>
        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded">Login</button>
    </form>
    <div class="mt-4 text-center">
        <a href="{{ route('register') }}" class="text-blue-600 hover:underline">Don't have an account? Register</a>
    </div>
</div>
@endsection 