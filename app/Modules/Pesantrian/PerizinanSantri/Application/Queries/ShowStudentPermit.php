<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PerizinanSantri\Application\Queries;

use App\Modules\Pesantrian\PerizinanSantri\Application\Contracts\StudentPermitReadRepository;
use App\Modules\Pesantrian\PerizinanSantri\Application\DTO\StudentPermitData;

final readonly class ShowStudentPermit
{
    public function __construct(private StudentPermitReadRepository $repository) {}

    public function execute(string $id): ?StudentPermitData
    {
        return $this->repository->find($id);
    }
}
