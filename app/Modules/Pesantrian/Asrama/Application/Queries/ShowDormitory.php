<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Application\Queries;

use App\Modules\Pesantrian\Asrama\Application\Contracts\AsramaReadRepository;
use App\Modules\Pesantrian\Asrama\Application\DTO\DormitoryData;

final readonly class ShowDormitory
{
    public function __construct(private AsramaReadRepository $repository) {}

    public function execute(string $id): ?DormitoryData
    {
        return $this->repository->findDormitory($id);
    }
}
