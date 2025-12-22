<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a database backup bundle and expose a latest copy for setup imports.';

    public function handle(): int
    {
        $connection = config('database.connections.mysql');
        $database = $connection['database'] ?? null;
        $username = $connection['username'] ?? null;
        $password = $connection['password'] ?? '';
        $host = $connection['host'] ?? '127.0.0.1';
        $port = $connection['port'] ?? '3306';

        if (empty($database) || empty($username)) {
            $this->error('DB_DATABASE and DB_USERNAME must be configured before running backups.');
            Log::error('backup:database failed because DB_DATABASE or DB_USERNAME is missing.');
            return Command::FAILURE;
        }

        $storagePath = storage_path('app/database_bundle.sql');
        $publicPath = public_path('database-backups/database_bundle_latest.sql');
        $cnfPath = storage_path('app/mysql-backup.cnf');

        try {
            File::ensureDirectoryExists(dirname($storagePath));
            File::ensureDirectoryExists(dirname($publicPath));

            Log::info('Starting database backup', [
                'database' => $database,
                'host' => $host,
                'port' => $port,
                'storage_path' => $storagePath,
                'public_path' => $publicPath,
            ]);

            $cnfContent = implode("\n", [
                '[client]',
                'user="' . $username . '"',
                'password="' . $password . '"',
                'host="' . $host . '"',
                'port="' . $port . '"',
                'default-character-set=utf8mb4',
                '',
            ]);

            File::put($cnfPath, $cnfContent);

            $process = new Process([
                'mysqldump',
                '--defaults-extra-file=' . $cnfPath,
                '--single-transaction',
                '--quick',
                '--routines',
                '--triggers',
                '--events',
                '--set-gtid-purged=OFF',
                '--result-file=' . $storagePath,
                $database,
            ]);

            $process->setTimeout(300);
            $process->run();

            if (! $process->isSuccessful()) {
                $error = trim($process->getErrorOutput()) ?: 'Unknown error';
                Log::error('Database backup failed', ['error' => $error]);
                $this->error('Database backup failed: ' . $error);
                return Command::FAILURE;
            }

            File::copy($storagePath, $publicPath);

            Log::info('Database backup completed', [
                'storage_path' => $storagePath,
                'public_path' => $publicPath,
                'size_bytes' => File::size($storagePath),
            ]);

            $this->info("Backup created at {$storagePath}");
            $this->info("Latest bundle copied to {$publicPath}");

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            Log::error('Database backup threw an exception', ['message' => $e->getMessage()]);
            $this->error('Database backup failed: ' . $e->getMessage());
            return Command::FAILURE;
        } finally {
            if (File::exists($cnfPath)) {
                File::delete($cnfPath);
            }
        }
    }
}

