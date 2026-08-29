<?php

declare(strict_types=1);

namespace App\Modules\HumanResource\HumanResource\Application\Actions;

use App\Modules\HumanResource\HumanResource\Application\Contracts\HumanResourceRepository;
use App\Modules\HumanResource\HumanResource\Application\DTO\EmployeeData;

final readonly class UpdateEmployee
{
    public function __construct(private HumanResourceRepository $repository) {}

    /** @param array<string, string|null> $changes */
    public function execute(string $id, array $changes): ?EmployeeData
    {
        return $this->repository->updateEmployee($id, $changes);
    }
}
