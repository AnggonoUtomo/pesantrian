<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PerizinanSantri\Application\Contracts;

use App\Modules\Pesantrian\PerizinanSantri\Application\DTO\StudentPermitData;
use App\Modules\Pesantrian\PerizinanSantri\Application\DTO\StudentPermitMutationData;

interface StudentPermitMutationRepository
{
    public function createDraft(StudentPermitMutationData $data, ?string $actorId): StudentPermitData;

    public function updateDraft(string $id, StudentPermitMutationData $data, string $reason, ?string $actorId): ?StudentPermitData;

    public function submitDraft(string $id, string $actorId): ?StudentPermitData;

    public function approve(string $id, ?string $reviewNote, string $actorId): ?StudentPermitData;

    public function reject(string $id, string $reason, string $actorId): ?StudentPermitData;

    public function checkout(string $id, string $actorId): ?StudentPermitData;

    public function returnPermit(string $id, string $returnedAt, ?string $returnNote, string $actorId): ?StudentPermitData;

    public function void(string $id, string $reason, string $actorId): ?StudentPermitData;

    public function hasActiveOverlap(string $studentId, string $startsAt, string $endsAt, ?string $exceptId = null): bool;
}
