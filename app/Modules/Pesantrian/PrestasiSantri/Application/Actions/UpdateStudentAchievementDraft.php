<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Application\Actions;

use App\Modules\Academic\AcademicPeriod\Application\Contracts\ActiveAcademicPeriodReader;
use App\Modules\Academic\AcademicPeriod\Application\DTO\ActiveAcademicPeriodData;
use App\Modules\HumanResource\HumanResource\Application\Contracts\ActiveEmployeeReader;
use App\Modules\Pesantrian\PrestasiSantri\Application\Contracts\StudentAchievementActivityPublisher;
use App\Modules\Pesantrian\PrestasiSantri\Application\Contracts\StudentAchievementCategoryMutationRepository;
use App\Modules\Pesantrian\PrestasiSantri\Application\Contracts\StudentAchievementMutationRepository;
use App\Modules\Pesantrian\PrestasiSantri\Application\Contracts\StudentAchievementReadRepository;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementData;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementMutationData;
use App\Modules\Pesantrian\PrestasiSantri\Application\Exceptions\StudentAchievementMutationException;
use App\Modules\Pesantrian\Santri\Application\Contracts\ActiveStudentReader;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class UpdateStudentAchievementDraft
{
    public function __construct(
        private StudentAchievementActivityPublisher $activities,
        private StudentAchievementReadRepository $reader,
        private StudentAchievementMutationRepository $repository,
        private StudentAchievementCategoryMutationRepository $categories,
        private ActiveStudentReader $students,
        private ActiveEmployeeReader $employees,
        private ActiveAcademicPeriodReader $periods,
    ) {}

    public function execute(?Authenticatable $actor, string $id, StudentAchievementMutationData $data, string $reason, ?string $correlationId = null): ?StudentAchievementData
    {
        $achievement = $this->reader->find($id);

        if ($achievement === null) {
            return null;
        }

        if ($achievement->status !== 'draft') {
            throw new StudentAchievementMutationException(
                'Prestasi yang sudah disubmit atau final tidak bisa diedit langsung.',
                ['status' => ['Prestasi harus berstatus draft.']],
            );
        }

        $normalized = $this->normalize($data);
        $actorId = $actor ? (string) $actor->getAuthIdentifier() : null;

        return $this->activities->publish(
            actorId: $actorId,
            action: 'prestasi_santri.achievement.updated',
            subjectType: 'student_achievement',
            mutation: fn (): ?StudentAchievementData => $this->repository->updateDraft($id, $normalized, $reason, $actorId),
            subjectId: static fn (?StudentAchievementData $achievement): ?string => $achievement?->id,
            metadata: static fn (?StudentAchievementData $achievement): array => [
                'changed_fields' => array_keys($normalized->toDatabasePayload()),
                'result' => $achievement instanceof StudentAchievementData ? self::auditAchievement($achievement) : null,
            ],
            reason: $reason,
            correlationId: $correlationId,
        );
    }

    private function normalize(StudentAchievementMutationData $data): StudentAchievementMutationData
    {
        $studentId = $data->studentId;
        $studentNo = null;
        $studentName = null;

        if ($studentId !== null) {
            $student = $this->students->findActive($studentId);

            if ($student === null) {
                throw new StudentAchievementMutationException(
                    'Prestasi hanya boleh memakai santri aktif.',
                    ['student_id' => ['Santri tidak aktif atau tidak ditemukan.']],
                );
            }

            $studentId = $student->id;
            $studentNo = $student->studentNo;
            $studentName = $student->fullName;
        }

        $categoryId = $data->categoryId;
        $categoryName = null;

        if ($categoryId !== null) {
            $category = $this->categories->findActiveCategory($categoryId);

            if ($category === null) {
                throw new StudentAchievementMutationException(
                    'Prestasi wajib memakai kategori aktif.',
                    ['category_id' => ['Kategori tidak aktif atau tidak ditemukan.']],
                );
            }

            $categoryId = $category->id;
            $categoryName = $category->name;
        }

        $mentorEmployeeId = $data->mentorEmployeeId;
        $mentorName = null;

        if ($mentorEmployeeId !== null) {
            $mentor = $this->employees->findActive($mentorEmployeeId);

            if ($mentor === null) {
                throw new StudentAchievementMutationException(
                    'Pembina prestasi harus pegawai aktif.',
                    ['mentor_employee_id' => ['Pegawai tidak aktif atau tidak ditemukan.']],
                );
            }

            $mentorEmployeeId = $mentor->id;
            $mentorName = $mentor->name;
        }

        $period = $data->academicPeriodId === null ? null : $this->activePeriod($data->academicPeriodId);

        return new StudentAchievementMutationData(
            categoryId: $categoryId,
            categoryName: $categoryName,
            studentId: $studentId,
            studentNo: $studentNo,
            studentName: $studentName,
            academicPeriodId: $period?->termId,
            academicPeriodLabel: $period === null ? null : self::periodLabel($period),
            mentorEmployeeId: $mentorEmployeeId,
            mentorName: $mentorName,
            title: $data->title,
            achievementType: $data->achievementType,
            level: $data->level,
            result: $data->result,
            organizer: $data->organizer,
            eventName: $data->eventName,
            eventLocation: $data->eventLocation,
            achievedOn: $data->achievedOn,
            periodStartedOn: $data->periodStartedOn,
            periodEndedOn: $data->periodEndedOn,
            description: $data->description,
            notes: $data->notes,
        );
    }

    private function activePeriod(string $expectedPeriodId): ActiveAcademicPeriodData
    {
        $period = $this->periods->current();

        if ($period === null || $period->termId !== $expectedPeriodId) {
            throw new StudentAchievementMutationException(
                'Periode akademik prestasi harus periode aktif.',
                ['academic_period_id' => ['Periode akademik tidak aktif atau tidak ditemukan.']],
            );
        }

        return $period;
    }

    private static function periodLabel(ActiveAcademicPeriodData $period): string
    {
        return trim($period->academicYearName.' '.$period->termName);
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
