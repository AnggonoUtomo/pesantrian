<?php

declare(strict_types=1);

namespace StarterKit\Modules;

use StarterKit\Modules\Contracts\ModuleManifest;

final class ModuleRuntimeState
{
    /** @var array<string, string> */
    private array $statuses = [];

    /** @var list<array{code: string, module: string, phase: string, path: string, message: string}> */
    private array $diagnostics = [];

    /**
     * @param  list<ModuleManifest>  $modules
     * @param  list<ModuleManifest>  $bootPlan
     * @param  list<array{code: string, module: string, phase: string, path: string, message: string}>  $diagnostics
     */
    public function initialize(array $modules, array $bootPlan, array $diagnostics): void
    {
        foreach ($modules as $module) {
            $this->statuses[$module->name] = 'discovered';
        }

        foreach ($diagnostics as $diagnostic) {
            $this->diagnostics[] = $diagnostic;

            if ($diagnostic['module'] !== 'unknown') {
                $this->statuses[$diagnostic['module']] = 'isolated';
            }
        }

        foreach ($bootPlan as $module) {
            $this->statuses[$module->name] = 'planned';
        }
    }

    /** @param list<string> $dependencies */
    public function dependenciesRegistered(array $dependencies): bool
    {
        return $this->dependenciesHaveStatus($dependencies, ['registered', 'booted']);
    }

    /** @param list<string> $dependencies */
    public function dependenciesBooted(array $dependencies): bool
    {
        return $this->dependenciesHaveStatus($dependencies, ['booted']);
    }

    public function markRegistered(string $module): void
    {
        $this->statuses[$module] = 'registered';
    }

    public function markBooted(string $module): void
    {
        $this->statuses[$module] = 'booted';
    }

    public function markRegisterFailed(ModuleManifest $module): void
    {
        $this->statuses[$module->name] = 'register_failed';
        $this->diagnostic(
            $module,
            'provider_register_failed',
            'register',
            'Provider module gagal pada fase register.',
        );
    }

    public function markBootFailed(ModuleManifest $module): void
    {
        $this->statuses[$module->name] = 'boot_failed';
        $this->diagnostic(
            $module,
            'provider_boot_failed',
            'boot',
            'Provider module gagal pada fase boot.',
        );
    }

    public function isolateDependency(ModuleManifest $module, string $phase): void
    {
        $this->statuses[$module->name] = 'isolated';
        $this->diagnostic(
            $module,
            'dependency_runtime_unavailable',
            $phase,
            'Dependency module tidak tersedia pada runtime.',
        );
    }

    public function status(string $module): string
    {
        return $this->statuses[$module] ?? 'unknown';
    }

    /** @return list<array{code: string, module: string, phase: string, path: string, message: string}> */
    public function diagnostics(): array
    {
        return $this->diagnostics;
    }

    /** @return list<array{code: string, module: string, phase: string, path: string, message: string}> */
    public function runtimeDiagnostics(): array
    {
        return array_values(array_filter(
            $this->diagnostics,
            static fn (array $diagnostic): bool => in_array($diagnostic['phase'], ['register', 'boot'], true),
        ));
    }

    /**
     * @param  list<string>  $dependencies
     * @param  list<string>  $allowedStatuses
     */
    private function dependenciesHaveStatus(array $dependencies, array $allowedStatuses): bool
    {
        foreach ($dependencies as $dependency) {
            if (! in_array($this->status($dependency), $allowedStatuses, true)) {
                return false;
            }
        }

        return true;
    }

    private function diagnostic(
        ModuleManifest $module,
        string $code,
        string $phase,
        string $message,
    ): void {
        $this->diagnostics[] = [
            'code' => $code,
            'module' => $module->name,
            'phase' => $phase,
            'path' => $module->path.'/module.json',
            'message' => $message,
        ];
    }
}
