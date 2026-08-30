<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PenerimaanSantri\Application\Actions;

use App\Modules\Pesantrian\PenerimaanSantri\Application\Contracts\StudentAdmissionRepository;
use App\Modules\Pesantrian\PenerimaanSantri\Application\DTO\StudentAdmissionData;
use App\Modules\Pesantrian\PenerimaanSantri\Application\DTO\UpsertStudentAdmissionData;

final readonly class CreateStudentAdmission
{
    public function __construct(private StudentAdmissionRepository $repository) {}

    public function execute(UpsertStudentAdmissionData $data): StudentAdmissionData
    {
        return $this->repository->create($data);
    }
}
