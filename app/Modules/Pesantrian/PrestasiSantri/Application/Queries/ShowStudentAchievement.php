<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Application\Queries;

use App\Modules\Pesantrian\PrestasiSantri\Application\Contracts\StudentAchievementReadRepository;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementData;

final readonly class ShowStudentAchievement
{
    public function __construct(private StudentAchievementReadRepository $repository) {}

    public function execute(string $id): ?StudentAchievementData
    {
        return $this->repository->find($id);
    }
}
