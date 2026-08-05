<?php

declare(strict_types=1);

namespace StarterKit\Modules;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
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

        $manifestPaths = $this->manifestPaths($rootPath);
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
                    throw new \InvalidArgumentException('Permission source tidak ditemukan.');
                }

                $configPath = dirname($manifestPath).DIRECTORY_SEPARATOR.$manifest->configSource;
                if (! is_file($configPath)) {
                    throw new \InvalidArgumentException('Config source tidak ditemukan.');
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

                    if ($identity->module !== $manifest->name) {
                        throw new \InvalidArgumentException('Permission module harus sama dengan nama module manifest.');
                    }

                    $identities['permission'][$key] = $manifestPath;
                }

                $modules[] = $manifest;
            } catch (Throwable $exception) {
                $diagnostics[] = [
                    'path' => $this->relativePath($rootPath, $manifestPath),
                    'message' => $this->safeMessage($exception->getMessage()),
                ];
            }
        }

        return compact('modules', 'diagnostics');
    }

    /** @return list<string> */
    private function manifestPaths(string $rootPath): array
    {
        if (! is_dir($rootPath)) {
            return [];
        }

        $paths = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getFilename() === 'module.json') {
                $paths[] = $file->getPathname();
            }
        }

        sort($paths);

        return array_values(array_unique($paths));
    }

    private function relativePath(string $rootPath, string $path): string
    {
        $root = realpath($rootPath) ?: $rootPath;
        $root = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $root), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        $normalizedPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);

        if (str_starts_with($normalizedPath, $root)) {
            return str_replace(DIRECTORY_SEPARATOR, '/', substr($normalizedPath, strlen($root)));
        }

        return basename($path);
    }

    private function safeMessage(string $message): string
    {
        if (str_contains($message, 'Duplicate module')) {
            return preg_replace('/\[[^\]]*\]/', '[duplicate]', $message) ?: 'Duplicate module identity.';
        }

        if (str_contains($message, 'Duplicate permission key')) {
            return preg_replace('/\[[^\]]*\]/', '[duplicate]', $message) ?: 'Duplicate permission key.';
        }

        if (str_contains($message, 'Permission source')) {
            return 'Permission source module tidak ditemukan atau tidak valid.';
        }

        if (str_contains($message, 'Config source')) {
            return 'Config source module tidak ditemukan atau tidak valid.';
        }

        if (str_contains($message, 'Permission module')) {
            return $message;
        }

        if (str_contains($message, 'Manifest field')
            || str_contains($message, 'Manifest ')
            || str_contains($message, 'Permission ')) {
            return $message;
        }

        return 'Manifest atau source module tidak valid.';
    }
}
