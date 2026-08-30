<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PenerimaanSantri\Application\DTO;

final readonly class StudentAdmissionListFilter
{
    public function __construct(
        public ?string $search,
        public ?string $status,
        public ?string $targetUnitId,
        public ?string $registrationFeeStatus,
        public int $page,
        public int $perPage,
        public string $sortField,
        public string $sortDirection,
    ) {}
}
