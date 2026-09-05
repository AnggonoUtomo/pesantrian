<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PresensiSantri\Application\DTO;

final readonly class StudentAttendanceListFilter
{
    public function __construct(
        public ?string $search,
        public ?string $dateFrom,
        public ?string $dateTo,
        public ?string $contextType,
        public ?string $contextId,
        public ?string $status,
        public int $page,
        public int $perPage,
        public string $sortField,
        public string $sortDirection,
    ) {}
}
