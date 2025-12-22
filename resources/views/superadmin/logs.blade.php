@extends('layouts.app')

@section('title', 'Laravel Logs')
@php
    use Illuminate\Support\Str;
@endphp

@section('content')
<div class="min-h-screen bg-gray-100 py-6">
    <div class="max-w-7xl mx-auto px-4 space-y-4">
        <div class="bg-white shadow rounded-lg p-4 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Laravel Application Logs</h1>
                <p class="text-sm text-gray-500">From {{ $path }} ({{ $size_human }})</p>
                @if(session('status'))
                    <p class="text-xs text-green-600 mt-1">{{ session('status') }}</p>
                @endif
                @if(session('error'))
                    <p class="text-xs text-red-600 mt-1">{{ session('error') }}</p>
                @endif
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('superadmin.logs.download') }}" class="inline-flex items-center gap-2 bg-blue-600 text-white px-3 py-2 rounded-lg text-sm hover:bg-blue-700">
                    <i class="fas fa-download text-xs"></i>
                    <span>Download</span>
                </a>
                <a href="{{ route('superadmin.logs', request()->query()) }}" class="inline-flex items-center gap-2 bg-gray-200 text-gray-800 px-3 py-2 rounded-lg text-sm hover:bg-gray-300">
                    <i class="fas fa-sync text-xs"></i>
                    <span>Refresh</span>
                </a>
                <form action="{{ route('superadmin.logs.clear') }}" method="POST" onsubmit="return confirm('Clear laravel.log? This cannot be undone.');">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 bg-red-600 text-white px-3 py-2 rounded-lg text-sm hover:bg-red-700">
                        <i class="fas fa-trash-alt text-xs"></i>
                        <span>Clear</span>
                    </button>
                </form>
            </div>
        </div>

        @if(!$hasLogs)
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg p-4">
                Log file not found at {{ $path }}.
            </div>
        @else
            <form method="GET" action="{{ route('superadmin.logs') }}" class="bg-white shadow rounded-lg p-4 flex flex-col lg:flex-row lg:items-end gap-3">
                <div class="w-full lg:w-40">
                    <label class="text-xs font-semibold text-gray-600 block mb-1">Log Level</label>
                    <select name="level" class="w-full border rounded-lg px-3 py-2 text-sm">
                        @php
                            $levels = ['all','debug','info','notice','warning','error','critical','alert','emergency'];
                        @endphp
                        @foreach($levels as $lvl)
                            <option value="{{ $lvl }}" {{ $level === $lvl ? 'selected' : '' }}>{{ ucfirst($lvl) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="text-xs font-semibold text-gray-600 block mb-1">Search</label>
                    <input type="text" name="search" value="{{ $search }}" class="w-full border rounded-lg px-3 py-2 text-sm" placeholder="Search in message or stack trace">
                </div>
                <div class="w-full lg:w-32">
                    <label class="text-xs font-semibold text-gray-600 block mb-1">Lines to show</label>
                    <select name="lines" class="w-full border rounded-lg px-3 py-2 text-sm">
                        @foreach([100,200,500,1000,2000,5000,10000] as $opt)
                            <option value="{{ $opt }}" {{ (int)$lines === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-full lg:w-auto">
                    <button type="submit" class="inline-flex items-center gap-2 bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-emerald-700 w-full justify-center">
                        <i class="fas fa-filter text-xs"></i>
                        <span>Apply Filters</span>
                    </button>
                </div>
            </form>

            @if($error)
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4">
                    {{ $error }}
                </div>
            @endif

            <div class="bg-white shadow rounded-lg border border-gray-200">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                    <div class="text-sm font-semibold text-gray-700">Log Entries ({{ count($entries) }})</div>
                    <div class="text-xs text-gray-500">Newest first</div>
                </div>
                <div class="divide-y divide-gray-100 max-h-[70vh] overflow-auto">
                    @forelse($entries as $entry)
                        <div class="px-4 py-3 space-y-2">
                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                @php
                                    $levelColors = [
                                        'emergency' => 'bg-red-800 text-white',
                                        'alert' => 'bg-red-700 text-white',
                                        'critical' => 'bg-red-600 text-white',
                                        'error' => 'bg-red-500 text-white',
                                        'warning' => 'bg-yellow-400 text-gray-900',
                                        'notice' => 'bg-blue-500 text-white',
                                        'info' => 'bg-sky-500 text-white',
                                        'debug' => 'bg-gray-600 text-white',
                                    ];
                                    $badge = $levelColors[$entry['level'] ?? ''] ?? 'bg-gray-400 text-white';
                                @endphp
                                @if($entry['level'])
                                    <span class="inline-flex items-center px-2 py-1 rounded {{ $badge }} uppercase font-semibold">{{ $entry['level'] }}</span>
                                @endif
                                <span class="inline-flex items-center px-2 py-1 rounded bg-gray-100 text-gray-700 font-mono">{{ $entry['timestamp'] ?? 'Unknown time' }}</span>
                                @if(!empty($entry['environment']))
                                    <span class="inline-flex items-center px-2 py-1 rounded bg-gray-200 text-gray-800">{{ $entry['environment'] }}</span>
                                @endif
                            </div>

                            <div class="text-sm text-gray-900 leading-snug">
                                {{ $entry['message'] ?: Str::limit($entry['body'] ?? '', 160) }}
                            </div>

                            <details class="text-xs text-gray-700">
                                <summary class="cursor-pointer text-blue-600">View raw</summary>
                                <pre class="whitespace-pre-wrap text-xs leading-5 font-mono bg-gray-50 border border-gray-200 rounded-md p-2 mt-2">{{ $entry['body'] }}</pre>
                                @if(!empty($entry['stack']))
                                    <pre class="whitespace-pre-wrap text-xs leading-5 font-mono bg-gray-50 border border-gray-200 rounded-md p-2 mt-2">{{ $entry['stack'] }}</pre>
                                @endif
                            </details>
                        </div>
                    @empty
                        <div class="px-4 py-3 text-sm text-gray-600">No log entries found for the selected filters.</div>
                    @endforelse
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

