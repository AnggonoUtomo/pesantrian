<?php

declare(strict_types=1);

namespace App\Modules\HumanResource\HumanResource\Application\Actions;

use App\Modules\HumanResource\HumanResource\Application\Contracts\HumanResourceRepository;
use App\Modules\HumanResource\HumanResource\Application\DTO\EmployeeData;
use App\Modules\HumanResource\HumanResource\Application\DTO\UpsertEmployeeData;

final readonly class CreateEmployee
{
    public function __construct(private HumanResourceRepository $repository) {}

    public function execute(UpsertEmployeeData $data): EmployeeData
    {
        return $this->repository->createEmployee($data);
    }
}
