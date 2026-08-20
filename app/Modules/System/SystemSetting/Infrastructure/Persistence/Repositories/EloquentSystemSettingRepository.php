<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Infrastructure\Persistence\Repositories;

use App\Modules\System\SystemSetting\Application\Contracts\SystemSettingRepository;
use App\Modules\System\SystemSetting\Application\DTO\SettingDefinitionData;
use App\Modules\System\SystemSetting\Application\DTO\StoredSettingData;
use App\Modules\System\SystemSetting\Domain\Exceptions\SettingStorageUnavailable;
use App\Modules\System\SystemSetting\Infrastructure\Persistence\Models\SystemSettingRecord;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Crypt;
use JsonException;

final class EloquentSystemSettingRepository implements SystemSettingRepository
{
    public function find(string $key): ?StoredSettingData
    {
        try {
            $record = SystemSettingRecord::query()->where('key', $key)->first();
        } catch (QueryException $exception) {
            throw new SettingStorageUnavailable('Penyimpanan SystemSetting tidak tersedia.', previous: $exception);
        }

        return $record === null ? null : $this->toData($record);
    }

    public function findMany(array $keys): array
    {
        try {
            return SystemSettingRecord::query()
                ->whereIn('key', $keys)
                ->get()
                ->mapWithKeys(fn (SystemSettingRecord $record): array => [
                    $record->key => $this->toData($record),
                ])
                ->all();
        } catch (QueryException $exception) {
            throw new SettingStorageUnavailable('Penyimpanan SystemSetting tidak tersedia.', previous: $exception);
        }
    }

    /** @return list<StoredSettingData> */
    public function all(): array
    {
        try {
            $settings = SystemSettingRecord::query()
                ->orderBy('key')
                ->get()
                ->map(fn (SystemSettingRecord $record): StoredSettingData => $this->toData($record))
                ->all();

            return array_values($settings);
        } catch (QueryException $exception) {
            throw new SettingStorageUnavailable('Penyimpanan SystemSetting tidak tersedia.', previous: $exception);
        }
    }

    /** @param int|bool|string|list<int>|null $value */
    public function upsert(
        SettingDefinitionData $definition,
        int|bool|string|array|null $value,
        ?string $actorId,
    ): StoredSettingData {
        try {
            $record = SystemSettingRecord::query()->updateOrCreate(
                ['key' => $definition->key],
                [
                    'value' => $definition->sensitive
                        ? json_encode(Crypt::encryptString(json_encode($value, JSON_THROW_ON_ERROR)), JSON_THROW_ON_ERROR)
                        : json_encode($value, JSON_THROW_ON_ERROR),
                    'type' => $definition->type->value,
                    'description' => $definition->description,
                    'is_sensitive' => $definition->sensitive,
                    'updated_by' => $actorId,
                ],
            );
        } catch (QueryException|JsonException $exception) {
            throw new SettingStorageUnavailable('Penyimpanan SystemSetting tidak tersedia.', previous: $exception);
        }

        return $this->toData($record);
    }

    private function toData(SystemSettingRecord $record): StoredSettingData
    {
        try {
            $storedValue = json_decode($record->value, true, flags: JSON_THROW_ON_ERROR);

            if ($record->is_sensitive) {
                if (! is_string($storedValue)) {
                    throw new SettingStorageUnavailable('Nilai SystemSetting tidak dapat dibaca.');
                }

                $value = json_decode(Crypt::decryptString($storedValue), true, flags: JSON_THROW_ON_ERROR);
            } else {
                $value = $storedValue;
            }
        } catch (DecryptException|JsonException $exception) {
            throw new SettingStorageUnavailable('Nilai SystemSetting tidak dapat dibaca.', previous: $exception);
        }

        return new StoredSettingData(
            id: $record->id,
            key: $record->key,
            value: $value,
            type: $record->type,
            description: $record->description,
            isSensitive: $record->is_sensitive,
            updatedBy: $record->updated_by,
            updatedAt: $record->updated_at->toISOString(),
        );
    }
}
