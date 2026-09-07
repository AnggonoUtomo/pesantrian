<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Application\Actions;

use App\Modules\Pesantrian\Tahfidz\Application\Contracts\TahfidzActivityPublisher;
use App\Modules\Pesantrian\Tahfidz\Application\Contracts\TahfidzMutationRepository;
use App\Modules\Pesantrian\Tahfidz\Application\Contracts\TahfidzReadRepository;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\TahfidzSubmissionData;
use App\Modules\Pesantrian\Tahfidz\Application\Exceptions\TahfidzMutationException;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class VoidTahfidzSubmission
{
    public function __construct(
        private TahfidzActivityPublisher $activities,
        private TahfidzReadRepository $reader,
        private TahfidzMutationRepository $repository,
    ) {}

    public function execute(?Authenticatable $actor, string $id, string $reason, ?string $correlationId = null): ?TahfidzSubmissionData
    {
        $submission = $this->reader->findSubmission($id);

        if ($submission === null) {
            return null;
        }

        if ($submission->status === 'void') {
            throw new TahfidzMutationException('Setoran tahfidz void tidak bisa dibatalkan ulang.', [
                'status' => ['Setoran sudah berstatus void.'],
            ]);
        }

        $actorId = $actor ? (string) $actor->getAuthIdentifier() : null;

        return $this->activities->publish(
            actorId: $actorId,
            action: 'tahfidz.submission.voided',
            subjectType: 'tahfidz_submission',
            mutation: fn (): ?TahfidzSubmissionData => $this->repository->voidSubmission($id, $reason, $actorId),
            subjectId: static fn (?TahfidzSubmissionData $submission): ?string => $submission?->id,
            metadata: static fn (?TahfidzSubmissionData $submission): array => [
                'changed_fields' => ['status', 'voided_at', 'voided_by', 'void_reason', 'revision_history'],
                'to_status' => 'void',
                'result' => $submission instanceof TahfidzSubmissionData ? self::auditSubmission($submission) : null,
            ],
            reason: $reason,
            correlationId: $correlationId,
        );
    }

    /** @return array<string, mixed> */
    private static function auditSubmission(TahfidzSubmissionData $submission): array
    {
        return [
            'student_id' => $submission->studentId,
            'student_no' => $submission->studentNo,
            'student_name' => $submission->studentName,
            'submission_date' => $submission->submissionDate,
            'type' => $submission->type,
            'status' => $submission->status,
            'voided_at' => $submission->voidedAt,
            'voided_by' => $submission->voidedBy,
            'revision_count' => $submission->summary->revisionCount,
        ];
    }
}
