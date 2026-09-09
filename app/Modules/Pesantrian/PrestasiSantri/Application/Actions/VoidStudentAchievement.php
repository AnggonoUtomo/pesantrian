<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Application\Actions;

use App\Modules\Pesantrian\PrestasiSantri\Application\Contracts\StudentAchievementActivityPublisher;
use App\Modules\Pesantrian\PrestasiSantri\Application\Contracts\StudentAchievementMutationRepository;
use App\Modules\Pesantrian\PrestasiSantri\Application\Contracts\StudentAchievementReadRepository;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementData;
use App\Modules\Pesantrian\PrestasiSantri\Application\Exceptions\StudentAchievementMutationException;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class VoidStudentAchievement
{
    public function __construct(
        private StudentAchievementActivityPublisher $activities,
        private StudentAchievementReadRepository $reader,
        private StudentAchievementMutationRepository $repository,
    ) {}

    public function execute(?Authenticatable $actor, string $id, string $voidReason, ?string $correlationId = null): ?StudentAchievementData
    {
        $achievement = $this->reader->find($id);

        if ($achievement === null) {
            return null;
        }

        if ($achievement->status === 'void') {
            throw new StudentAchievementMutationException(
                'Prestasi sudah dibatalkan.',
                ['status' => ['Prestasi sudah berstatus void.']],
            );
        }

        $actorId = $actor ? (string) $actor->getAuthIdentifier() : null;

        if ($actorId === null) {
            throw new StudentAchievementMutationException(
                'Actor wajib tersedia untuk membatalkan prestasi.',
                ['actor' => ['Actor tidak tersedia.']],
            );
        }

        return $this->activities->publish(
            actorId: $actorId,
            action: 'prestasi_santri.achievement.voided',
            subjectType: 'student_achievement',
            mutation: fn (): ?StudentAchievementData => $this->repository->void($id, $voidReason, $actorId),
            subjectId: static fn (?StudentAchievementData $achievement): ?string => $achievement?->id,
            metadata: static fn (?StudentAchievementData $achievement): array => [
                'changed_fields' => ['status', 'voided_at', 'voided_by', 'void_reason'],
                'result' => $achievement instanceof StudentAchievementData ? self::auditAchievement($achievement) : null,
            ],
            reason: $voidReason,
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
