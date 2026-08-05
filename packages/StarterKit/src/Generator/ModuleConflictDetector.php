<?php

declare(strict_types=1);

namespace StarterKit\Generator;

use InvalidArgumentException;
use StarterKit\Generator\Contracts\ModuleGenerationPlan;
use StarterKit\Modules\ModuleRegistry;

final class ModuleConflictDetector
{
    public function __construct(private ModuleRegistry $registry) {}

    /** @return list<array{code: string, message: string}> */
    public function detect(ModuleGenerationPlan $plan, string $rootPath, bool $extension = false): array
    {
        $targetPath = $this->targetPath($plan, $rootPath);
        $diagnostics = [];

        if (is_file($targetPath) && $extension) {
            $diagnostics[] = [
                'code' => 'TARGET_NOT_DIRECTORY',
                'message' => "Target [{$plan->targetPath}] bukan directory module.",
            ];
        } elseif (! $extension && (is_dir($targetPath) || is_file($targetPath))) {
            $diagnostics[] = [
                'code' => 'TARGET_EXISTS',
                'message' => "Target [{$plan->targetPath}] sudah ada.",
            ];
        }

        $manifest = json_decode($plan->files['module.json'] ?? '', true);
        if (! is_array($manifest)) {
            throw new InvalidArgumentException('Plan module.json tidak valid.');
        }

        $discovery = $this->registry->discover($rootPath);
        foreach ($discovery['diagnostics'] as $diagnostic) {
            $diagnostics[] = [
                'code' => 'REGISTRY_INVALID',
                'message' => 'Registry existing memiliki diagnostic: '.$diagnostic['message'],
            ];
        }

        foreach ($discovery['modules'] as $module) {
            $isTargetModule = $extension && $module->path === $manifest['path'];

            if ($isTargetModule) {
                continue;
            }

            foreach (['name', 'path', 'namespace', 'provider'] as $identity) {
                if ($module->{$identity} === $manifest[$identity]) {
                    $diagnostics[] = [
                        'code' => 'DUPLICATE_'.strtoupper($identity),
                        'message' => "Duplicate module {$identity} [{$manifest[$identity]}].",
                    ];
                }
            }
        }

        return $diagnostics;
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
}
