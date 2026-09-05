<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PresensiSantri\Application\DTO;

final readonly class PaginatedStudentAttendanceData
{
    /** @param list<StudentAttendanceData> $data */
    public function __construct(
        public array $data,
        public int $currentPage,
        public int $perPage,
        public int $total,
        public int $lastPage,
    ) {}
}
