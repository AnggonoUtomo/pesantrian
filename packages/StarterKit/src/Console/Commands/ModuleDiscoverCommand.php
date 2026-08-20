<?php

declare(strict_types=1);

namespace StarterKit\Console\Commands;

use Illuminate\Console\Command;
use StarterKit\Modules\ModuleRegistry;
use StarterKit\Modules\ModuleRuntimeState;

final class ModuleDiscoverCommand extends Command
{
    protected $signature = 'module:discover {--json : Tampilkan hasil sebagai JSON}';

    protected $description = 'Menemukan module dari app/Modules tanpa mengubah file.';

    public function handle(ModuleRegistry $registry, ModuleRuntimeState $runtimeState): int
    {
        $result = $registry->bootPlan(app_path('Modules'));
        $result['diagnostics'] = [...$result['diagnostics'], ...$runtimeState->runtimeDiagnostics()];
        $payload = [
            'success' => $result['diagnostics'] === [],
            'code' => $result['diagnostics'] === [] ? 'MODULE_DISCOVERED' : 'MODULE_DISCOVERY_FAILED',
            'message' => $result['diagnostics'] === [] ? 'Discovery module berhasil.' : 'Discovery menemukan module tidak valid.',
            'data' => [
                'modules' => array_map(static fn ($module): array => get_object_vars($module), $result['modules']),
                'boot_plan' => array_map(static fn ($module): string => $module->name, $result['boot_plan']),
            ],
            'diagnostics' => $result['diagnostics'],
        ];

        return $this->respond($payload);
    }

    /** @param array<string, mixed> $payload */
    private function respond(array $payload): int
    {
        if ($this->option('json')) {
            $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        } else {
            $this->info(count($payload['data']['modules']).' module ditemukan.');
            foreach ($payload['diagnostics'] as $diagnostic) {
                $this->error($diagnostic['path'].': '.$diagnostic['message']);
            }
        }

        return $payload['success'] ? self::SUCCESS : self::FAILURE;
    }
}
