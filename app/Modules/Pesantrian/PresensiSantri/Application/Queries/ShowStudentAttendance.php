<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PresensiSantri\Application\Queries;

use App\Modules\Pesantrian\PresensiSantri\Application\Contracts\StudentAttendanceReadRepository;
use App\Modules\Pesantrian\PresensiSantri\Application\DTO\StudentAttendanceData;

final readonly class ShowStudentAttendance
{
    public function __construct(private StudentAttendanceReadRepository $repository) {}

    public function execute(string $id): ?StudentAttendanceData
    {
        return $this->repository->find($id);
    }
}
