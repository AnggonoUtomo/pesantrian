<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Application\DTO;

final readonly class TahfidzListFilter
{
    public function __construct(
        public ?string $search,
        public ?string $programId,
        public ?string $studentId,
        public ?string $supervisorId,
        public ?string $academicPeriodId,
        public ?string $type,
        public ?string $status,
        public ?string $dateFrom,
        public ?string $dateTo,
        public int $page,
        public int $perPage,
        public string $sortField,
        public string $sortDirection,
    ) {}
}
