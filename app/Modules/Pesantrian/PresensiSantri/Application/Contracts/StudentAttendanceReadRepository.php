<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PresensiSantri\Application\Contracts;

use App\Modules\Pesantrian\PresensiSantri\Application\DTO\PaginatedStudentAttendanceData;
use App\Modules\Pesantrian\PresensiSantri\Application\DTO\StudentAttendanceData;
use App\Modules\Pesantrian\PresensiSantri\Application\DTO\StudentAttendanceListFilter;

interface StudentAttendanceReadRepository
{
    public function paginate(StudentAttendanceListFilter $filter): PaginatedStudentAttendanceData;

    public function find(string $id): ?StudentAttendanceData;
}
