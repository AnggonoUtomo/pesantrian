<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Application\DTO;

final readonly class StudentAchievementListFilter
{
    public function __construct(
        public ?string $search,
        public ?string $dateFrom,
        public ?string $dateTo,
        public ?string $status,
        public ?string $level,
        public ?string $categoryId,
        public ?string $studentId,
        public ?string $mentorEmployeeId,
        public ?string $academicPeriodId,
        public int $page,
        public int $perPage,
        public string $sortField,
        public string $sortDirection,
    ) {}
}
