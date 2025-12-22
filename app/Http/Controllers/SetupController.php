<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\Process\Process;

class SetupController extends Controller
{
    public function show()
    {
        return view('setup');
    }

    public function csrfToken(Request $request)
    {
        $request->session()->regenerateToken();

        return response()->json([
            'token' => csrf_token(),
        ]);
    }

    public function testConnection(Request $request)
    {
        $config = $this->validateDbConfig($request);

        try {
            Log::info('Setup test connection requested', [
                'host' => $config['db_host'],
                'port' => $config['db_port'],
                'database' => $config['db_database'],
                'username' => $config['db_username'],
            ]);

            $this->configureTempConnection($config)->getPdo();

            return response()->json([
                'success' => true,
                'message' => 'Database connection successful.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Setup DB connection failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } finally {
            DB::purge('setup_temp');
        }
    }

    public function import(Request $request)
    {
        $config = $this->validateDbConfig($request);

        $envUpdated = $this->updateEnvFile($config);
        $connectionConfig = $this->connectionConfig($config);

        config([
            'database.default' => 'mysql',
            'database.connections.mysql' => $connectionConfig,
        ]);

        DB::purge('mysql');

        try {
            Log::info('Setup import started', [
                'host' => $config['db_host'],
                'port' => $config['db_port'],
                'database' => $config['db_database'],
                'username' => $config['db_username'],
                'env_updated' => $envUpdated,
            ]);

            $bundlePath = $this->resolveBundlePath();
            $this->runMysqlImport($connectionConfig, $bundlePath);
            $this->ensureSessionsTable();

            $message = 'Database imported successfully.';
            if (! $envUpdated) {
                $message .= ' .env was not updated; please set DB_* values manually.';
            }

            Log::info('Setup import completed', [
                'database' => $config['db_database'],
                'bundle_path' => $bundlePath,
                'env_updated' => $envUpdated,
            ]);

            return response()->json([
                'success' => true,
                'message' => $message,
                'show_admin_registration' => true,
            ]);
        } catch (\Throwable $e) {
            Log::error('Setup import failed', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function registerAdmin(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'superadmin',
        ]);

        Auth::login($user);

        Log::info('SuperAdmin created via setup', ['user_id' => $user->id, 'email' => $user->email]);

        return response()->json([
            'success' => true,
            'redirect' => route('dashboard.superadmin'),
        ]);
    }

    private function validateDbConfig(Request $request): array
    {
        $validated = $request->validate([
            'db_host' => ['required', 'string'],
            'db_port' => ['nullable', 'integer'],
            'db_database' => ['required', 'string'],
            'db_username' => ['required', 'string'],
            'db_password' => ['nullable', 'string'],
        ]);

        $validated['db_port'] = $validated['db_port'] ?? 3306;
        $validated['db_password'] = $validated['db_password'] ?? '';

        return $validated;
    }

    private function configureTempConnection(array $config)
    {
        $connectionConfig = $this->connectionConfig($config);

        config(['database.connections.setup_temp' => $connectionConfig]);
        DB::purge('setup_temp');

        return DB::connection('setup_temp');
    }

    private function connectionConfig(array $config): array
    {
        return [
            'driver' => 'mysql',
            'host' => $config['db_host'],
            'port' => (string) $config['db_port'],
            'database' => $config['db_database'],
            'username' => $config['db_username'],
            'password' => $config['db_password'],
            'unix_socket' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([]) : [],
        ];
    }

    private function updateEnvFile(array $config): bool
    {
        $envPath = base_path('.env');
        $contents = File::exists($envPath) ? File::get($envPath) : '';

        // If the file or directory is not writable, log and skip without failing the import.
        $dir = dirname($envPath);
        if ((File::exists($envPath) && ! File::isWritable($envPath)) || (! File::exists($envPath) && ! File::isWritable($dir))) {
            Log::warning('.env not writable during setup import; skipping env update.');
            return false;
        }

        $pairs = [
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $config['db_host'],
            'DB_PORT' => $config['db_port'],
            'DB_DATABASE' => $config['db_database'],
            'DB_USERNAME' => $config['db_username'],
            'DB_PASSWORD' => $config['db_password'],
        ];

        foreach ($pairs as $key => $value) {
            $line = $key . '=' . $value;
            if (preg_match("/^{$key}=.*$/m", $contents)) {
                $contents = preg_replace("/^{$key}=.*$/m", $line, $contents);
            } else {
                $contents .= PHP_EOL . $line;
            }
        }

        try {
            File::put($envPath, $contents);
            return true;
        } catch (\Throwable $e) {
            Log::warning('.env update failed during setup import', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function resolveBundlePath(): string
    {
        $remoteUrl = env('DB_BUNDLE_URL');

        if (! empty($remoteUrl)) {
            try {
                Log::info('Attempting remote DB bundle download', ['url' => $remoteUrl]);
                return $this->downloadBundle($remoteUrl);
            } catch (\Throwable $e) {
                Log::warning('Remote DB bundle download failed, falling back to local copy', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $localPath = public_path('database-backups/database_bundle_latest.sql');

        if (! File::exists($localPath)) {
            throw new \RuntimeException('No database bundle available. Please run php artisan backup:database first.');
        }

        Log::info('Using local DB bundle copy', ['path' => $localPath]);

        return $localPath;
    }

    private function downloadBundle(string $url): string
    {
        $destination = storage_path('app/setup_import.sql');

        $response = Http::withOptions(['stream' => true])
            ->timeout(300)
            ->get($url);

        if (! $response->successful()) {
            throw new \RuntimeException('Failed to download database bundle from remote source.');
        }

        $stream = $response->toPsrResponse()->getBody();
        $handle = fopen($destination, 'w');

        while (! $stream->eof()) {
            fwrite($handle, $stream->read(1024 * 1024));
        }

        fclose($handle);

        return $destination;
    }

    private function runMysqlImport(array $config, string $bundlePath): void
    {
        $cnfPath = storage_path('app/mysql-import.cnf');

        $cnfContent = implode("\n", [
            '[client]',
            'user="' . $config['username'] . '"',
            'password="' . $config['password'] . '"',
            'host="' . $config['host'] . '"',
            'port="' . $config['port'] . '"',
            'default-character-set=utf8mb4',
            '',
        ]);

        File::put($cnfPath, $cnfContent);

        try {
            $process = new Process([
                'mysql',
                '--defaults-extra-file=' . $cnfPath,
                '--host=' . $config['host'],
                '--port=' . $config['port'],
                '--default-character-set=utf8mb4',
                $config['database'],
                '-e',
                'source ' . $bundlePath,
            ]);

            $process->setTimeout(300);
            $process->run();

            if (! $process->isSuccessful()) {
                $error = trim($process->getErrorOutput()) ?: 'Unknown error';
                throw new \RuntimeException('MySQL import failed: ' . $error);
            }
        } finally {
            if (File::exists($cnfPath)) {
                File::delete($cnfPath);
            }
        }
    }

    private function ensureSessionsTable(): void
    {
        $exists = DB::select("SHOW TABLES LIKE 'sessions'");

        if (empty($exists)) {
            Artisan::call('session:table');
            Artisan::call('migrate', ['--force' => true]);
        }
    }
}

