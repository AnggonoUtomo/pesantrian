<?php

declare(strict_types=1);

namespace App\Modules\HumanResource\HumanResource\Application\Actions;

use App\Modules\HumanResource\HumanResource\Application\Contracts\HumanResourceRepository;
use App\Modules\HumanResource\HumanResource\Application\DTO\EmployeeData;

final readonly class ActivateEmployee
{
    public function __construct(private HumanResourceRepository $repository) {}

    public function execute(string $id): ?EmployeeData
    {
        return $this->repository->updateEmployee($id, [
            'status' => 'active',
            'left_on' => null,
        ]);
    }
}
