<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Application\Queries;

use App\Modules\Pesantrian\Tahfidz\Application\Contracts\TahfidzReadRepository;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\TahfidzSubmissionData;

final readonly class ShowTahfidzSubmission
{
    public function __construct(private TahfidzReadRepository $repository) {}

    public function execute(string $id): ?TahfidzSubmissionData
    {
        return $this->repository->findSubmission($id);
    }
}
