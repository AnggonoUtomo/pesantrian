<?php

declare(strict_types=1);

namespace StarterKit\Console\Commands;

use Illuminate\Console\Command;
use StarterKit\Modules\ModuleRegistry;

final class ModuleListCommand extends Command
{
    protected $signature = 'module:list {--json : Tampilkan hasil sebagai JSON}';

    protected $description = 'Menampilkan module yang ditemukan.';

    public function handle(ModuleRegistry $registry): int
    {
        $result = $registry->discover(app_path('Modules'));

        if ($this->option('json')) {
            $this->line(json_encode([
                'success' => $result['diagnostics'] === [],
                'code' => $result['diagnostics'] === [] ? 'MODULE_LISTED' : 'MODULE_LIST_FAILED',
                'data' => ['modules' => array_map(static fn ($module): array => get_object_vars($module), $result['modules'])],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            foreach ($result['modules'] as $module) {
                $this->line($module->domain.'/'.$module->name.' ('.$module->status.')');
            }
        }

        return $result['diagnostics'] === [] ? self::SUCCESS : self::FAILURE;
    }
}
