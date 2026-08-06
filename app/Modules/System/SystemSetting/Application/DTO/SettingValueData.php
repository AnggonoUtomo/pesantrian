<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Application\DTO;

final readonly class SettingValueData
{
    public function __construct(
        public string $key,
        public int|bool|string|null $value,
        public string $source,
        public ?string $updatedAt = null,
    ) {}
}
