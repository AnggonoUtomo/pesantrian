<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod\Application\Actions;

use App\Modules\Academic\AcademicPeriod\Application\Contracts\AcademicPeriodRepository;
use App\Modules\Academic\AcademicPeriod\Application\DTO\AcademicTermData;

final readonly class UpdateAcademicTerm
{
    public function __construct(private AcademicPeriodRepository $repository) {}

    /** @param array<string, string|int|bool> $changes */
    public function execute(string $id, array $changes): ?AcademicTermData
    {
        return $this->repository->updateTerm($id, $changes);
    }
}
