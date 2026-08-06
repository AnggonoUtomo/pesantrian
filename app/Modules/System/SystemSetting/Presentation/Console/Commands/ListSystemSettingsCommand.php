<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Presentation\Console\Commands;

use App\Modules\System\SystemSetting\Application\Queries\ListSystemSettings;
use Illuminate\Console\Command;

final class ListSystemSettingsCommand extends Command
{
    protected $signature = 'system-setting:list {--json}';

    protected $description = 'Menampilkan seluruh SystemSetting yang terdaftar.';

    public function handle(ListSystemSettings $query): int
    {
        $settings = array_map(static fn ($setting): array => $setting->toArray(), $query->execute());

        if ($this->option('json')) {
            $this->line((string) json_encode($settings, JSON_THROW_ON_ERROR));

            return self::SUCCESS;
        }

        $this->table(
            ['Key', 'Type', 'Value', 'Source'],
            array_map(static fn (array $setting): array => [
                $setting['key'],
                $setting['type'],
                json_encode($setting['value'], JSON_THROW_ON_ERROR),
                $setting['source'],
            ], $settings),
        );

        return self::SUCCESS;
    }
}
