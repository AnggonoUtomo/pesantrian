<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Presentation\Console\Commands;

use App\Modules\System\SystemSetting\Application\Contracts\SystemRuntimeSettings;
use Illuminate\Console\Command;

final class ShowSystemRuntimeCommand extends Command
{
    protected $signature = 'system-setting:runtime {--json}';

    protected $description = 'Menampilkan target runtime SystemSetting tanpa secret.';

    public function handle(SystemRuntimeSettings $settings): int
    {
        $data = $settings->current()->diagnostic();

        if ($this->option('json')) {
            $this->line((string) json_encode($data, JSON_THROW_ON_ERROR));
        } else {
            $this->table(
                ['Target', 'Nilai'],
                array_map(
                    static fn (string $key, int|bool|string|null $value): array => [
                        $key,
                        json_encode($value, JSON_THROW_ON_ERROR),
                    ],
                    array_keys($data),
                    array_values($data),
                ),
            );
        }

        return self::SUCCESS;
    }
}
