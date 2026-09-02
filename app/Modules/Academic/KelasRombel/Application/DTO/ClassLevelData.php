<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Application\DTO;

final readonly class ClassLevelData
{
    public function __construct(
        public string $id,
        public string $unitId,
        public string $code,
        public string $name,
        public int $sequence,
        public string $status,
        public ?string $createdAt,
        public ?string $updatedAt,
    ) {}
}
