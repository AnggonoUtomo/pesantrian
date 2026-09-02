<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Application\DTO;

final readonly class CurriculumData
{
    public function __construct(
        public string $id,
        public string $code,
        public string $name,
        public ?string $description,
        public string $status,
        public ?string $createdAt,
        public ?string $updatedAt,
    ) {}
}
