<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Application\Actions;

use App\Modules\Pesantrian\Tahfidz\Application\Contracts\TahfidzActivityPublisher;
use App\Modules\Pesantrian\Tahfidz\Application\Contracts\TahfidzMutationRepository;
use App\Modules\Pesantrian\Tahfidz\Application\Contracts\TahfidzReadRepository;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\TahfidzSubmissionData;
use App\Modules\Pesantrian\Tahfidz\Application\Exceptions\TahfidzMutationException;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class ReviewTahfidzSubmission
{
    public function __construct(
        private TahfidzActivityPublisher $activities,
        private TahfidzReadRepository $reader,
        private TahfidzMutationRepository $repository,
    ) {}

    public function execute(?Authenticatable $actor, string $id, string $status, string $reason, ?string $correlationId = null): ?TahfidzSubmissionData
    {
        $submission = $this->reader->findSubmission($id);

        if ($submission === null) {
            return null;
        }

        if ($submission->status !== 'submitted') {
            throw new TahfidzMutationException('Hanya setoran submitted yang bisa direview.', [
                'status' => ['Setoran harus berstatus submitted.'],
            ]);
        }

        $actorId = $actor ? (string) $actor->getAuthIdentifier() : null;

        return $this->activities->publish(
            actorId: $actorId,
            action: 'tahfidz.submission.reviewed',
            subjectType: 'tahfidz_submission',
            mutation: fn (): ?TahfidzSubmissionData => $this->repository->reviewSubmission($id, $status, $reason, $actorId),
            subjectId: static fn (?TahfidzSubmissionData $submission): ?string => $submission?->id,
            metadata: static fn (?TahfidzSubmissionData $submission): array => [
                'changed_fields' => ['status', 'reviewed_at', 'reviewed_by', 'revision_history'],
                'to_status' => $status,
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
            'reviewed_at' => $submission->reviewedAt,
            'reviewed_by' => $submission->reviewedBy,
            'revision_count' => $submission->summary->revisionCount,
        ];
    }
}
