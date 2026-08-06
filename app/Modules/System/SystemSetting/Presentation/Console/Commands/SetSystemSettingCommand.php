<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Presentation\Console\Commands;

use App\Models\User;
use App\Modules\System\SystemSetting\Application\Actions\UpdateSystemSetting;
use App\Modules\System\SystemSetting\Application\DTO\UpdateSystemSettingData;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Throwable;

final class SetSystemSettingCommand extends Command
{
    protected $signature = 'system-setting:set {key} {value} {--actor=} {--reason=} {--json}';

    protected $description = 'Mengubah SystemSetting dengan actor SuperSystem dan reason.';

    public function handle(UpdateSystemSetting $action): int
    {
        $actor = User::query()->find((string) $this->option('actor'));

        if (! $actor instanceof User || trim((string) $this->option('reason')) === '') {
            $this->error('Actor SuperSystem dan reason wajib diisi.');

            return self::FAILURE;
        }

        try {
            $result = $action->execute($actor, new UpdateSystemSettingData(
                key: (string) $this->argument('key'),
                value: $this->normalizeInput((string) $this->argument('value')),
                reason: (string) $this->option('reason'),
                correlationId: (string) Str::ulid(),
            ));
        } catch (Throwable) {
            $this->error('SystemSetting gagal diubah. Periksa actor, key, value, dan reason.');

            return self::FAILURE;
        }

        $payload = ['key' => $result->key, 'value' => $result->value, 'source' => $result->source];

        $this->line($this->option('json')
            ? (string) json_encode($payload, JSON_THROW_ON_ERROR)
            : "SystemSetting [{$result->key}] berhasil diubah.");

        return self::SUCCESS;
    }

    private function normalizeInput(string $value): int|bool|string|null
    {
        $trimmed = trim($value);

        if ($trimmed === 'null') {
            return null;
        }

        if ($trimmed === 'true' || $trimmed === 'false') {
            return $trimmed === 'true';
        }

        if (preg_match('/^-?\d+$/', $trimmed) === 1) {
            return (int) $trimmed;
        }

        return $trimmed;
    }
}
