<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PenerimaanSantri\Application\Queries;

use App\Modules\Pesantrian\PenerimaanSantri\Application\Contracts\StudentAdmissionRepository;
use App\Modules\Pesantrian\PenerimaanSantri\Application\DTO\PaginatedStudentAdmissionData;
use App\Modules\Pesantrian\PenerimaanSantri\Application\DTO\StudentAdmissionListFilter;

final readonly class ListStudentAdmissions
{
    public function __construct(private StudentAdmissionRepository $repository) {}

    public function execute(StudentAdmissionListFilter $filter): PaginatedStudentAdmissionData
    {
        return $this->repository->paginate($filter);
    }
}
