<?php

declare(strict_types=1);

namespace StarterKit\Modules;

use Illuminate\Support\ServiceProvider;
use StarterKit\Modules\Contracts\ModuleManifest;
use Throwable;

final class ModuleGraphValidator
{
    /**
     * @param  list<ModuleManifest>  $modules
     * @return array{boot_plan: list<ModuleManifest>, diagnostics: list<array{code: string, module: string, phase: string, path: string, message: string}>}
     */
    public function validate(array $modules): array
    {
        /** @var array<string, ModuleManifest> $byName */
        $byName = [];

        foreach ($modules as $module) {
            $byName[$module->name] = $module;
        }

        ksort($byName);
        $blocked = [];
        $diagnostics = [];

        foreach ($byName as $name => $module) {
            if ($module->status !== 'enabled') {
                $this->block($blocked, $diagnostics, $module, 'module_disabled', 'Module dinonaktifkan oleh manifest.');

                continue;
            }

            if (! $this->validProvider($module->provider)) {
                $this->block($blocked, $diagnostics, $module, 'provider_invalid', 'Provider module tidak tersedia atau tidak valid.');

                continue;
            }

            if (in_array($name, $module->dependencies, true)) {
                $this->block($blocked, $diagnostics, $module, 'dependency_self', 'Module tidak boleh bergantung pada dirinya sendiri.');

                continue;
            }

            foreach ($module->dependencies as $dependency) {
                if (! isset($byName[$dependency])) {
                    $this->block($blocked, $diagnostics, $module, 'dependency_missing', 'Dependency module tidak ditemukan.');

                    break;
                }
            }
        }

        foreach ($this->cycleNodes($byName, $blocked) as $name) {
            $this->block($blocked, $diagnostics, $byName[$name], 'dependency_cycle', 'Cycle dependency module terdeteksi.');
        }

        do {
            $changed = false;

            foreach ($byName as $name => $module) {
                if (isset($blocked[$name])) {
                    continue;
                }

                foreach ($module->dependencies as $dependency) {
                    if (isset($blocked[$dependency])) {
                        $this->block($blocked, $diagnostics, $module, 'dependency_unavailable', 'Dependency module tidak tersedia untuk runtime.');
                        $changed = true;

                        break;
                    }
                }
            }
        } while ($changed);

        return [
            'boot_plan' => $this->topologicalOrder($byName, $blocked),
            'diagnostics' => $diagnostics,
        ];
    }

    private function validProvider(string $provider): bool
    {
        try {
            return class_exists($provider) && is_subclass_of($provider, ServiceProvider::class);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @param  array<string, ModuleManifest>  $modules
     * @param  array<string, true>  $blocked
     * @return list<string>
     */
    private function cycleNodes(array $modules, array $blocked): array
    {
        $state = [];
        $stack = [];
        $stackIndexes = [];
        $cycles = [];

        $visit = function (string $name) use (&$visit, &$state, &$stack, &$stackIndexes, &$cycles, $modules, $blocked): void {
            $state[$name] = 1;
            $stackIndexes[$name] = count($stack);
            $stack[] = $name;

            foreach ($modules[$name]->dependencies as $dependency) {
                if (! isset($modules[$dependency]) || isset($blocked[$dependency])) {
                    continue;
                }

                $dependencyState = $state[$dependency] ?? 0;

                if ($dependencyState === 0) {
                    $visit($dependency);
                } elseif ($dependencyState === 1) {
                    foreach (array_slice($stack, $stackIndexes[$dependency]) as $cycleNode) {
                        $cycles[$cycleNode] = true;
                    }
                }
            }

            array_pop($stack);
            unset($stackIndexes[$name]);
            $state[$name] = 2;
        };

        foreach (array_keys($modules) as $name) {
            if (! isset($blocked[$name]) && ($state[$name] ?? 0) === 0) {
                $visit($name);
            }
        }

        $names = array_keys($cycles);
        sort($names);

        return $names;
    }

    /**
     * @param  array<string, ModuleManifest>  $modules
     * @param  array<string, true>  $blocked
     * @return list<ModuleManifest>
     */
    private function topologicalOrder(array $modules, array $blocked): array
    {
        $indegree = [];
        $dependents = [];

        foreach ($modules as $name => $module) {
            if (isset($blocked[$name])) {
                continue;
            }

            $indegree[$name] = count($module->dependencies);

            foreach ($module->dependencies as $dependency) {
                $dependents[$dependency][] = $name;
            }
        }

        $ready = array_keys(array_filter($indegree, static fn (int $value): bool => $value === 0));
        sort($ready);
        $ordered = [];

        while ($ready !== []) {
            $name = array_shift($ready);
            $ordered[] = $modules[$name];

            foreach ($dependents[$name] ?? [] as $dependent) {
                $indegree[$dependent]--;

                if ($indegree[$dependent] === 0) {
                    $ready[] = $dependent;
                    sort($ready);
                }
            }
        }

        return $ordered;
    }

    /**
     * @param  array<string, true>  $blocked
     * @param  list<array{code: string, module: string, phase: string, path: string, message: string}>  $diagnostics
     */
    private function block(
        array &$blocked,
        array &$diagnostics,
        ModuleManifest $module,
        string $code,
        string $message,
    ): void {
        if (isset($blocked[$module->name])) {
            return;
        }

        $blocked[$module->name] = true;
        $diagnostics[] = [
            'code' => $code,
            'module' => $module->name,
            'phase' => 'validation',
            'path' => $module->path.'/module.json',
            'message' => $message,
        ];
    }
}
