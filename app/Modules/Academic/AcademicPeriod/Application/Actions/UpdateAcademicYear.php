<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod\Application\Actions;

use App\Modules\Academic\AcademicPeriod\Application\Contracts\AcademicPeriodRepository;
use App\Modules\Academic\AcademicPeriod\Application\DTO\AcademicYearData;

final readonly class UpdateAcademicYear
{
    public function __construct(private AcademicPeriodRepository $repository) {}

    /** @param array<string, string> $changes */
    public function execute(string $id, array $changes): ?AcademicYearData
    {
        return $this->repository->updateYear($id, $changes);
    }
}
