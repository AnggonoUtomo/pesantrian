<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO;

final readonly class PaginatedStudentDisciplineCaseData
{
    /** @param list<StudentDisciplineCaseData> $data */
    public function __construct(
        public array $data,
        public int $currentPage,
        public int $perPage,
        public int $total,
        public int $lastPage,
    ) {}
}
