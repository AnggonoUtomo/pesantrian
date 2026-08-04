<?php

declare(strict_types=1);

namespace StarterKit\Console\Commands;

use Illuminate\Console\Command;
use StarterKit\Modules\ModuleRegistry;

final class ModuleDiscoverCommand extends Command
{
    protected $signature = 'module:discover {--json : Tampilkan hasil sebagai JSON}';

    protected $description = 'Menemukan module dari app/Modules tanpa mengubah file.';

    public function handle(ModuleRegistry $registry): int
    {
        $result = $registry->discover(app_path('Modules'));
        $payload = [
            'success' => $result['diagnostics'] === [],
            'code' => $result['diagnostics'] === [] ? 'MODULE_DISCOVERED' : 'MODULE_DISCOVERY_FAILED',
            'data' => [
                'modules' => array_map(static fn ($module): array => get_object_vars($module), $result['modules']),
                'diagnostics' => $result['diagnostics'],
            ],
        ];

        return $this->respond($payload);
    }

    /** @param array<string, mixed> $payload */
    private function respond(array $payload): int
    {
        if ($this->option('json')) {
            $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $this->info(count($payload['data']['modules']).' module ditemukan.');
            foreach ($payload['data']['diagnostics'] as $diagnostic) {
                $this->error($diagnostic['path'].': '.$diagnostic['message']);
            }
        }

        return $payload['success'] ? self::SUCCESS : self::FAILURE;
    }
}
