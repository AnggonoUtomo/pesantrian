<?php

declare(strict_types=1);

namespace StarterKit\Generator;

use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use StarterKit\Generator\Contracts\ModuleGenerationPlan;
use StarterKit\Generator\Contracts\ModuleGenerationPromotion;

final class ModulePromotionService
{
    public function promote(
        ModuleGenerationPlan $plan,
        string $rootPath,
        string $stagingRoot,
        bool $extension = false,
        bool $overwrite = false,
    ): ModuleGenerationPromotion {
        $targetPath = $this->targetPath($plan, $rootPath);

        if (! $extension && (is_dir($targetPath) || is_file($targetPath))) {
            throw new RuntimeException('Target module sudah ada.');
        }

        if ($extension && is_file($targetPath)) {
            throw new RuntimeException('Target module bukan directory.');
        }

        $stagingPath = rtrim($stagingRoot, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.Str::ulid()->toBase32();
        $outputPath = $extension ? $stagingPath.DIRECTORY_SEPARATOR.'output' : $stagingPath;
        $backupPath = $stagingPath.DIRECTORY_SEPARATOR.'backup';
        $createdFiles = [];
        $overwrittenFiles = [];

        try {
            if (! mkdir($stagingPath, 0755, true) && ! is_dir($stagingPath)) {
                throw new RuntimeException('Staging tidak dapat dibuat.');
            }

            $this->writePlan($plan, $outputPath);

            if ($extension) {
                $this->promoteExtension(
                    $plan,
                    $outputPath,
                    $targetPath,
                    $backupPath,
                    $overwrite,
                    $createdFiles,
                    $overwrittenFiles,
                );

                $this->removeDirectory($stagingPath);

                return new ModuleGenerationPromotion($targetPath);
            }

            $targetParent = dirname($targetPath);
            if (! is_dir($targetParent) && ! mkdir($targetParent, 0755, true) && ! is_dir($targetParent)) {
                throw new RuntimeException('Parent target tidak dapat dibuat.');
            }

            if (! $this->promoteDirectory($outputPath, $targetPath)) {
                throw new RuntimeException('Promotion module gagal.');
            }

            $this->removeDirectory($stagingPath);

            return new ModuleGenerationPromotion($targetPath);
        } catch (\Throwable $exception) {
            foreach ($overwrittenFiles as $relativePath) {
                $backup = $backupPath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
                $target = $targetPath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

                if (is_file($backup)) {
                    copy($backup, $target);
                }
            }

            foreach ($createdFiles as $relativePath) {
                $created = $targetPath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

                if (is_file($created)) {
                    unlink($created);
                }
            }

            $this->removeDirectory($stagingPath);

            throw $exception;
        }
    }

    private function writePlan(ModuleGenerationPlan $plan, string $outputPath): void
    {
        foreach ($plan->directories as $directory) {
            $this->safeRelativePath($directory);
            $path = $outputPath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $directory);

            if (! mkdir($path, 0755, true) && ! is_dir($path)) {
                throw new RuntimeException('Directory staging tidak dapat dibuat.');
            }
        }

        foreach ($plan->files as $relativePath => $contents) {
            $this->safeRelativePath($relativePath);
            $path = $outputPath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
            $parent = dirname($path);

            if (! is_dir($parent) && ! mkdir($parent, 0755, true) && ! is_dir($parent)) {
                throw new RuntimeException('Parent staging tidak dapat dibuat.');
            }

            if (file_put_contents($path, $contents, LOCK_EX) === false) {
                throw new RuntimeException('File staging tidak dapat ditulis.');
            }
        }
    }

    /**
     * @param  list<string>  $createdFiles
     * @param  list<string>  $overwrittenFiles
     *
     * @param-out list<string> $createdFiles
     * @param-out list<string> $overwrittenFiles
     */
    private function promoteExtension(
        ModuleGenerationPlan $plan,
        string $outputPath,
        string $targetPath,
        string $backupPath,
        bool $overwrite,
        array &$createdFiles,
        array &$overwrittenFiles,
    ): void {
        foreach ($plan->files as $relativePath => $contents) {
            $this->safeRelativePath($relativePath);
            $target = $targetPath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
            $staged = $outputPath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

            if (is_file($target) && ! $overwrite) {
                continue;
            }

            $parent = dirname($target);
            if (is_file($parent)) {
                throw new RuntimeException('Parent target tidak dapat dibuat.');
            }

            if (! is_dir($parent) && ! mkdir($parent, 0755, true) && ! is_dir($parent)) {
                throw new RuntimeException('Parent target tidak dapat dibuat.');
            }

            if (is_file($target)) {
                $backup = $backupPath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
                $backupParent = dirname($backup);

                if (! is_dir($backupParent) && ! mkdir($backupParent, 0755, true) && ! is_dir($backupParent)) {
                    throw new RuntimeException('Backup target tidak dapat dibuat.');
                }

                if (! copy($target, $backup)) {
                    throw new RuntimeException('Backup target tidak dapat dibuat.');
                }

                $overwrittenFiles[] = $relativePath;
            } else {
                $createdFiles[] = $relativePath;
            }

            $stagedContents = file_get_contents($staged);
            if (! is_string($stagedContents) || file_put_contents($target, $stagedContents, LOCK_EX) === false) {
                throw new RuntimeException('File target tidak dapat ditulis.');
            }
        }
    }

    private function safeRelativePath(string $path): void
    {
        if ($path === '' || str_starts_with($path, '/') || str_starts_with($path, '\\') || str_contains(str_replace('\\', '/', $path), '../')) {
            throw new InvalidArgumentException("Path output [$path] tidak aman.");
        }
    }

    private function targetPath(ModuleGenerationPlan $plan, string $rootPath): string
    {
        $root = rtrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $rootPath), DIRECTORY_SEPARATOR);
        $relative = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $plan->targetPath);
        $prefix = 'app'.DIRECTORY_SEPARATOR.'Modules'.DIRECTORY_SEPARATOR;

        if (! str_starts_with($relative, $prefix)) {
            throw new InvalidArgumentException('Target plan harus berada di app/Modules.');
        }

        return $root.DIRECTORY_SEPARATOR.substr($relative, strlen($prefix));
    }

    private function removeDirectory(string $path): void
    {
        if (! is_dir($path)) {
            return;
        }

        foreach (scandir($path) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $child = $path.DIRECTORY_SEPARATOR.$entry;
            is_dir($child) ? $this->removeDirectory($child) : unlink($child);
        }

        rmdir($path);
    }

    private function promoteDirectory(string $outputPath, string $targetPath): bool
    {
        for ($attempt = 0; $attempt < 20; $attempt++) {
            clearstatcache(true, $outputPath);
            clearstatcache(true, $targetPath);

            if (rename($outputPath, $targetPath)) {
                return true;
            }

            usleep(50_000 * ($attempt + 1));
        }

        return false;
    }
}
