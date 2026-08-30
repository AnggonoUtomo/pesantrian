<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PenerimaanSantri\Application\Contracts;

use App\Modules\Pesantrian\PenerimaanSantri\Application\DTO\PaginatedStudentAdmissionData;
use App\Modules\Pesantrian\PenerimaanSantri\Application\DTO\StudentAdmissionData;
use App\Modules\Pesantrian\PenerimaanSantri\Application\DTO\StudentAdmissionListFilter;
use App\Modules\Pesantrian\PenerimaanSantri\Application\DTO\UpsertStudentAdmissionData;

interface StudentAdmissionRepository
{
    public function paginate(StudentAdmissionListFilter $filter): PaginatedStudentAdmissionData;

    public function find(string $id): ?StudentAdmissionData;

    public function create(UpsertStudentAdmissionData $data): StudentAdmissionData;

    /** @param array<string, mixed> $changes */
    public function update(string $id, array $changes): ?StudentAdmissionData;
}
