<?php

declare(strict_types=1);

namespace App\Modules\HumanResource\HumanResource\Application\Contracts;

use App\Modules\HumanResource\HumanResource\Application\DTO\EmployeeData;
use App\Modules\HumanResource\HumanResource\Application\DTO\EmployeeListFilter;
use App\Modules\HumanResource\HumanResource\Application\DTO\PaginatedEmployeeData;
use App\Modules\HumanResource\HumanResource\Application\DTO\UpsertEmployeeData;

interface HumanResourceRepository
{
    public function paginateEmployees(EmployeeListFilter $filter): PaginatedEmployeeData;

    public function createEmployee(UpsertEmployeeData $data): EmployeeData;

    /** @param array<string, string|null> $changes */
    public function updateEmployee(string $id, array $changes): ?EmployeeData;

    public function hasActiveUnitAssignments(string $employeeId): bool;
}
