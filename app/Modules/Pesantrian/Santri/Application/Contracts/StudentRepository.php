<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Santri\Application\Contracts;

use App\Modules\Pesantrian\Santri\Application\DTO\PaginatedStudentData;
use App\Modules\Pesantrian\Santri\Application\DTO\StudentData;
use App\Modules\Pesantrian\Santri\Application\DTO\StudentListFilter;
use App\Modules\Pesantrian\Santri\Application\DTO\UpsertStudentData;

interface StudentRepository
{
    public function paginate(StudentListFilter $filter): PaginatedStudentData;

    public function find(string $id): ?StudentData;

    public function create(UpsertStudentData $data): StudentData;

    /**
     * @param  array<string, mixed>  $studentChanges
     * @param  array<string, mixed>  $guardianChanges
     */
    public function update(string $id, array $studentChanges, array $guardianChanges): ?StudentData;
}
