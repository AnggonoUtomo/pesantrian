<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Presentation\Console\Commands;

use App\Models\User;
use App\Modules\System\SystemSetting\Application\Actions\UpdateSystemSetting;
use App\Modules\System\SystemSetting\Application\DTO\SettingDefinitionData;
use App\Modules\System\SystemSetting\Application\DTO\UpdateSystemSettingData;
use App\Modules\System\SystemSetting\Application\Services\SettingDefinitionRegistry;
use App\Modules\System\SystemSetting\Presentation\Support\SystemSettingOutputPresenter;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\StreamableInputInterface;
use Throwable;

final class SetSystemSettingCommand extends Command
{
    protected $signature = 'system-setting:set
                            {key : Key SystemSetting yang terdaftar}
                            {value? : Nilai posisi untuk setting non-sensitive}
                            {--actor= : ULID actor SuperSystem}
                            {--reason= : Alasan perubahan}
                            {--value-stdin : Baca nilai sensitif dari STDIN}
                            {--json : Keluarkan response JSON}';

    protected $description = 'Mengubah SystemSetting dengan actor SuperSystem dan reason.';

    public function handle(
        UpdateSystemSetting $action,
        SystemSettingOutputPresenter $presenter,
        SettingDefinitionRegistry $definitions,
    ): int {
        $actor = User::query()->find((string) $this->option('actor'));

        if (! $actor instanceof User || trim((string) $this->option('reason')) === '') {
            $this->error('Actor SuperSystem dan reason wajib diisi.');

            return self::FAILURE;
        }

        try {
            $definition = $definitions->definition((string) $this->argument('key'));
            $inputValue = $this->inputValue($definition);

            if ($inputValue === false) {
                return self::FAILURE;
            }

            $result = $action->execute($actor, new UpdateSystemSettingData(
                key: (string) $this->argument('key'),
                value: $definition->sensitive
                    ? $inputValue
                    : $this->normalizeInput($inputValue),
                reason: (string) $this->option('reason'),
                correlationId: (string) Str::ulid(),
            ));
        } catch (Throwable) {
            $this->error('SystemSetting gagal diubah. Periksa actor, key, value, dan reason.');

            return self::FAILURE;
        }

        $payload = $presenter->toArray($result);

        $this->line($this->option('json')
            ? (string) json_encode($payload, JSON_THROW_ON_ERROR)
            : "SystemSetting [{$result->key}] berhasil diubah.");

        return self::SUCCESS;
    }

    private function inputValue(SettingDefinitionData $definition): string|false
    {
        $argument = $this->argument('value');
        $fromStdin = (bool) $this->option('value-stdin');

        if (! $definition->sensitive) {
            if ($fromStdin) {
                $this->error('Opsi --value-stdin hanya boleh dipakai untuk setting sensitif.');

                return false;
            }

            if (! is_string($argument)) {
                $this->error('Argumen value wajib diisi untuk setting non-sensitive.');

                return false;
            }

            return $argument;
        }

        if ($argument !== null) {
            $this->error('Setting sensitif tidak boleh memakai argumen posisi. Gunakan prompt tersembunyi atau --value-stdin.');

            return false;
        }

        if ($fromStdin) {
            return $this->readStdin();
        }

        if (! $this->input->isInteractive()) {
            $this->error('Input sensitif non-interaktif wajib memakai --value-stdin.');

            return false;
        }

        $value = $this->secret("Masukkan nilai sensitif untuk [{$definition->key}]");

        if (! is_string($value)) {
            $this->error('Nilai sensitif wajib diisi.');

            return false;
        }

        return $value;
    }

    private function readStdin(): string|false
    {
        $stream = $this->input instanceof StreamableInputInterface
            ? $this->input->getStream()
            : null;
        $stream = is_resource($stream) ? $stream : STDIN;
        $value = stream_get_contents($stream);

        if (! is_string($value)) {
            $this->error('Nilai sensitif tidak dapat dibaca dari STDIN.');

            return false;
        }

        return preg_replace('/\r?\n\z/', '', $value) ?? $value;
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
