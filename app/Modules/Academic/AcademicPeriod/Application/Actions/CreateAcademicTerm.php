<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod\Application\Actions;

use App\Modules\Academic\AcademicPeriod\Application\Contracts\AcademicPeriodRepository;
use App\Modules\Academic\AcademicPeriod\Application\DTO\AcademicTermData;
use App\Modules\Academic\AcademicPeriod\Application\DTO\UpsertAcademicTermData;

final readonly class CreateAcademicTerm
{
    public function __construct(private AcademicPeriodRepository $repository) {}

    public function execute(UpsertAcademicTermData $data): AcademicTermData
    {
        return $this->repository->createTerm($data);
    }
}
