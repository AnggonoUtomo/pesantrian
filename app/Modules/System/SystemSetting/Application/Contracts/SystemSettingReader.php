<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Application\Contracts;

use App\Modules\System\SystemSetting\Application\DTO\SettingValueData;

interface SystemSettingReader
{
    public function get(string $key): SettingValueData;

    /**
     * @param  list<string>  $keys
     * @return array<string, SettingValueData>
     */
    public function many(array $keys): array;

    public function integer(string $key): int;

    public function boolean(string $key): bool;

    public function string(string $key): ?string;
}
