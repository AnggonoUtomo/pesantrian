<?php

declare(strict_types=1);

namespace App\Modules\HumanResource\HumanResource\Application\Actions;

use App\Modules\HumanResource\HumanResource\Application\Contracts\HumanResourceRepository;
use App\Modules\HumanResource\HumanResource\Application\DTO\EmployeeData;
use Illuminate\Validation\ValidationException;

final readonly class DeactivateEmployee
{
    public function __construct(private HumanResourceRepository $repository) {}

    /**
     * @throws ValidationException
     */
    public function execute(string $id, string $leftOn): ?EmployeeData
    {
        if ($this->repository->hasActiveUnitAssignments($id)) {
            throw ValidationException::withMessages([
                'employee' => 'Employee masih memiliki assignment unit aktif. Tutup assignment aktif lebih dulu.',
            ]);
        }

        return $this->repository->updateEmployee($id, [
            'status' => 'inactive',
            'left_on' => $leftOn,
        ]);
    }
}
