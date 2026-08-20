<?php

declare(strict_types=1);

namespace StarterKit\Modules;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use StarterKit\Modules\Contracts\ModuleManifest;

final class ModuleDependencyValidator
{
    /**
     * @param  list<ModuleManifest>  $modules
     * @return list<array{code: string, module: string, phase: string, path: string, message: string}>
     */
    public function validate(string $rootPath, array $modules): array
    {
        /** @var array<string, ModuleManifest> $byName */
        $byName = [];

        foreach ($modules as $module) {
            $byName[$module->name] = $module;
        }

        $diagnostics = [];
        $seen = [];

        foreach ($modules as $owner) {
            $modulePath = rtrim($rootPath, '/\\').DIRECTORY_SEPARATOR
                .$owner->domain.DIRECTORY_SEPARATOR.$owner->name;

            foreach ($this->phpFiles($modulePath) as $file) {
                $source = file_get_contents($file);

                if (! is_string($source)) {
                    continue;
                }

                foreach ($this->moduleReferences($source) as $reference) {
                    $target = $reference['module'];

                    if ($target === $owner->name || ! isset($byName[$target])) {
                        continue;
                    }

                    $relativePath = $owner->domain.'/'.$owner->name.'/'.$this->relativePath($modulePath, $file);
                    $deduplicationKey = $owner->name.'|'.$target.'|'.$relativePath.'|'.$reference['boundary'];

                    if (isset($seen[$deduplicationKey])) {
                        continue;
                    }

                    $seen[$deduplicationKey] = true;

                    if (! in_array($target, $owner->dependencies, true)) {
                        $diagnostics[] = $this->diagnostic(
                            $owner,
                            $relativePath,
                            'dependency_undeclared',
                            'Import lintas module belum dideklarasikan pada manifest.',
                        );

                        continue;
                    }

                    if (! $this->isPublicBoundary($reference['boundary'])) {
                        $diagnostics[] = $this->diagnostic(
                            $owner,
                            $relativePath,
                            'dependency_private',
                            'Import lintas module mengakses boundary private.',
                        );
                    }
                }
            }
        }

        usort($diagnostics, static fn (array $left, array $right): int => [
            $left['module'],
            $left['path'],
            $left['code'],
        ] <=> [
            $right['module'],
            $right['path'],
            $right['code'],
        ]);

        return $diagnostics;
    }

    /** @return list<string> */
    private function phpFiles(string $modulePath): array
    {
        if (! is_dir($modulePath)) {
            return [];
        }

        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($modulePath, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        sort($files);

        return $files;
    }

    /** @return list<array{module: string, boundary: string}> */
    private function moduleReferences(string $source): array
    {
        preg_match_all(
            '~App\\\\Modules\\\\[A-Z][A-Za-z0-9]*\\\\([A-Z][A-Za-z0-9]*)\\\\([A-Za-z0-9_\\\\]+)~',
            $source,
            $matches,
            PREG_SET_ORDER,
        );

        return array_map(static fn (array $match): array => [
            'module' => $match[1],
            'boundary' => $match[2],
        ], $matches);
    }

    private function isPublicBoundary(string $boundary): bool
    {
        return str_starts_with($boundary, 'Application\\Contracts\\')
            || str_starts_with($boundary, 'Application\\DTO\\')
            || str_starts_with($boundary, 'Application\\Events\\');
    }

    private function relativePath(string $root, string $path): string
    {
        $prefix = rtrim(str_replace('\\', '/', $root), '/').'/';
        $normalized = str_replace('\\', '/', $path);

        return str_starts_with($normalized, $prefix)
            ? substr($normalized, strlen($prefix))
            : basename($path);
    }

    /** @return array{code: string, module: string, phase: string, path: string, message: string} */
    private function diagnostic(
        ModuleManifest $module,
        string $path,
        string $code,
        string $message,
    ): array {
        return [
            'code' => $code,
            'module' => $module->name,
            'phase' => 'architecture',
            'path' => $path,
            'message' => $message,
        ];
    }
}
