<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Application\Actions;

use App\Modules\Pesantrian\PrestasiSantri\Application\Contracts\StudentAchievementActivityPublisher;
use App\Modules\Pesantrian\PrestasiSantri\Application\Contracts\StudentAchievementMutationRepository;
use App\Modules\Pesantrian\PrestasiSantri\Application\Contracts\StudentAchievementReadRepository;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementData;
use App\Modules\Pesantrian\PrestasiSantri\Application\Exceptions\StudentAchievementMutationException;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class VerifyStudentAchievement
{
    public function __construct(
        private StudentAchievementActivityPublisher $activities,
        private StudentAchievementReadRepository $reader,
        private StudentAchievementMutationRepository $repository,
    ) {}

    public function execute(?Authenticatable $actor, string $id, ?string $verificationNote, ?string $correlationId = null): ?StudentAchievementData
    {
        $achievement = $this->reader->find($id);

        if ($achievement === null) {
            return null;
        }

        if ($achievement->status !== 'submitted') {
            throw new StudentAchievementMutationException(
                'Prestasi hanya bisa diverifikasi setelah disubmit.',
                ['status' => ['Prestasi harus berstatus submitted.']],
            );
        }

        $actorId = $actor ? (string) $actor->getAuthIdentifier() : null;

        if ($actorId === null) {
            throw new StudentAchievementMutationException(
                'Actor wajib tersedia untuk verifikasi prestasi.',
                ['actor' => ['Actor tidak tersedia.']],
            );
        }

        return $this->activities->publish(
            actorId: $actorId,
            action: 'prestasi_santri.achievement.verified',
            subjectType: 'student_achievement',
            mutation: fn (): ?StudentAchievementData => $this->repository->verify($id, $verificationNote, $actorId),
            subjectId: static fn (?StudentAchievementData $achievement): ?string => $achievement?->id,
            metadata: static fn (?StudentAchievementData $achievement): array => [
                'changed_fields' => ['status', 'verified_at', 'verified_by', 'verification_note'],
                'result' => $achievement instanceof StudentAchievementData ? self::auditAchievement($achievement) : null,
            ],
            reason: $verificationNote,
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
