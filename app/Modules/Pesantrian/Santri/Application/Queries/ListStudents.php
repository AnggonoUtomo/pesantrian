<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Santri\Application\Queries;

use App\Modules\Pesantrian\Santri\Application\Contracts\StudentRepository;
use App\Modules\Pesantrian\Santri\Application\DTO\PaginatedStudentData;
use App\Modules\Pesantrian\Santri\Application\DTO\StudentListFilter;

final readonly class ListStudents
{
    public function __construct(private StudentRepository $repository) {}

    public function execute(StudentListFilter $filter): PaginatedStudentData
    {
        return $this->repository->paginate($filter);
    }
}
