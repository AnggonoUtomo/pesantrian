<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod\Application\Queries;

use App\Modules\Academic\AcademicPeriod\Application\Contracts\AcademicPeriodRepository;
use App\Modules\Academic\AcademicPeriod\Application\DTO\AcademicTermData;

final readonly class GetCurrentAcademicTerm
{
    public function __construct(private AcademicPeriodRepository $repository) {}

    public function execute(): ?AcademicTermData
    {
        return $this->repository->currentActiveTerm();
    }
}
