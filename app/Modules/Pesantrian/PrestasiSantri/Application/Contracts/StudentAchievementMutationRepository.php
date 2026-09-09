<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Application\Contracts;

use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementData;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementMutationData;

interface StudentAchievementMutationRepository
{
    public function createDraft(StudentAchievementMutationData $data, ?string $actorId): StudentAchievementData;

    public function updateDraft(string $id, StudentAchievementMutationData $data, string $reason, ?string $actorId): ?StudentAchievementData;

    public function submitDraft(string $id, string $actorId): ?StudentAchievementData;

    public function verify(string $id, ?string $verificationNote, string $actorId): ?StudentAchievementData;

    public function requestRevision(string $id, string $verificationNote, string $actorId): ?StudentAchievementData;

    public function void(string $id, string $voidReason, string $actorId): ?StudentAchievementData;
}
