@extends('layouts.app')

@section('title', 'System Logs')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">System Logs</h1>
            <p class="mt-2 text-sm text-gray-500">
                Viewer for <code class="bg-gray-100 px-1 py-0.5 rounded text-gray-700 font-mono text-xs">{{ $path }}</code>
                <span class="mx-2">&bull;</span>
                File Size: {{ $size_human }}
            </p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            <form action="{{ route('superadmin.logs.clear') }}" method="POST" onsubmit="return confirm('WARNING: This will permanently delete the current log file. Are you sure?');">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-red-300 shadow-sm text-sm font-medium rounded-lg text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <i class="fas fa-trash-alt mr-2"></i> Clear File
                </button>
            </form>
            <a href="{{ route('superadmin.logs.download') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i class="fas fa-download mr-2 text-gray-400"></i> Download
            </a>
            <a href="{{ route('superadmin.logs') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i class="fas fa-sync-alt mr-2"></i> Refresh
            </a>
        </div>
    </div>

    @if(session('status'))
        <div class="mb-6 rounded-md bg-green-50 p-4 border border-green-200">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('status') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(!$hasLogs)
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-8 text-center">
            <div class="mx-auto h-12 w-12 text-yellow-300 bg-yellow-100 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-exclamation-triangle text-xl"></i>
            </div>
            <h3 class="text-lg font-medium text-yellow-800 mb-2">No Log File Found</h3>
            <p class="text-sm text-yellow-600">The file <code>{{ $path }}</code> does not exist or has not been created yet.</p>
        </div>
    @else

    <!-- Filters -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6 p-5">
        <form method="GET" action="{{ route('superadmin.logs') }}">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div class="md:col-span-1">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Max Lines</label>
                    <select name="lines" class="block w-full border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @foreach([100, 200, 500, 1000, 2000] as $opt)
                            <option value="{{ $opt }}" {{ (int)$lines === $opt ? 'selected' : '' }}>{{ $opt }} lines</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-1">
                     <label class="block text-xs font-medium text-gray-500 mb-1">Log Level</label>
                     <select name="level" class="block w-full border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="all" {{ $level === 'all' ? 'selected' : '' }}>All Levels</option>
                        <option value="emergency" {{ $level === 'emergency' ? 'selected' : '' }}>Emergency</option>
                        <option value="alert" {{ $level === 'alert' ? 'selected' : '' }}>Alert</option>
                        <option value="critical" {{ $level === 'critical' ? 'selected' : '' }}>Critical</option>
                        <option value="error" {{ $level === 'error' ? 'selected' : '' }}>Error</option>
                        <option value="warning" {{ $level === 'warning' ? 'selected' : '' }}>Warning</option>
                        <option value="notice" {{ $level === 'notice' ? 'selected' : '' }}>Notice</option>
                        <option value="info" {{ $level === 'info' ? 'selected' : '' }}>Info</option>
                        <option value="debug" {{ $level === 'debug' ? 'selected' : '' }}>Debug</option>
                     </select>
                </div>
                <div class="md:col-span-2">
                     <label class="block text-xs font-medium text-gray-500 mb-1">Search Message</label>
                     <div class="flex space-x-2">
                         <input type="text" name="search" value="{{ $search }}" class="block w-full border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g. 'SQL' or 'Method not found'">
                         <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">Filter</button>
                     </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900">
                Log Entries 
                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                    {{ count($entries) }} shown
                </span>
            </h3>
            <span class="text-xs text-gray-500">Showing newest first</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-40">Level / Time</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($entries as $index => $entry)
                        @php
                            $levelColor = match($entry['level']) {
                                'emergency', 'alert', 'critical', 'error' => 'bg-red-100 text-red-800 border-red-200',
                                'warning' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                'notice', 'info' => 'bg-blue-100 text-blue-800 border-blue-200',
                                'debug' => 'bg-gray-100 text-gray-800 border-gray-200',
                                default => 'bg-gray-100 text-gray-800 border-gray-200'
                            };
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 align-top whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $levelColor }} uppercase">
                                    {{ $entry['level'] }}
                                </span>
                                <div class="mt-2 text-xs text-gray-500 font-mono">
                                    {{ $entry['timestamp'] }}
                                </div>
                                <div class="mt-1 text-xs text-gray-400 font-mono">
                                    {{ $entry['environment'] }}
                                </div>
                            </td>
                            <td class="px-6 py-4 align-top">
                                <div class="text-sm text-gray-900 font-medium break-all whitespace-pre-wrap font-mono">{{ Str::limit($entry['message'], 300) }}</div>
                                
                                @if(!empty($entry['stack']) || strlen($entry['message']) > 300)
                                    <div class="mt-3">
                                        <button onclick="document.getElementById('stack-{{ $index }}').classList.toggle('hidden')" 
                                                class="text-xs font-medium text-indigo-600 hover:text-indigo-800 inline-flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                            View Details & Stack Trace
                                        </button>
                                        <div id="stack-{{ $index }}" class="hidden mt-2 p-4 bg-gray-50 rounded-lg border border-gray-200 overflow-x-auto">
                                            @if(strlen($entry['message']) > 300)
                                                <div class="mb-3">
                                                    <div class="text-xs font-bold text-gray-500 uppercase mb-1">Full Message</div>
                                                    <pre class="text-xs text-gray-800 whitespace-pre-wrap font-mono">{{ $entry['message'] }}</pre>
                                                </div>
                                            @endif
                                            
                                            @if(!empty($entry['stack']))
                                                <div>
                                                    <div class="text-xs font-bold text-gray-500 uppercase mb-1">Stack Trace</div>
                                                    <pre class="text-xs text-red-600 whitespace-pre font-mono leading-snug">{{ $entry['stack'] }}</pre>
                                                </div>
                                            @endif

                                            @if(!empty($entry['body']) && empty($entry['stack']))
                                                <div>
                                                    <div class="text-xs font-bold text-gray-500 uppercase mb-1">Context</div>
                                                    <pre class="text-xs text-gray-600 whitespace-pre-wrap font-mono">{{ $entry['body'] }}</pre>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-6 py-12 text-center text-gray-500">
                                <div class="mx-auto h-12 w-12 text-gray-300 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                                    <i class="fas fa-check text-xl"></i>
                                </div>
                                <p>No logs found matching your criteria.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
