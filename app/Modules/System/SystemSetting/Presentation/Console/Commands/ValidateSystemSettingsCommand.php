<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Presentation\Console\Commands;

use App\Modules\System\SystemSetting\Application\Queries\ValidateSystemSettings;
use Illuminate\Console\Command;
use Throwable;

final class ValidateSystemSettingsCommand extends Command
{
    protected $signature = 'system-setting:validate {--json}';

    protected $description = 'Memvalidasi record SystemSetting terhadap registry.';

    public function handle(ValidateSystemSettings $query): int
    {
        try {
            $report = $query->execute();
        } catch (Throwable) {
            $this->error('Validasi SystemSetting gagal karena storage tidak tersedia.');

            return self::FAILURE;
        }

        $payload = $report->toArray();

        if ($this->option('json')) {
            $this->line((string) json_encode($payload, JSON_THROW_ON_ERROR));
        } else {
            $this->line($report->isValid()
                ? 'Seluruh SystemSetting valid.'
                : 'SystemSetting memiliki record missing, invalid, atau unknown.');
        }

        return $report->isValid() ? self::SUCCESS : self::FAILURE;
    }
}
