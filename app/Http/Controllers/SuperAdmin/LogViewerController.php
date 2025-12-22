<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class LogViewerController extends Controller
{
    public function index(Request $request)
    {
        $path = storage_path('logs/laravel.log');
        $hasLogs = File::exists($path);
        $entries = [];
        $error = null;
        $level = (string) ($request->input('level', 'all') ?? 'all');
        $search = (string) ($request->input('search', '') ?? '');
        $linesToShow = (int) ($request->input('lines', 500) ?? 500);
        $linesToShow = max(100, min(10000, $linesToShow)); // bound between 100 and 10k
        $fileSize = $hasLogs ? File::size($path) : 0;

        if ($hasLogs) {
            try {
                Log::info('Log viewer accessed', [
                    'level' => $level,
                    'search' => $search,
                    'lines' => $linesToShow,
                    'file_size' => $fileSize,
                ]);

                $contents = File::get($path);
                $lines = collect(preg_split('/\r\n|\n|\r/', $contents));

                // take last N lines
                if ($lines->count() > $linesToShow) {
                    $lines = $lines->slice(-1 * $linesToShow)->values();
                }

                $parsed = $this->parseEntries($lines);

                // filter by level/search
                $filtered = array_filter($parsed, function ($entry) use ($level, $search) {
                    if ($level !== 'all' && ($entry['level'] ?? '') !== $level) {
                        return false;
                    }
                    if ($search !== '') {
                        $haystack = strtolower(($entry['message'] ?? '') . ' ' . ($entry['stack'] ?? '') . ' ' . ($entry['body'] ?? ''));
                        if (str_contains($haystack, strtolower($search)) === false) {
                            return false;
                        }
                    }
                    return true;
                });

                // newest first
                $entries = array_reverse(array_values($filtered));
            } catch (\Throwable $e) {
                $error = 'Failed to read log file: ' . $e->getMessage();
                Log::error('Failed to read Laravel log file', ['error' => $e->getMessage()]);
            }
        }

        return view('superadmin.logs', [
            'entries' => $entries,
            'hasLogs' => $hasLogs,
            'path' => $path,
            'error' => $error,
            'level' => $level,
            'search' => $search,
            'lines' => $linesToShow,
            'size' => $fileSize,
            'size_human' => $this->formatBytes($fileSize),
        ]);
    }

    public function download()
    {
        $path = storage_path('logs/laravel.log');

        if (!File::exists($path)) {
            abort(404, 'Log file not found.');
        }

        return response()->download($path, 'laravel.log');
    }

    /**
     * Parse log lines into structured entries keyed by timestamp/level.
     *
     * @param \Illuminate\Support\Collection<int, string> $lines
     * @return array<int, array{timestamp:?string, environment:?string, level:?string, message:string, stack:string, body:string}>
     */
    private function parseEntries(Collection $lines): array
    {
        $entries = [];
        $current = [
            'timestamp' => null,
            'level' => null,
            'environment' => null,
            'message' => '',
            'stack' => '',
            'lines' => [],
        ];

        foreach ($lines as $line) {
            // Laravel default: [YYYY-MM-DD HH:MM:SS] local.LEVEL: message
            if (preg_match('/^\[(?<ts>\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]\s+(?<env>[^\s]+)\.(?<level>[A-Z]+):\s*(?<msg>.*)$/', $line, $matches)) {
                // Save previous entry
                if (!empty($current['lines'])) {
                    $entries[] = [
                        'timestamp' => $current['timestamp'],
                        'level' => $current['level'],
                        'environment' => $current['environment'],
                        'message' => $current['message'],
                        'stack' => $current['stack'],
                        'body' => implode(PHP_EOL, $current['lines']),
                    ];
                }

                $current = [
                    'timestamp' => $matches['ts'] ?? null,
                    'environment' => $matches['env'] ?? null,
                    'level' => strtolower($matches['level'] ?? ''),
                    'message' => $matches['msg'] ?? '',
                    'stack' => '',
                    'lines' => [$line],
                ];
            } else {
                $current['lines'][] = $line;
                // Append to stack/message body
                if (trim($line) !== '') {
                    $current['stack'] .= ($current['stack'] === '' ? '' : PHP_EOL) . $line;
                }
            }
        }

        // Push the last entry
        if (!empty($current['lines'])) {
            $entries[] = [
                'timestamp' => $current['timestamp'],
                'level' => $current['level'],
                'environment' => $current['environment'],
                'message' => $current['message'],
                'stack' => $current['stack'],
                'body' => implode(PHP_EOL, $current['lines']),
            ];
        }

        return $entries;
    }

    public function clear()
    {
        $path = storage_path('logs/laravel.log');

        try {
            if (File::exists($path)) {
                File::put($path, '');
                Log::info('Laravel log cleared by superadmin');
                return redirect()->route('superadmin.logs')->with('status', 'Log file cleared successfully.');
            }
            return redirect()->route('superadmin.logs')->with('error', 'Log file does not exist.');
        } catch (\Throwable $e) {
            Log::error('Failed to clear log file', ['error' => $e->getMessage()]);
            return redirect()->route('superadmin.logs')->with('error', 'Failed to clear log file: ' . $e->getMessage());
        }
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = (int) floor(log($bytes, 1024));
        return round($bytes / (1024 ** $i), $precision) . ' ' . $units[$i];
    }
}

