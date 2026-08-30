<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PenerimaanSantri\Application\Actions;

use App\Modules\Pesantrian\PenerimaanSantri\Application\Contracts\StudentAdmissionRepository;
use App\Modules\Pesantrian\PenerimaanSantri\Application\DTO\StudentAdmissionData;
use App\Modules\Pesantrian\PenerimaanSantri\Domain\Services\StudentAdmissionLifecycle;
use Illuminate\Validation\ValidationException;

final readonly class TransitionStudentAdmission
{
    public function __construct(
        private StudentAdmissionRepository $repository,
        private StudentAdmissionLifecycle $lifecycle,
    ) {}

    public function execute(string $id, string $targetStatus, string $actorId): ?StudentAdmissionData
    {
        $admission = $this->repository->find($id);

        if ($admission === null) {
            return null;
        }

        if ($this->lifecycle->isTerminal($admission->status)) {
            throw ValidationException::withMessages([
                'status' => ["Status {$admission->status} bersifat terminal dan tidak dapat diproses lagi."],
            ]);
        }

        if (! $this->lifecycle->canTransition($admission->status, $targetStatus)) {
            throw ValidationException::withMessages([
                'status' => ["Status {$admission->status} tidak dapat diubah menjadi {$targetStatus}."],
            ]);
        }

        return $this->repository->update($id, [
            'status' => $targetStatus,
            'decided_at' => now(),
            'decided_by' => $actorId,
        ]);
    }
}
