@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="max-w-md mx-auto bg-white p-8 rounded shadow">
    <h2 class="text-2xl font-bold mb-6">Register</h2>
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="mb-4">
            <label class="block mb-1">Name</label>
            <input type="text" name="name" class="w-full border rounded px-3 py-2" required>
            @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
        <div class="mb-4">
            <label class="block mb-1">Email</label>
            <input type="email" name="email" class="w-full border rounded px-3 py-2" required>
            @error('email') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
        <div class="mb-4">
            <label class="block mb-1">Password</label>
            <input type="password" name="password" class="w-full border rounded px-3 py-2" required>
            @error('password') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
        <div class="mb-4">
            <label class="block mb-1">Confirm Password</label>
            <input type="password" name="password_confirmation" class="w-full border rounded px-3 py-2" required>
        </div>
        <div class="mb-4">
            <label class="block mb-1">Role</label>
            <select name="role" class="w-full border rounded px-3 py-2" required>
                <option value="">Select Role</option>
                <option value="owner">Owner</option>
                <option value="manager">Manager</option>
                <option value="cashier">Cashier</option>
            </select>
            @error('role') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
        </div>
        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded">Register</button>
    </form>
    <div class="mt-4 text-center">
        <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Already have an account? Login</a>
    </div>
</div>
@endsection 