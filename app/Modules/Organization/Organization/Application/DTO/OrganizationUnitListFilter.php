<?php

declare(strict_types=1);

namespace App\Modules\Organization\Organization\Application\DTO;

final readonly class OrganizationUnitListFilter
{
    public function __construct(
        public ?string $search,
        public ?string $status,
        public ?string $type,
        public int $page,
        public int $perPage,
        public string $sortField,
        public string $sortDirection,
    ) {}
}
