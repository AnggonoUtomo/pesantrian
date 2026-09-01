<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Santri\Application\Queries;

use App\Modules\Pesantrian\Santri\Application\Contracts\StudentRepository;
use App\Modules\Pesantrian\Santri\Application\DTO\StudentData;

final readonly class ShowStudent
{
    public function __construct(private StudentRepository $repository) {}

    public function execute(string $id): ?StudentData
    {
        return $this->repository->find($id);
    }
}
