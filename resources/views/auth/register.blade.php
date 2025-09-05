<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <!-- Include Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md mx-auto bg-white p-8 rounded shadow">
        <h2 class="text-2xl font-bold mb-6">Register</h2>
        <form method="POST" action="{{ route('register.post') }}">
            @csrf
            <div class="mb-4">
                <label class="block mb-1">Name</label>
                <input type="text" 
                       name="name" 
                       value="{{ old('name') }}"
                       class="w-full border rounded px-3 py-2 @error('name') border-red-500 @enderror" 
                       required>
                @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>
            <div class="mb-4">
                <label class="block mb-1">Email</label>
                <input type="email" 
                       name="email" 
                       value="{{ old('email') }}"
                       class="w-full border rounded px-3 py-2 @error('email') border-red-500 @enderror" 
                       required>
                @error('email') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>
            <div class="mb-4">
                <label class="block mb-1">Password</label>
                <input type="password" 
                       name="password" 
                       class="w-full border rounded px-3 py-2 @error('password') border-red-500 @enderror" 
                       required>
                @error('password') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>
            <div class="mb-4">
                <label class="block mb-1">Confirm Password</label>
                <input type="password" 
                       name="password_confirmation" 
                       class="w-full border rounded px-3 py-2" 
                       required>
            </div>
            <div class="mb-4">
                <label class="block mb-1">Role</label>
                <select name="role" 
                        class="w-full border rounded px-3 py-2 @error('role') border-red-500 @enderror" 
                        required>
                    <option value="">Select Role</option>
                    <option value="owner" {{ old('role') == 'owner' ? 'selected' : '' }}>Owner</option>
                    <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>Manager</option>
                    <option value="cashier" {{ old('role') == 'cashier' ? 'selected' : '' }}>Cashier</option>
                </select>
                @error('role') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
            </div>
            <button type="submit" 
                    class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 transition-colors">
                Register
            </button>
        </form>
        <div class="mt-4 text-center">
            <a href="{{ route('login') }}" 
               class="text-blue-600 hover:underline">
                Already have an account? Login
            </a>
        </div>
    </div>
</body>
</html>