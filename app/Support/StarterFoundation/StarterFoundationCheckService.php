<?php

declare(strict_types=1);

namespace App\Support\StarterFoundation;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use StarterKit\Modules\ModuleRegistry;
use Throwable;

final class StarterFoundationCheckService
{
    public function __construct(
        private readonly ForbiddenDependencyScanner $scanner,
        private readonly ModuleRegistry $modules,
    ) {}

    /** @return array{success: bool, code: string, message: string, data: array<string, mixed>} */
    public function verify(): array
    {
        /** @var array<string, array{status: string, message: string}> $checks */
        $checks = [];
        $this->check($checks, 'php', version_compare(PHP_VERSION, '8.4.0', '>='), 'PHP '.PHP_VERSION);
        $this->check($checks, 'laravel', version_compare((string) app()->version(), '13.0.0', '>='), 'Laravel '.app()->version());
        $this->check($checks, 'mysql_extension', extension_loaded('pdo_mysql'), 'Extension pdo_mysql tersedia');
        $databaseDriver = config('database.default');
        $testingDatabase = app()->environment('testing') && $databaseDriver === 'sqlite';
        $this->check(
            $checks,
            'mysql_driver',
            $databaseDriver === 'mysql' || $testingDatabase,
            $testingDatabase ? 'Testing memakai SQLite in-memory; extension MySQL tersedia' : 'Driver database MySQL aktif',
        );
        $this->check($checks, 'redis_client', class_exists('Predis\\Client'), 'Predis tersedia');
        $this->check($checks, 'ziggy', class_exists('Tighten\\Ziggy\\Ziggy'), 'Ziggy Laravel tersedia');
        $this->check($checks, 'permission', class_exists('Spatie\\Permission\\Models\\Permission'), 'Spatie Permission tersedia');
        $this->checkRuntime($checks);
        $this->checkFrontend($checks);
        $this->checkApprovedPackages($checks);
        $this->checkForbiddenDependencies($checks);

        return $this->report($checks, 'STARTER_VERIFIED', 'Starter foundation lulus.');
    }

    /** @return array{success: bool, code: string, message: string, data: array<string, mixed>} */
    public function diagnose(): array
    {
        $report = $this->verify();
        $discovery = $this->modules->discover(app_path('Modules'));

        $report['data']['environment'] = [
            'php' => PHP_VERSION,
            'laravel' => app()->version(),
            'database_driver' => config('database.default'),
            'cache_store' => config('cache.default'),
            'queue_connection' => config('queue.default'),
            'session_driver' => config('session.driver'),
            'redis_client' => config('database.redis.client'),
        ];
        $report['data']['modules'] = array_map(
            static fn (object $module): array => [
                'domain' => $module->domain,
                'name' => $module->name,
                'status' => $module->status,
            ],
            $discovery['modules'],
        );
        $report['data']['module_diagnostics'] = count($discovery['diagnostics']);
        $report['code'] = $report['success'] ? 'STARTER_DIAGNOSED' : 'STARTER_DIAGNOSIS_FAILED';
        $report['message'] = $report['success'] ? 'Diagnosis foundation lulus.' : 'Diagnosis foundation menemukan masalah.';

        return $report;
    }

    /** @return array{success: bool, code: string, message: string, data: array<string, mixed>} */
    public function health(): array
    {
        /** @var array<string, array{status: string, message: string}> $checks */
        $checks = [];
        $this->checkRuntime($checks);
        $this->check($checks, 'queue_configured', is_string(config('queue.default')) && config('queue.default') !== '', 'Queue terkonfigurasi');
        $this->check($checks, 'session_configured', is_string(config('session.driver')) && config('session.driver') !== '', 'Session terkonfigurasi');

        return $this->report($checks, 'STARTER_HEALTHY', 'Health foundation lulus.');
    }

    /** @param array<string, array{status: string, message: string}> $checks */
    private function checkRuntime(array &$checks): void
    {
        try {
            DB::connection()->getPdo();
            $this->check($checks, 'database', true, 'Koneksi database aktif');
        } catch (Throwable) {
            $this->check($checks, 'database', false, 'Koneksi database gagal; periksa konfigurasi dan service database.');
        }

        try {
            $response = Redis::connection()->ping();
            $this->check($checks, 'redis', $response !== false, 'Redis merespons PONG');
        } catch (Throwable) {
            $this->check($checks, 'redis', false, 'Koneksi Redis gagal; periksa konfigurasi dan service Redis.');
        }

        $cacheKey = 'starter-foundation-check-'.Str::ulid();

        try {
            $cache = Cache::store();
            $cache->put($cacheKey, 'ok', 10);
            $this->check($checks, 'cache', $cache->get($cacheKey) === 'ok', 'Cache round-trip berhasil');
            $cache->forget($cacheKey);
        } catch (Throwable) {
            $this->check($checks, 'cache', false, 'Cache round-trip gagal; periksa konfigurasi cache.');
        }

        $this->check($checks, 'storage', file_exists(public_path('storage')), 'Storage public tersedia');
        $this->check($checks, 'ulid', preg_match('/^[0-9A-HJKMNP-TV-Z]{26}$/', (string) Str::ulid()) === 1, 'ULID dapat dibuat');
    }

    /** @param array<string, array{status: string, message: string}> $checks */
    private function checkFrontend(array &$checks): void
    {
        $package = $this->readJson(base_path('package.json'));
        $dependencies = array_merge($package['dependencies'] ?? [], $package['devDependencies'] ?? []);

        foreach ([
            '@inertiajs/react' => 'Inertia React tersedia',
            'react' => 'React tersedia',
            'typescript' => 'TypeScript tersedia',
            'vite' => 'Vite tersedia',
            'tailwindcss' => 'Tailwind tersedia',
        ] as $dependency => $message) {
            $name = match ($dependency) {
                '@inertiajs/react' => 'inertia_react',
                default => str_replace(['@', '/', '-'], '_', $dependency),
            };
            $this->check($checks, $name, isset($dependencies[$dependency]), $message);
        }

        $this->check($checks, 'shadcn_ui', is_dir(resource_path('js/components/ui')), 'shadcn/ui tersedia');
        $this->check($checks, 'frontend_entry', is_file(resource_path('js/app.tsx')), 'Entry frontend tersedia');
        $this->check($checks, 'vite_config', is_file(base_path('vite.config.ts')), 'Konfigurasi Vite tersedia');
    }

    /** @param array<string, array{status: string, message: string}> $checks */
    private function checkApprovedPackages(array &$checks): void
    {
        $composer = $this->readJson(base_path('composer.json'));
        $packages = array_merge($composer['require'] ?? [], $composer['require-dev'] ?? []);
        $required = [
            'laravel/framework',
            'starterkit/framework',
            'tightenco/ziggy',
            'spatie/laravel-permission',
            'predis/predis',
        ];

        foreach ($required as $package) {
            $this->check($checks, 'composer_'.str_replace(['/', '-'], '_', $package), isset($packages[$package]), $package.' tersedia');
        }
    }

    /** @param array<string, array{status: string, message: string}> $checks */
    private function checkForbiddenDependencies(array &$checks): void
    {
        $paths = [base_path('composer.json'), base_path('package.json'), app_path(), config_path(), database_path(), resource_path(), base_path('routes')];
        $found = $this->scanner->scan($paths, [__DIR__.'/ForbiddenDependencyScanner.php']);
        $this->check($checks, 'forbidden_dependencies', $found === [], $found === [] ? 'Tidak ditemukan' : 'Forbidden dependency ditemukan pada source.');
    }

    /** @return array<string, mixed> */
    private function readJson(string $path): array
    {
        $contents = file_get_contents($path);
        $data = is_string($contents) ? json_decode($contents, true) : null;

        return is_array($data) ? $data : [];
    }

    /** @param array<string, array{status: string, message: string}> $checks */
    private function check(array &$checks, string $name, bool $passed, string $message): void
    {
        $checks[$name] = [
            'status' => $passed ? 'passed' : 'failed',
            'message' => $message,
        ];
    }

    /**
     * @param  array<string, array{status: string, message: string}>  $checks
     * @return array{success: bool, code: string, message: string, data: array<string, mixed>}
     */
    private function report(array $checks, string $code, string $message): array
    {
        $failed = count(array_filter($checks, static fn (array $check): bool => $check['status'] === 'failed'));

        return [
            'success' => $failed === 0,
            'code' => $failed === 0 ? $code : str_replace(['VERIFIED', 'HEALTHY'], ['VERIFICATION_FAILED', 'UNHEALTHY'], $code),
            'message' => $failed === 0 ? $message : 'Foundation memiliki pemeriksaan yang gagal.',
            'data' => [
                'checks' => $checks,
                'failed' => $failed,
            ],
        ];
    }
}
