<?php

declare(strict_types=1);

namespace StarterKit\Console\Commands;

use Illuminate\Console\Command;
use StarterKit\Modules\ModuleRegistry;
use StarterKit\Modules\ModuleRuntimeState;

final class ModuleValidateCommand extends Command
{
    protected $signature = 'module:validate
        {module? : Target module dalam format Domain/Module}
        {--json : Tampilkan hasil sebagai JSON}';

    protected $description = 'Memvalidasi manifest module tanpa mengubah file.';

    public function handle(ModuleRegistry $registry, ModuleRuntimeState $runtimeState): int
    {
        $result = $registry->bootPlan(app_path('Modules'));
        $result['diagnostics'] = [...$result['diagnostics'], ...$runtimeState->runtimeDiagnostics()];
        $target = $this->argument('module');
        $diagnostics = $result['diagnostics'];
        $modules = $result['modules'];
        $bootPlan = $result['boot_plan'];

        if ($target !== null) {
            $target = trim((string) $target, '/');

            if (preg_match('#^[A-Z][A-Za-z0-9]*/[A-Z][A-Za-z0-9]*$#', $target) !== 1) {
                return $this->respond([
                    'success' => false,
                    'code' => 'MODULE_TARGET_INVALID',
                    'message' => 'Target module harus menggunakan format Domain/Module.',
                    'data' => ['target' => $target, 'valid' => 0],
                    'diagnostics' => [],
                ]);
            }

            [$domain, $name] = explode('/', $target, 2);
            $modules = array_values(array_filter(
                $modules,
                static fn ($module): bool => $module->domain === $domain && $module->name === $name,
            ));
            $bootPlan = array_values(array_filter(
                $bootPlan,
                static fn ($module): bool => $module->domain === $domain && $module->name === $name,
            ));
            $diagnostics = array_values(array_filter(
                $diagnostics,
                static fn (array $diagnostic): bool => $diagnostic['module'] === $name
                    || str_starts_with($diagnostic['path'], $target.'/'),
            ));

            if ($modules === [] && $diagnostics === []) {
                return $this->respond([
                    'success' => false,
                    'code' => 'MODULE_TARGET_NOT_FOUND',
                    'message' => "Target module [$target] tidak ditemukan.",
                    'data' => ['target' => $target, 'valid' => 0],
                    'diagnostics' => [],
                ]);
            }
        }

        $success = $diagnostics === [] && ($target === null || $bootPlan !== []);
        $payload = [
            'success' => $success,
            'code' => $success ? 'MODULE_VALID' : 'MODULE_INVALID',
            'message' => $success
                ? ($target === null ? 'Semua module yang ditemukan valid.' : "Target module [$target] valid.")
                : 'Ada module yang tidak valid.',
            'data' => [
                'target' => $target,
                'valid' => count($bootPlan),
                'boot_plan' => array_map(static fn ($module): string => $module->name, $bootPlan),
                'diagnostics' => $diagnostics,
            ],
            'diagnostics' => $diagnostics,
        ];

        if ($this->option('json')) {
            $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        } else {
            $success ? $this->info($target === null ? 'Semua module yang ditemukan valid.' : "Target module [$target] valid.") : $this->error('Ada module yang tidak valid.');
            foreach ($diagnostics as $diagnostic) {
                $this->error($diagnostic['path'].': '.$diagnostic['message']);
            }
        }

        return $success ? self::SUCCESS : self::FAILURE;
    }

    /** @param array<string, mixed> $payload */
    private function respond(array $payload): int
    {
        if ($this->option('json')) {
            $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        } elseif ($payload['success']) {
            $this->info((string) $payload['message']);
        } else {
            $this->error((string) $payload['message']);

            foreach ($payload['diagnostics'] as $diagnostic) {
                $this->error($diagnostic['path'].': '.$diagnostic['message']);
            }
        }

        return $payload['success'] ? self::SUCCESS : self::FAILURE;
    }
}
