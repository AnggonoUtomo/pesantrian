<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PenerimaanSantri\Application\Actions;

use App\Modules\Pesantrian\PenerimaanSantri\Application\Contracts\StudentAdmissionRepository;
use App\Modules\Pesantrian\PenerimaanSantri\Application\DTO\StudentAdmissionData;

final readonly class UpdateStudentAdmission
{
    public function __construct(private StudentAdmissionRepository $repository) {}

    /** @param array<string, mixed> $changes */
    public function execute(string $id, array $changes): ?StudentAdmissionData
    {
        if ($this->repository->find($id) === null) {
            return null;
        }

        return $this->repository->update($id, $changes);
    }
}
