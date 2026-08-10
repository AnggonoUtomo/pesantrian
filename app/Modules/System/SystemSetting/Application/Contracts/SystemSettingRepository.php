<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Application\Contracts;

use App\Modules\System\SystemSetting\Application\DTO\SettingDefinitionData;
use App\Modules\System\SystemSetting\Application\DTO\StoredSettingData;

interface SystemSettingRepository
{
    public function find(string $key): ?StoredSettingData;

    /**
     * @param  list<string>  $keys
     * @return array<string, StoredSettingData>
     */
    public function findMany(array $keys): array;

    /** @return list<StoredSettingData> */
    public function all(): array;

    /** @param int|bool|string|list<int>|null $value */
    public function upsert(
        SettingDefinitionData $definition,
        int|bool|string|array|null $value,
        ?string $actorId,
    ): StoredSettingData;
}
