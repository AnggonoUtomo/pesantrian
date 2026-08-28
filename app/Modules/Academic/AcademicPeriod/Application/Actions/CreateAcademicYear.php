<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod\Application\Actions;

use App\Modules\Academic\AcademicPeriod\Application\Contracts\AcademicPeriodRepository;
use App\Modules\Academic\AcademicPeriod\Application\DTO\AcademicYearData;
use App\Modules\Academic\AcademicPeriod\Application\DTO\UpsertAcademicYearData;

final readonly class CreateAcademicYear
{
    public function __construct(private AcademicPeriodRepository $repository) {}

    public function execute(UpsertAcademicYearData $data): AcademicYearData
    {
        return $this->repository->createYear($data);
    }
}
