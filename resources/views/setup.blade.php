@extends('layouts.app')

@section('title', 'Environment Setup')

@section('content')
<div class="min-h-screen bg-gray-100 py-10">
    <div class="max-w-5xl mx-auto px-4">
        <div class="bg-white shadow rounded-lg p-6 space-y-6">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Environment Setup</h1>
                    <p class="text-sm text-gray-500">Configure database access, import the provided bundle, then create a SuperAdmin to start.</p>
                    <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-md px-3 py-2 mt-2">
                        After import, update your <code>.env</code> DB settings (DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD) to match the new environment. If we cannot write .env automatically, do it manually before running in production.
                    </p>
                </div>
                <div class="bg-blue-50 border border-blue-200 text-blue-800 text-xs rounded-md px-3 py-2">
                    <div class="font-semibold">Required Folder Permissions</div>
                    <ul class="list-disc pl-4 space-y-1 mt-1">
                        <li>storage (write)</li>
                        <li>public/database-backups (write)</li>
                    </ul>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">DB Host</label>
                    <input type="text" id="db_host" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200" placeholder="127.0.0.1" value="127.0.0.1">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">DB Port</label>
                    <input type="number" id="db_port" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200" placeholder="3306" value="3306">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">DB Name</label>
                    <input type="text" id="db_database" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200" placeholder="database name">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">DB Username</label>
                    <input type="text" id="db_username" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200" placeholder="database user">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">DB Password <span class="text-gray-400">(optional)</span></label>
                    <input type="password" id="db_password" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200" placeholder="leave blank if none">
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                <button id="test-connection-btn" class="inline-flex items-center gap-2 bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-plug"></i>
                    <span>Test Connection</span>
                </button>
                <button id="import-btn" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-database"></i>
                    <span>Import Database & Continue</span>
                </button>
            </div>

            <div id="setup-status" class="hidden rounded-lg border px-4 py-3 text-sm"></div>
        </div>
    </div>
</div>

<div id="admin-modal" class="fixed inset-0 bg-gray-900/70 items-center justify-center px-4 hidden z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-xl font-semibold text-gray-900">Create SuperAdmin</h3>
            <button id="close-admin-modal" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <p class="text-sm text-gray-500">After import, create a SuperAdmin account. You will be logged in automatically.</p>
        <div class="space-y-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                <input type="text" id="admin_name" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200" placeholder="Full name">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" id="admin_email" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200" placeholder="email@example.com">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" id="admin_password" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200" placeholder="••••••••">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <input type="password" id="admin_password_confirmation" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200" placeholder="••••••••">
                </div>
            </div>
        </div>
        <div id="admin-status" class="hidden rounded-lg border px-4 py-3 text-sm"></div>
        <div class="flex justify-end gap-3 pt-2">
            <button id="submit-admin" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg inline-flex items-center gap-2">
                <i class="fas fa-user-shield"></i>
                <span>Create & Login</span>
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const statusBox = document.getElementById('setup-status');
    const adminStatus = document.getElementById('admin-status');
    const adminModal = document.getElementById('admin-modal');
    const closeAdminModal = document.getElementById('close-admin-modal');

    function setStatus(element, type, message) {
        element.classList.remove('hidden', 'border-green-200', 'bg-green-50', 'text-green-700', 'border-red-200', 'bg-red-50', 'text-red-700', 'border-blue-200', 'bg-blue-50', 'text-blue-700');
        if (type === 'success') {
            element.classList.add('border-green-200', 'bg-green-50', 'text-green-700');
        } else if (type === 'error') {
            element.classList.add('border-red-200', 'bg-red-50', 'text-red-700');
        } else {
            element.classList.add('border-blue-200', 'bg-blue-50', 'text-blue-700');
        }
        element.textContent = message;
    }

    function getDbPayload() {
        return {
            db_host: document.getElementById('db_host').value.trim(),
            db_port: document.getElementById('db_port').value ? parseInt(document.getElementById('db_port').value, 10) : null,
            db_database: document.getElementById('db_database').value.trim(),
            db_username: document.getElementById('db_username').value.trim(),
            db_password: document.getElementById('db_password').value,
        };
    }

    async function refreshCsrf() {
        const response = await fetch('/setup/csrf-token', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'include',
        });

        const data = await response.json();
        csrfToken = data.token;
        return csrfToken;
    }

    async function postJson(url, payload) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
            },
            credentials: 'include',
            body: JSON.stringify(payload),
        });
    }

    document.getElementById('test-connection-btn').addEventListener('click', async (event) => {
        event.preventDefault();
        setStatus(statusBox, 'info', 'Testing database connection...');
        try {
            const response = await postJson('/setup/test-connection', getDbPayload());
            const data = await response.json();
            if (response.ok && data.success) {
                setStatus(statusBox, 'success', data.message || 'Connection successful.');
            } else {
                setStatus(statusBox, 'error', data.message || 'Connection failed.');
            }
        } catch (error) {
            setStatus(statusBox, 'error', error.message || 'Connection failed.');
        }
    });

    document.getElementById('import-btn').addEventListener('click', async (event) => {
        event.preventDefault();
        setStatus(statusBox, 'info', 'Importing database... this may take a moment.');
        try {
            const response = await postJson('/setup/import', getDbPayload());
            const data = await response.json();
            if (response.ok && data.success) {
                setStatus(statusBox, 'success', data.message || 'Import complete.');
                await refreshCsrf();
                openAdminModal();
            } else {
                setStatus(statusBox, 'error', data.message || 'Import failed.');
            }
        } catch (error) {
            setStatus(statusBox, 'error', error.message || 'Import failed.');
        }
    });

    function openAdminModal() {
        adminModal.classList.remove('hidden');
        adminModal.classList.add('flex');
    }

    function closeModal() {
        adminModal.classList.remove('flex');
        adminModal.classList.add('hidden');
    }

    closeAdminModal.addEventListener('click', () => closeModal());

    document.getElementById('submit-admin').addEventListener('click', async (event) => {
        event.preventDefault();
        setStatus(adminStatus, 'info', 'Creating SuperAdmin...');
        try {
            const response = await postJson('/setup/admin-register', {
                name: document.getElementById('admin_name').value.trim(),
                email: document.getElementById('admin_email').value.trim(),
                password: document.getElementById('admin_password').value,
                password_confirmation: document.getElementById('admin_password_confirmation').value,
            });
            const data = await response.json();
            if (response.ok && data.success) {
                setStatus(adminStatus, 'success', 'SuperAdmin created. Redirecting...');
                window.location.href = data.redirect || '/superadmin/dashboard';
            } else {
                setStatus(adminStatus, 'error', data.message || 'Registration failed.');
            }
        } catch (error) {
            setStatus(adminStatus, 'error', error.message || 'Registration failed.');
        }
    });
</script>
@endpush
@endsection

