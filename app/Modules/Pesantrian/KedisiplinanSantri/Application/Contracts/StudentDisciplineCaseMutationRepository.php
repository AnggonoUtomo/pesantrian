<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts;

use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCaseData;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCaseMutationData;

interface StudentDisciplineCaseMutationRepository
{
    public function createCaseDraft(StudentDisciplineCaseMutationData $data, ?string $actorId): StudentDisciplineCaseData;

    public function updateCaseDraft(string $id, StudentDisciplineCaseMutationData $data, string $reason, ?string $actorId): ?StudentDisciplineCaseData;

    public function submitCaseDraft(string $id, string $actorId): ?StudentDisciplineCaseData;

    public function reviewCase(string $id, string $reviewNote, string $actorId): ?StudentDisciplineCaseData;

    public function assignCaseAction(string $id, string $actionPlan, ?string $assignedEmployeeId, ?string $assignedEmployeeName, string $actorId): ?StudentDisciplineCaseData;
}
