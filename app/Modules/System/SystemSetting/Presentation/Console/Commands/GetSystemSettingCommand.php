<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Presentation\Console\Commands;

use App\Modules\System\SystemSetting\Application\Contracts\SystemSettingReader;
use Illuminate\Console\Command;
use Throwable;

final class GetSystemSettingCommand extends Command
{
    protected $signature = 'system-setting:get {key} {--json}';

    protected $description = 'Menampilkan satu SystemSetting yang terdaftar.';

    public function handle(SystemSettingReader $reader): int
    {
        try {
            $value = $reader->get((string) $this->argument('key'));
            $payload = [
                'key' => $value->key,
                'value' => $value->value,
                'source' => $value->source,
                'updated_at' => $value->updatedAt,
            ];
        } catch (Throwable) {
            $this->error('SystemSetting tidak dapat dibaca. Periksa key dan storage.');

            return self::FAILURE;
        }

        if ($this->option('json')) {
            $this->line((string) json_encode($payload, JSON_THROW_ON_ERROR));
        } else {
            $this->table(['Key', 'Value', 'Source'], [[
                $payload['key'],
                json_encode($payload['value'], JSON_THROW_ON_ERROR),
                $payload['source'],
            ]]);
        }

        return self::SUCCESS;
    }
}
