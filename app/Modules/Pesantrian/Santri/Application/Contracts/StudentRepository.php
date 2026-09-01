<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Santri\Application\Contracts;

use App\Modules\Pesantrian\PenerimaanSantri\Application\DTO\AcceptedAdmissionData;
use App\Modules\Pesantrian\Santri\Application\DTO\PaginatedStudentData;
use App\Modules\Pesantrian\Santri\Application\DTO\StudentData;
use App\Modules\Pesantrian\Santri\Application\DTO\StudentListFilter;
use App\Modules\Pesantrian\Santri\Application\DTO\UpsertStudentData;

interface StudentRepository
{
    public function paginate(StudentListFilter $filter): PaginatedStudentData;

    public function find(string $id): ?StudentData;

    public function findArchived(string $id): ?StudentData;

    public function create(UpsertStudentData $data): StudentData;

    public function createFromAcceptedAdmission(AcceptedAdmissionData $data): StudentData;

    public function existsForAdmission(string $admissionId): bool;

    public function changeStatus(string $id, string $status, ?string $reason, ?string $actorId): ?StudentData;

    public function archive(string $id, ?string $actorId): ?StudentData;

    public function restore(string $id): ?StudentData;

    /**
     * @param  array<string, mixed>  $studentChanges
     * @param  array<string, mixed>  $guardianChanges
     */
    public function update(string $id, array $studentChanges, array $guardianChanges): ?StudentData;
}
