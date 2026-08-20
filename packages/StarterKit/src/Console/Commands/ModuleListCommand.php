<?php

declare(strict_types=1);

namespace StarterKit\Console\Commands;

use Illuminate\Console\Command;
use StarterKit\Modules\ModuleRegistry;
use StarterKit\Modules\ModuleRuntimeState;

final class ModuleListCommand extends Command
{
    protected $signature = 'module:list {--json : Tampilkan hasil sebagai JSON}';

    protected $description = 'Menampilkan module yang ditemukan.';

    public function handle(ModuleRegistry $registry, ModuleRuntimeState $runtimeState): int
    {
        $result = $registry->bootPlan(app_path('Modules'));
        $result['diagnostics'] = [...$result['diagnostics'], ...$runtimeState->runtimeDiagnostics()];
        $bootable = array_fill_keys(
            array_map(static fn ($module): string => $module->name, $result['boot_plan']),
            true,
        );

        if ($this->option('json')) {
            $this->line(json_encode([
                'success' => $result['diagnostics'] === [],
                'code' => $result['diagnostics'] === [] ? 'MODULE_LISTED' : 'MODULE_LIST_FAILED',
                'message' => $result['diagnostics'] === [] ? 'Daftar module berhasil dibaca.' : 'Daftar module memiliki diagnostic.',
                'data' => [
                    'modules' => array_map(static fn ($module): array => [
                        ...get_object_vars($module),
                        'bootable' => isset($bootable[$module->name]),
                    ], $result['modules']),
                    'boot_plan' => array_keys($bootable),
                ],
                'diagnostics' => $result['diagnostics'],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        } else {
            foreach ($result['modules'] as $module) {
                $runtimeStatus = isset($bootable[$module->name]) ? 'bootable' : 'isolated';
                $this->line($module->domain.'/'.$module->name.' ('.$module->status.', '.$runtimeStatus.')');
            }
        }

        return $result['diagnostics'] === [] ? self::SUCCESS : self::FAILURE;
    }
}
