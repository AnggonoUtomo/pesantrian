<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts;

use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\PaginatedStudentDisciplineCaseData;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCaseData;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCaseListFilter;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCategoryData;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCategoryListFilter;

interface StudentDisciplineReadRepository
{
    /** @return list<StudentDisciplineCategoryData> */
    public function categories(StudentDisciplineCategoryListFilter $filter): array;

    public function paginateCases(StudentDisciplineCaseListFilter $filter): PaginatedStudentDisciplineCaseData;

    public function findCase(string $id): ?StudentDisciplineCaseData;
}
