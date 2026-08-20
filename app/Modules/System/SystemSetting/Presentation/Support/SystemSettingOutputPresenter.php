<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Presentation\Support;

use App\Modules\System\SystemSetting\Application\DTO\SettingValueData;
use App\Modules\System\SystemSetting\Application\DTO\SystemSettingItemData;
use App\Modules\System\SystemSetting\Application\Services\SettingDefinitionRegistry;

final readonly class SystemSettingOutputPresenter
{
    public function __construct(private SettingDefinitionRegistry $definitions) {}

    /** @return array<string, mixed> */
    public function toArray(SettingValueData|SystemSettingItemData $setting): array
    {
        if ($setting instanceof SystemSettingItemData) {
            return [
                ...$setting->toArray(),
                'value' => $setting->sensitive ? null : $setting->value,
                'default_value' => $setting->sensitive ? null : $setting->defaultValue,
                'has_value' => $setting->hasValue,
            ];
        }

        $sensitive = $this->definitions->definition($setting->key)->sensitive;

        return [
            'key' => $setting->key,
            'value' => $sensitive ? null : $setting->value,
            'source' => $setting->source,
            'updated_at' => $setting->updatedAt,
            'sensitive' => $sensitive,
            'has_value' => $setting->value !== null,
        ];
    }

    public function displayValue(SettingValueData $setting): string
    {
        if ($this->definitions->definition($setting->key)->sensitive) {
            return $setting->value !== null ? 'Rahasia terisi' : 'Rahasia belum diatur';
        }

        return (string) json_encode($setting->value, JSON_THROW_ON_ERROR);
    }
}
