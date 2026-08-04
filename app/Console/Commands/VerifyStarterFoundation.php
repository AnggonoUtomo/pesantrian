<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Throwable;

final class VerifyStarterFoundation extends Command
{
    protected $signature = 'starter:verify {--json : Tampilkan hasil sebagai JSON}';

    protected $description = 'Memeriksa fondasi starter kit dan service runtime.';

    /** @var array<string, array{status: string, message: string}> */
    private array $checks = [];

    public function handle(): int
    {
        $this->check('php', version_compare(PHP_VERSION, '8.4.0', '>='), 'PHP '.PHP_VERSION);
        $this->check('laravel', version_compare((string) app()->version(), '13.0.0', '>='), 'Laravel '.app()->version());
        $this->check('pgsql_extension', extension_loaded('pdo_pgsql'), 'Extension pdo_pgsql tersedia');
        $this->check('redis_client', class_exists('Predis\\Client'), 'Predis tersedia');
        $this->check('ziggy', class_exists('Tighten\\Ziggy\\Ziggy'), 'Ziggy Laravel tersedia');
        $this->check('permission', class_exists('Spatie\\Permission\\Models\\Permission'), 'Spatie Permission tersedia');
        $this->checkDatabase();
        $this->checkRedis();
        $this->checkStorage();
        $this->checkUlid();
        $this->checkForbiddenDependencies();

        $failed = collect($this->checks)->where('status', 'failed')->count();
        $payload = [
            'success' => $failed === 0,
            'code' => $failed === 0 ? 'STARTER_VERIFIED' : 'STARTER_VERIFICATION_FAILED',
            'message' => $failed === 0 ? 'Starter foundation lulus.' : 'Starter foundation memiliki pemeriksaan yang gagal.',
            'data' => ['checks' => $this->checks, 'failed' => $failed],
        ];

        if ($this->option('json')) {
            $this->line((string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            foreach ($this->checks as $name => $check) {
                $style = $check['status'] === 'passed' ? 'info' : 'error';
                $this->{$style}($name.': '.$check['message']);
            }
            $this->newLine();
            $this->{$failed === 0 ? 'info' : 'error'}($payload['message']);
        }

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function check(string $name, bool $passed, string $message): void
    {
        $this->checks[$name] = [
            'status' => $passed ? 'passed' : 'failed',
            'message' => $message,
        ];
    }

    private function checkDatabase(): void
    {
        try {
            DB::connection()->getPdo();
            $driver = (string) config('database.default');
            $this->check('database', true, 'Koneksi aktif dengan driver '.$driver);
        } catch (Throwable $exception) {
            $this->check('database', false, 'Koneksi database gagal: '.Str::before($exception->getMessage(), ' (SQL:'));
        }
    }

    private function checkRedis(): void
    {
        try {
            $response = Redis::connection()->ping();
            $this->check('redis', $response !== false, 'Redis merespons PONG');
        } catch (Throwable $exception) {
            $this->check('redis', false, 'Koneksi Redis gagal: '.Str::before($exception->getMessage(), ' ('));
        }
    }

    private function checkStorage(): void
    {
        $link = public_path('storage');
        $this->check('storage', file_exists($link), 'Storage public tersedia');
    }

    private function checkUlid(): void
    {
        $value = (string) Str::ulid();
        $this->check('ulid', (bool) preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/', $value), 'ULID dapat dibuat');
    }

    private function checkForbiddenDependencies(): void
    {
        $files = [base_path('composer.json'), base_path('package.json')];
        foreach ([app_path(), config_path(), database_path(), resource_path(), base_path('routes')] as $directory) {
            if (is_dir($directory)) {
                $files = array_merge($files, glob($directory.'/**/*.php') ?: []);
            }
        }

        $found = [];
        foreach ($files as $file) {
            if (! is_file($file)) {
                continue;
            }
            $contents = strtolower((string) file_get_contents($file));
            if (str_contains($contents, 'wayfinder') || str_contains($contents, 'laravel boost') || str_contains($contents, 'laravel-boost')) {
                $found[] = basename($file);
            }
        }

        $this->check('forbidden_dependencies', $found === [], $found === [] ? 'Tidak ditemukan' : 'Ditemukan pada: '.implode(', ', $found));
    }
}
