<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Presentation\Resources;

use App\Modules\System\SystemSetting\Application\DTO\SettingValueData;
use App\Modules\System\SystemSetting\Application\DTO\SystemSettingItemData;
use App\Modules\System\SystemSetting\Presentation\Support\SystemSettingOutputPresenter;

final readonly class SystemSettingResource
{
    public function __construct(
        private SystemSettingItemData|SettingValueData $setting,
        private SystemSettingOutputPresenter $presenter,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return $this->presenter->toArray($this->setting);
    }
}
