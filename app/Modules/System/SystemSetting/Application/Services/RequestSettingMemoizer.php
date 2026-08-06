<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Application\Services;

use App\Modules\System\SystemSetting\Application\DTO\SettingValueData;

final class RequestSettingMemoizer
{
    /** @var array<string, SettingValueData> */
    private array $values = [];

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->values);
    }

    public function get(string $key): SettingValueData
    {
        return $this->values[$key];
    }

    public function put(SettingValueData $value): void
    {
        $this->values[$value->key] = $value;
    }

    public function forget(string $key): void
    {
        unset($this->values[$key]);
    }
}
