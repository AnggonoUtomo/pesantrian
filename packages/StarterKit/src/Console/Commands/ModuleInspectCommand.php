<?php

declare(strict_types=1);

namespace StarterKit\Console\Commands;

use Illuminate\Console\Command;
use StarterKit\Modules\Contracts\ModuleManifest;
use StarterKit\Modules\Contracts\PermissionIdentity;
use StarterKit\Modules\ModuleRegistry;
use Throwable;

final class ModuleInspectCommand extends Command
{
    protected $signature = 'module:inspect
        {module : Target module dalam format Domain/Module}
        {--json : Tampilkan hasil sebagai JSON}';

    protected $description = 'Membaca detail module tanpa mengubah file.';

    public function handle(ModuleRegistry $registry): int
    {
        $target = trim((string) $this->argument('module'), '/');

        if (preg_match('#^[A-Z][A-Za-z0-9]*/[A-Z][A-Za-z0-9]*$#', $target) !== 1) {
            return $this->respond([
                'success' => false,
                'code' => 'MODULE_INSPECTION_FAILED',
                'message' => 'Target module harus menggunakan format Domain/Module.',
                'data' => [],
                'diagnostics' => [],
            ]);
        }

        $result = $registry->discover(app_path('Modules'));
        [$domain, $name] = explode('/', $target, 2);

        foreach ($result['modules'] as $manifest) {
            if ($manifest->domain !== $domain || $manifest->name !== $name) {
                continue;
            }

            try {
                return $this->respond([
                    'success' => true,
                    'code' => 'MODULE_INSPECTED',
                    'message' => 'Detail module berhasil dibaca.',
                    'data' => ['module' => $this->moduleData($manifest)],
                    'diagnostics' => $result['diagnostics'],
                ]);
            } catch (Throwable $exception) {
                return $this->respond([
                    'success' => false,
                    'code' => 'MODULE_INSPECTION_FAILED',
                    'message' => 'Detail module tidak dapat dibaca.',
                    'data' => [],
                    'diagnostics' => [[
                        'path' => $manifest->path,
                        'message' => $exception->getMessage(),
                    ]],
                ]);
            }
        }

        return $this->respond([
            'success' => false,
            'code' => 'MODULE_NOT_FOUND',
            'message' => "Module [$target] tidak ditemukan.",
            'data' => ['target' => $target],
            'diagnostics' => $result['diagnostics'],
        ]);
    }

    /** @return array<string, mixed> */
    private function moduleData(ModuleManifest $manifest): array
    {
        $modulePath = base_path($manifest->path);
        $permissionPath = $modulePath.DIRECTORY_SEPARATOR.$manifest->permissionSource;
        $permissions = require $permissionPath;

        $permissionIdentities = array_map(static function (mixed $permission): array {
            if (! is_array($permission)) {
                throw new \InvalidArgumentException('Permission identity harus berupa object/array.');
            }

            return get_object_vars(PermissionIdentity::fromArray($permission));
        }, is_array($permissions) ? $permissions : []);

        return [
            'name' => $manifest->name,
            'domain' => $manifest->domain,
            'namespace' => $manifest->namespace,
            'path' => $manifest->path,
            'version' => $manifest->version,
            'schema_version' => $manifest->schemaVersion,
            'status' => $manifest->status,
            'provider' => $manifest->provider,
            'dependencies' => $manifest->dependencies,
            'permission_source' => $manifest->permissionSource,
            'config_source' => $manifest->configSource,
            'permissions' => $permissionIdentities,
        ];
    }

    /** @param array<string, mixed> $payload */
    private function respond(array $payload): int
    {
        if ($this->option('json')) {
            $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        } elseif ($payload['success']) {
            $module = $payload['data']['module'];
            $this->info((string) $payload['message']);
            $this->line('Module: '.$module['domain'].'/'.$module['name']);
            $this->line('Path: '.$module['path']);
            $this->line('Status: '.$module['status']);
        } else {
            $this->error((string) $payload['message']);
            foreach ($payload['diagnostics'] as $diagnostic) {
                $this->error(($diagnostic['path'] ?? 'module').': '.($diagnostic['message'] ?? 'Unknown diagnostic'));
            }
        }

        return $payload['success'] ? self::SUCCESS : self::FAILURE;
    }
}
