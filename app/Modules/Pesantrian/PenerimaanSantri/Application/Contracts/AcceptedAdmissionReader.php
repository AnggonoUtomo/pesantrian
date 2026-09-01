<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PenerimaanSantri\Application\Contracts;

use App\Modules\Pesantrian\PenerimaanSantri\Application\DTO\AcceptedAdmissionData;

interface AcceptedAdmissionReader
{
    public function findAcceptedForConversion(string $admissionId): ?AcceptedAdmissionData;
}
