<?php

declare(strict_types=1);

namespace App\Support\StarterFoundation;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class ForbiddenDependencyScanner
{
    /**
     * @param  list<string>  $paths
     * @param  list<string>  $excluded
     * @return list<string>
     */
    public function scan(array $paths, array $excluded = []): array
    {
        $files = [];
        $excluded = array_map(static fn (string $path): string => realpath($path) ?: $path, $excluded);

        foreach ($paths as $path) {
            if (is_file($path)) {
                $files[] = $path;

                continue;
            }

            if (! is_dir($path)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
            );

            foreach ($iterator as $file) {
                if ($file->isFile()
                    && $file->getExtension() === 'php'
                    && ! in_array($file->getPathname(), $excluded, true)) {
                    $files[] = $file->getPathname();
                }
            }
        }

        $found = [];

        foreach (array_unique($files) as $file) {
            $contents = strtolower((string) file_get_contents($file));

            if (str_contains($contents, 'wayfinder')
                || str_contains($contents, 'laravel boost')
                || str_contains($contents, 'laravel-boost')) {
                $found[] = basename($file);
            }
        }

        return array_values(array_unique($found));
    }
}
