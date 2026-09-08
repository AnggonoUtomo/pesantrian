<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Application\Queries;

use App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts\StudentDisciplineReadRepository;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCaseData;

final readonly class ShowStudentDisciplineCase
{
    public function __construct(private StudentDisciplineReadRepository $repository) {}

    public function execute(string $id): ?StudentDisciplineCaseData
    {
        return $this->repository->findCase($id);
    }
}
