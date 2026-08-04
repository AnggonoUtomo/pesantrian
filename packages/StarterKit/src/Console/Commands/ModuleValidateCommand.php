<?php

declare(strict_types=1);

namespace StarterKit\Console\Commands;

use Illuminate\Console\Command;
use StarterKit\Modules\ModuleRegistry;

final class ModuleValidateCommand extends Command
{
    protected $signature = 'module:validate {--json : Tampilkan hasil sebagai JSON}';

    protected $description = 'Memvalidasi manifest module tanpa mengubah file.';

    public function handle(ModuleRegistry $registry): int
    {
        $result = $registry->discover(app_path('Modules'));
        $success = $result['diagnostics'] === [];
        $payload = [
            'success' => $success,
            'code' => $success ? 'MODULE_VALID' : 'MODULE_INVALID',
            'data' => ['valid' => count($result['modules']), 'diagnostics' => $result['diagnostics']],
        ];

        if ($this->option('json')) {
            $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $success ? $this->info('Semua module yang ditemukan valid.') : $this->error('Ada module yang tidak valid.');
        }

        return $success ? self::SUCCESS : self::FAILURE;
    }
}
