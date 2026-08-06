<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Presentation\Resources;

use App\Modules\System\SystemSetting\Application\DTO\SettingValueData;
use App\Modules\System\SystemSetting\Application\DTO\SystemSettingItemData;

final readonly class SystemSettingResource
{
    public function __construct(private SystemSettingItemData|SettingValueData $setting) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        if ($this->setting instanceof SystemSettingItemData) {
            return $this->setting->toArray();
        }

        return [
            'key' => $this->setting->key,
            'value' => $this->setting->value,
            'source' => $this->setting->source,
            'updated_at' => $this->setting->updatedAt,
        ];
    }
}
