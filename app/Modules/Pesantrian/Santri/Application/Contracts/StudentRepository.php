<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Santri\Application\Contracts;

use App\Modules\Pesantrian\Santri\Application\DTO\PaginatedStudentData;
use App\Modules\Pesantrian\Santri\Application\DTO\StudentData;
use App\Modules\Pesantrian\Santri\Application\DTO\StudentListFilter;

interface StudentRepository
{
    public function paginate(StudentListFilter $filter): PaginatedStudentData;

    public function find(string $id): ?StudentData;
}
