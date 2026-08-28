<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod\Application\Actions;

use App\Modules\Academic\AcademicPeriod\Application\Contracts\AcademicPeriodRepository;
use App\Modules\Academic\AcademicPeriod\Application\DTO\AcademicTermData;
use App\Modules\Academic\AcademicPeriod\Application\Exceptions\AcademicPeriodLifecycleException;

final readonly class ActivateAcademicTerm
{
    public function __construct(private AcademicPeriodRepository $repository) {}

    public function execute(string $id): ?AcademicTermData
    {
        $term = $this->repository->findTerm($id);

        if ($term === null) {
            return null;
        }

        if ($term->status === 'closed') {
            throw new AcademicPeriodLifecycleException(
                'Term akademik tidak bisa diaktifkan.',
                ['term' => ['Closed term tidak bisa dijadikan active period.']],
            );
        }

        return $this->repository->activateTerm($id);
    }
}
