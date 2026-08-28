<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod\Application\Actions;

use App\Modules\Academic\AcademicPeriod\Application\Contracts\AcademicPeriodRepository;
use App\Modules\Academic\AcademicPeriod\Application\DTO\AcademicTermData;

final readonly class CloseAcademicTerm
{
    public function __construct(private AcademicPeriodRepository $repository) {}

    public function execute(string $id): ?AcademicTermData
    {
        return $this->repository->closeTerm($id);
    }
}
