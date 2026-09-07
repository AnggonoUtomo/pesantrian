<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PerizinanSantri\Application\Queries;

use App\Modules\Pesantrian\PerizinanSantri\Application\Contracts\StudentPermitReadRepository;
use App\Modules\Pesantrian\PerizinanSantri\Application\DTO\PaginatedStudentPermitData;
use App\Modules\Pesantrian\PerizinanSantri\Application\DTO\StudentPermitListFilter;

final readonly class ListStudentPermits
{
    public function __construct(private StudentPermitReadRepository $repository) {}

    public function execute(StudentPermitListFilter $filter): PaginatedStudentPermitData
    {
        return $this->repository->paginate($filter);
    }
}
