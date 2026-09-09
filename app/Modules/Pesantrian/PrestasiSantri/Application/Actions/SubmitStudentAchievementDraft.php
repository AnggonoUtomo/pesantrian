<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Application\Actions;

use App\Modules\Pesantrian\PrestasiSantri\Application\Contracts\StudentAchievementActivityPublisher;
use App\Modules\Pesantrian\PrestasiSantri\Application\Contracts\StudentAchievementMutationRepository;
use App\Modules\Pesantrian\PrestasiSantri\Application\Contracts\StudentAchievementReadRepository;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementData;
use App\Modules\Pesantrian\PrestasiSantri\Application\Exceptions\StudentAchievementMutationException;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class SubmitStudentAchievementDraft
{
    public function __construct(
        private StudentAchievementActivityPublisher $activities,
        private StudentAchievementReadRepository $reader,
        private StudentAchievementMutationRepository $repository,
    ) {}

    public function execute(?Authenticatable $actor, string $id, ?string $correlationId = null): ?StudentAchievementData
    {
        $achievement = $this->reader->find($id);

        if ($achievement === null) {
            return null;
        }

        if (! in_array($achievement->status, ['draft', 'needs_revision'], true)) {
            throw new StudentAchievementMutationException(
                'Prestasi hanya bisa disubmit dari status draft atau perlu revisi.',
                ['status' => ['Prestasi harus berstatus draft atau needs_revision.']],
            );
        }

        $actorId = $actor ? (string) $actor->getAuthIdentifier() : null;

        if ($actorId === null) {
            throw new StudentAchievementMutationException(
                'Actor wajib tersedia untuk submit prestasi.',
                ['actor' => ['Actor tidak tersedia.']],
            );
        }

        return $this->activities->publish(
            actorId: $actorId,
            action: 'prestasi_santri.achievement.submitted',
            subjectType: 'student_achievement',
            mutation: fn (): ?StudentAchievementData => $this->repository->submitDraft($id, $actorId),
            subjectId: static fn (?StudentAchievementData $achievement): ?string => $achievement?->id,
            metadata: static fn (?StudentAchievementData $achievement): array => [
                'changed_fields' => ['status', 'submitted_at', 'submitted_by'],
                'result' => $achievement instanceof StudentAchievementData ? self::auditAchievement($achievement) : null,
            ],
            correlationId: $correlationId,
        );
    }

    /** @return array<string, mixed> */
    private static function auditAchievement(StudentAchievementData $achievement): array
    {
        return [
            'achievement_no' => $achievement->achievementNo,
            'student_id' => $achievement->studentId,
            'student_no' => $achievement->studentNo,
            'student_name' => $achievement->studentName,
            'category_code' => $achievement->category->code,
            'title' => $achievement->title,
            'level' => $achievement->level,
            'result' => $achievement->result,
            'status' => $achievement->status,
        ];
    }
}
