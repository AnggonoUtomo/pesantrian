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
    public function promote(ModuleGenerationPlan $plan, string $rootPath, string $stagingRoot): ModuleGenerationPromotion
    {
        $targetPath = $this->targetPath($plan, $rootPath);

        if (is_dir($targetPath) || is_file($targetPath)) {
            throw new RuntimeException("Target [$targetPath] sudah ada.");
        }

        $stagingPath = rtrim($stagingRoot, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.Str::ulid()->toBase32();

        try {
            if (! mkdir($stagingPath, 0755, true) && ! is_dir($stagingPath)) {
                throw new RuntimeException("Staging [$stagingPath] tidak dapat dibuat.");
            }

            foreach ($plan->directories as $directory) {
                $this->safeRelativePath($directory);
                $path = $stagingPath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $directory);

                if (! mkdir($path, 0755, true) && ! is_dir($path)) {
                    throw new RuntimeException("Directory staging [$path] tidak dapat dibuat.");
                }
            }

            foreach ($plan->files as $relativePath => $contents) {
                $this->safeRelativePath($relativePath);
                $path = $stagingPath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
                $parent = dirname($path);

                if (! is_dir($parent) && ! mkdir($parent, 0755, true) && ! is_dir($parent)) {
                    throw new RuntimeException("Parent staging [$parent] tidak dapat dibuat.");
                }

                if (file_put_contents($path, $contents, LOCK_EX) === false) {
                    throw new RuntimeException("File staging [$path] tidak dapat ditulis.");
                }
            }

            $targetParent = dirname($targetPath);
            if (! is_dir($targetParent) && ! mkdir($targetParent, 0755, true) && ! is_dir($targetParent)) {
                throw new RuntimeException("Parent target [$targetParent] tidak dapat dibuat.");
            }

            if (! rename($stagingPath, $targetPath)) {
                throw new RuntimeException("Promotion ke [$targetPath] gagal.");
            }

            return new ModuleGenerationPromotion($targetPath);
        } catch (\Throwable $exception) {
            $this->removeDirectory($stagingPath);

            throw $exception;
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
}
