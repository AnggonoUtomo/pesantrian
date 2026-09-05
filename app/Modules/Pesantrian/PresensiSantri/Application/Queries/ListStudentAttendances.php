<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PresensiSantri\Application\Queries;

use App\Modules\Pesantrian\PresensiSantri\Application\Contracts\StudentAttendanceReadRepository;
use App\Modules\Pesantrian\PresensiSantri\Application\DTO\PaginatedStudentAttendanceData;
use App\Modules\Pesantrian\PresensiSantri\Application\DTO\StudentAttendanceListFilter;

final readonly class ListStudentAttendances
{
    public function __construct(private StudentAttendanceReadRepository $repository) {}

    public function execute(StudentAttendanceListFilter $filter): PaginatedStudentAttendanceData
    {
        return $this->repository->paginate($filter);
    }
}
