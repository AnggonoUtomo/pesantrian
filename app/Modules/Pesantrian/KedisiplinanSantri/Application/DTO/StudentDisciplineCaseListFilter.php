<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO;

final readonly class StudentDisciplineCaseListFilter
{
    public function __construct(
        public ?string $search,
        public ?string $dateFrom,
        public ?string $dateTo,
        public ?string $status,
        public ?string $severity,
        public ?string $categoryId,
        public ?string $studentId,
        public ?string $assignedEmployeeId,
        public int $page,
        public int $perPage,
        public string $sortField,
        public string $sortDirection,
    ) {}
}
