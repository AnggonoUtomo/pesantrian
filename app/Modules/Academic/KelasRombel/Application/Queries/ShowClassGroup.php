<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Application\Queries;

use App\Modules\Academic\KelasRombel\Application\Contracts\KelasRombelReadRepository;
use App\Modules\Academic\KelasRombel\Application\DTO\ClassGroupData;

final readonly class ShowClassGroup
{
    public function __construct(private KelasRombelReadRepository $repository) {}

    public function execute(string $id): ?ClassGroupData
    {
        return $this->repository->findClassGroup($id);
    }
}
