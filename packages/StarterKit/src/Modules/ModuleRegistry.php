<?php

declare(strict_types=1);

namespace StarterKit\Modules;

use StarterKit\Modules\Contracts\ModuleManifest;
use StarterKit\Modules\Contracts\PermissionIdentity;
use Throwable;

final class ModuleRegistry
{
    /** @return array{modules: list<ModuleManifest>, diagnostics: list<array{path: string, message: string}>} */
    public function discover(string $rootPath): array
    {
        $modules = [];
        $diagnostics = [];

        if (! is_dir($rootPath)) {
            return compact('modules', 'diagnostics');
        }

        $manifestPaths = glob($rootPath.'/*/*/module.json') ?: [];
        $identities = [];

        foreach ($manifestPaths as $manifestPath) {
            try {
                $contents = file_get_contents($manifestPath);
                $data = is_string($contents) ? json_decode($contents, true, 512, JSON_THROW_ON_ERROR) : null;

                if (! is_array($data)) {
                    throw new \InvalidArgumentException('Manifest JSON must contain an object.');
                }

                $manifest = ModuleManifest::fromArray($data);

                foreach (['name', 'path', 'namespace', 'provider'] as $identity) {
                    $value = $manifest->{$identity};

                    if (isset($identities[$identity][$value])) {
                        throw new \InvalidArgumentException("Duplicate module {$identity} [$value].");
                    }

                    $identities[$identity][$value] = $manifestPath;
                }

                $permissionPath = dirname($manifestPath).DIRECTORY_SEPARATOR.$manifest->permissionSource;
                if (! is_file($permissionPath)) {
                    throw new \InvalidArgumentException("Permission source [$permissionPath] tidak ditemukan.");
                }

                $permissions = require $permissionPath;
                if (! is_array($permissions)) {
                    throw new \InvalidArgumentException('Permission source harus mengembalikan array.');
                }

                foreach ($permissions as $permission) {
                    $identity = PermissionIdentity::fromArray($permission);
                    $key = $identity->key;

                    if (isset($identities['permission'][$key])) {
                        throw new \InvalidArgumentException("Duplicate permission key [$key].");
                    }

                    $identities['permission'][$key] = $manifestPath;
                }

                $modules[] = $manifest;
            } catch (Throwable $exception) {
                $diagnostics[] = [
                    'path' => $manifestPath,
                    'message' => $exception->getMessage(),
                ];
            }
        }

        return compact('modules', 'diagnostics');
    }
}
