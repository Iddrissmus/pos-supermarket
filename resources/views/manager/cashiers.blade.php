@extends('layouts.app')

@section('title', 'Manage Cashiers')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto">
    <!-- Header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-slate-800">Manage Cashiers</h1>
            <p class="text-slate-500 mt-1">Assign and manage staff for <span class="font-medium text-indigo-600">{{ $managedBranch->display_label }}</span></p>
        </div>
        <div>
            <a href="{{ route('dashboard.manager') }}" class="btn bg-white border-slate-200 hover:border-slate-300 text-slate-600 hover:text-indigo-600">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg flex items-center shadow-sm">
            <i class="fas fa-check-circle mr-3 text-emerald-500 text-lg"></i>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-lg flex items-center shadow-sm">
            <i class="fas fa-exclamation-circle mr-3 text-rose-500 text-lg"></i>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column: Actions (Create/Assign) -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- Create New Cashier -->
            @if($assignedCashiers->count() === 0)
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h2 class="font-bold text-slate-800">Create New Cashier</h2>
                </div>
                <div class="p-6">
                    <form method="POST" action="{{ route('manager.cashiers.create') }}" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
                            <input type="text" name="name" class="form-input w-full" required placeholder="e.g. John Doe">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                            <input type="email" name="email" class="form-input w-full" required placeholder="john@example.com">
                        </div>
                        <hr class="border-slate-100 my-4">
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <label class="block text-sm font-medium text-slate-700">Password</label>
                                    <button type="button" onclick="generateManagerPassword()" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold">
                                        Generate
                                    </button>
                                </div>
                                <div class="relative">
                                    <input type="password" id="password" name="password" class="form-input w-full pr-10" required minlength="6">
                                    <button type="button" onclick="toggleManagerPassword('password', 'eye-pass')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                        <i id="eye-pass" class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Confirm Password</label>
                                <div class="relative">
                                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-input w-full pr-10" required minlength="6">
                                    <button type="button" onclick="toggleManagerPassword('password_confirmation', 'eye-conf')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                        <i id="eye-conf" class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Password Alert -->
                        <div id="manager-password-alert" class="hidden p-3 bg-indigo-50 border border-indigo-100 rounded-lg">
                            <div class="text-xs text-indigo-800 font-semibold mb-1">Generated Password:</div>
                            <div class="flex items-center justify-between bg-white px-2 py-1.5 rounded border border-indigo-100">
                                <code id="manager-password-display" class="text-sm font-mono text-indigo-600 select-all"></code>
                                <button type="button" onclick="copyManagerPassword()" class="text-indigo-500 hover:text-indigo-700">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                            <p class="text-[10px] text-indigo-600 mt-2">
                                <i class="fas fa-info-circle mr-1"></i>Copy this now. It won't be shown again.
                            </p>
                        </div>

                        <button type="submit" class="btn w-full bg-indigo-500 hover:bg-indigo-600 text-white">
                            Create Account
                        </button>
                    </form>
                </div>
            </div>
            @else
            <!-- Limit Reached -->
            <div class="bg-amber-50 rounded-xl p-6 border border-amber-200">
                <div class="flex items-start">
                    <div class="shrink-0 bg-amber-100 rounded-full p-2 text-amber-600 mt-1">
                        <i class="fas fa-lock text-lg"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-bold text-amber-800">Limit Reached</h3>
                        <p class="text-sm text-amber-700 mt-2">
                            This branch currently has a maximum of 1 active cashier.
                        </p>
                        <p class="text-sm text-amber-700 mt-2">
                            To add a new cashier, you must remove the existing one first.
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Assign Existing -->
            @if($unassignedCashiers->count() > 0 && $assignedCashiers->count() === 0)
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                 <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h2 class="font-bold text-slate-800">Assign Existing Cashier</h2>
                </div>
                <div class="p-6">
                    <form method="POST" action="{{ route('manager.cashiers.assign') }}">
                        @csrf
                        <label class="block text-sm font-medium text-slate-700 mb-2">Select Cashier</label>
                        <select name="cashier_id" class="form-select w-full mb-4">
                             <option value="">Choose...</option>
                            @foreach($unassignedCashiers as $cashier)
                                <option value="{{ $cashier->id }}">{{ $cashier->name }} ({{ $cashier->email }})</option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn w-full bg-white border-slate-200 hover:border-slate-300 text-slate-600 hover:text-indigo-600">
                            Assign to Branch
                        </button>
                    </form>
                </div>
            </div>
            @endif

        </div>

        <!-- Right Column: Current Cashiers List -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                    <h2 class="font-bold text-slate-800">Active Staff</h2>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                        {{ $assignedCashiers->count() }} / 1 Assigned
                    </span>
                </div>

                @if($assignedCashiers->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th class="px-6 py-3 font-semibold text-slate-500 text-xs uppercase tracking-wider">Cashier</th>
                                <th class="px-6 py-3 font-semibold text-slate-500 text-xs uppercase tracking-wider">Contact</th>
                                <th class="px-6 py-3 font-semibold text-slate-500 text-xs uppercase tracking-wider">Joined</th>
                                <th class="px-6 py-3 font-semibold text-slate-500 text-xs uppercase tracking-wider text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($assignedCashiers as $cashier)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-6 py-4">
                                         <div class="flex items-center">
                                            <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold mr-3">
                                                {{ substr($cashier->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-800">{{ $cashier->name }}</div>
                                                <div class="text-xs text-slate-500">ID: #{{ $cashier->id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-slate-600">{{ $cashier->email }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-slate-600">{{ $cashier->created_at->format('M d, Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button type="button" 
                                                    class="btn-sm bg-white border-slate-200 hover:bg-rose-50 hover:border-rose-200 hover:text-rose-600 text-slate-600 transition-colors"
                                                    onclick="confirmRemove('{{ $cashier->id }}', '{{ addslashes($cashier->name) }}')">
                                                <i class="fas fa-user-minus mr-2"></i> Remove
                                            </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="p-12 text-center">
                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-users text-slate-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-slate-900">No Cashiers Assigned</h3>
                    <p class="text-slate-500 mt-2 max-w-sm mx-auto">
                        This branch currently has no active cashiers. Use the form on the left to create or assign one.
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Password JS (Kept original logic) -->
@endsection

@section('scripts')
<!-- Removal Confirmation Modal -->
<div id="removeCashierModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeRemoveModal()"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-rose-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-exclamation-triangle text-rose-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-slate-900" id="modal-title">Remove Cashier</h3>
                        <div class="mt-2 text-sm text-slate-500">
                            <p>Are you sure you want to remove <span id="remove-cashier-name" class="font-bold text-slate-700"></span> from this branch?</p>
                            <p class="mt-1">This action cannot be undone.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <form method="POST" action="{{ route('manager.cashiers.unassign') }}" id="removeCashierForm">
                    @csrf
                    <input type="hidden" name="cashier_id" id="modal_cashier_id">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-rose-600 text-base font-medium text-white hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Confirm Removal
                    </button>
                </form>
                <button type="button" onclick="closeRemoveModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function confirmRemove(id, name) {
    document.getElementById('modal_cashier_id').value = id;
    document.getElementById('remove-cashier-name').textContent = name;
    document.getElementById('removeCashierModal').classList.remove('hidden');
}

function closeRemoveModal() {
    document.getElementById('removeCashierModal').classList.add('hidden');
}

// Password Generator Functions
function generateManagerPassword() {
    const length = 12;
    const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
    let password = "";
    for (let i = 0; i < length; i++) {
        password += charset.charAt(Math.floor(Math.random() * charset.length));
    }
    document.getElementById('password').value = password;
    document.getElementById('password_confirmation').value = password;
    document.getElementById('manager-password-display').textContent = password;
    document.getElementById('manager-password-alert').classList.remove('hidden');
    
    // Switch to text type to show it
    document.getElementById('password').type = 'text';
    document.getElementById('password_confirmation').type = 'text';
}

function toggleManagerPassword(fieldId, eyeId) {
    const field = document.getElementById(fieldId);
    if (field.type === 'password') {
        field.type = 'text';
        document.getElementById(eyeId).classList.remove('fa-eye');
        document.getElementById(eyeId).classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        document.getElementById(eyeId).classList.remove('fa-eye-slash');
        document.getElementById(eyeId).classList.add('fa-eye');
    }
}

function copyManagerPassword() {
    const password = document.getElementById('manager-password-display').textContent;
    navigator.clipboard.writeText(password).then(() => {
        // Optional: show a small toast or tooltip
    });
}
</script>
@endsection

