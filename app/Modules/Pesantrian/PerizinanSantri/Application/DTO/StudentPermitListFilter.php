<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PerizinanSantri\Application\DTO;

final readonly class StudentPermitListFilter
{
    public function __construct(
        public ?string $search,
        public ?string $dateFrom,
        public ?string $dateTo,
        public ?string $permitType,
        public ?string $status,
        public ?string $studentId,
        public ?bool $isLate,
        public int $page,
        public int $perPage,
        public string $sortField,
        public string $sortDirection,
    ) {}
}
