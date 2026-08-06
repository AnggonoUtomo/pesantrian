<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Application\DTO;

final readonly class StoredSettingData
{
    public function __construct(
        public string $id,
        public string $key,
        public mixed $value,
        public string $type,
        public string $description,
        public bool $isSensitive,
        public ?string $updatedBy,
        public string $updatedAt,
    ) {}
}
