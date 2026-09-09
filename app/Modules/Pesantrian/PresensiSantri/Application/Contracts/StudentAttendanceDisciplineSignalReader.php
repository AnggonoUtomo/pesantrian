<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PresensiSantri\Application\Contracts;

use App\Modules\Pesantrian\PresensiSantri\Application\DTO\StudentAttendanceDisciplineSignalData;

interface StudentAttendanceDisciplineSignalReader
{
    /**
     * Membaca sinyal presensi yang mungkin perlu direview sebagai kandidat kasus
     * kedisiplinan. Method ini read-only dan tidak membuat kasus.
     *
     * @return list<StudentAttendanceDisciplineSignalData>
     */
    public function signals(?string $dateFrom = null, ?string $dateTo = null, ?string $studentId = null, int $limit = 50): array;
}
