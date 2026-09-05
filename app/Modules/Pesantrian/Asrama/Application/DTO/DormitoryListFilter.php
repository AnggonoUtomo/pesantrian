<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Application\DTO;

final readonly class DormitoryListFilter
{
    public function __construct(
        public ?string $search,
        public ?string $unitId,
        public ?string $genderPolicy,
        public ?string $status,
        public string $archived,
        public int $page,
        public int $perPage,
        public string $sortField,
        public string $sortDirection,
    ) {}
}
