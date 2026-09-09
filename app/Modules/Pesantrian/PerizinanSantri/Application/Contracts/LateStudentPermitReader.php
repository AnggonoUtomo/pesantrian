<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PerizinanSantri\Application\Contracts;

use App\Modules\Pesantrian\PerizinanSantri\Application\DTO\StudentPermitDisciplineSignalData;

interface LateStudentPermitReader
{
    /**
     * Membaca izin yang terlambat kembali atau sedang overdue sebagai kandidat
     * review kedisiplinan. Method ini read-only dan tidak membuat kasus.
     *
     * @return list<StudentPermitDisciplineSignalData>
     */
    public function lateReturns(?string $dateFrom = null, ?string $dateTo = null, ?string $studentId = null, int $limit = 50): array;
}
