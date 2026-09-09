<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Application\Actions;

use App\Modules\Academic\AcademicPeriod\Application\Contracts\ActiveAcademicPeriodReader;
use App\Modules\Academic\AcademicPeriod\Application\DTO\ActiveAcademicPeriodData;
use App\Modules\HumanResource\HumanResource\Application\Contracts\ActiveEmployeeReader;
use App\Modules\Pesantrian\PrestasiSantri\Application\Contracts\StudentAchievementActivityPublisher;
use App\Modules\Pesantrian\PrestasiSantri\Application\Contracts\StudentAchievementCategoryMutationRepository;
use App\Modules\Pesantrian\PrestasiSantri\Application\Contracts\StudentAchievementMutationRepository;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementData;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementMutationData;
use App\Modules\Pesantrian\PrestasiSantri\Application\Exceptions\StudentAchievementMutationException;
use App\Modules\Pesantrian\Santri\Application\Contracts\ActiveStudentReader;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class CreateStudentAchievementDraft
{
    public function __construct(
        private StudentAchievementActivityPublisher $activities,
        private StudentAchievementMutationRepository $repository,
        private StudentAchievementCategoryMutationRepository $categories,
        private ActiveStudentReader $students,
        private ActiveEmployeeReader $employees,
        private ActiveAcademicPeriodReader $periods,
    ) {}

    public function execute(?Authenticatable $actor, StudentAchievementMutationData $data, ?string $correlationId = null): StudentAchievementData
    {
        $actorId = $actor ? (string) $actor->getAuthIdentifier() : null;
        $normalized = $this->normalize($data);

        return $this->activities->publish(
            actorId: $actorId,
            action: 'prestasi_santri.achievement.created',
            subjectType: 'student_achievement',
            mutation: fn (): StudentAchievementData => $this->repository->createDraft($normalized, $actorId),
            subjectId: static fn (StudentAchievementData $achievement): string => $achievement->id,
            metadata: static fn (StudentAchievementData $achievement): array => [
                'changed_fields' => ['category_id', 'student_id', 'student_no', 'student_name', 'academic_period_id', 'mentor_employee_id', 'title', 'achievement_type', 'level', 'result', 'organizer', 'event_name', 'event_location', 'achieved_on', 'period_started_on', 'period_ended_on', 'description', 'notes', 'status'],
                'result' => self::auditAchievement($achievement),
            ],
            correlationId: $correlationId,
        );
    }

    private function normalize(StudentAchievementMutationData $data): StudentAchievementMutationData
    {
        $student = $data->studentId === null ? null : $this->students->findActive($data->studentId);

        if ($student === null) {
            throw new StudentAchievementMutationException(
                'Prestasi hanya boleh dibuat untuk santri aktif.',
                ['student_id' => ['Santri tidak aktif atau tidak ditemukan.']],
            );
        }

        $category = $data->categoryId === null ? null : $this->categories->findActiveCategory($data->categoryId);

        if ($category === null) {
            throw new StudentAchievementMutationException(
                'Prestasi wajib memakai kategori aktif.',
                ['category_id' => ['Kategori tidak aktif atau tidak ditemukan.']],
            );
        }

        $mentor = $data->mentorEmployeeId === null ? null : $this->employees->findActive($data->mentorEmployeeId);

        if ($data->mentorEmployeeId !== null && $mentor === null) {
            throw new StudentAchievementMutationException(
                'Pembina prestasi harus pegawai aktif.',
                ['mentor_employee_id' => ['Pegawai tidak aktif atau tidak ditemukan.']],
            );
        }

        $period = $this->activePeriod($data->academicPeriodId);

        return new StudentAchievementMutationData(
            categoryId: $category->id,
            categoryName: $category->name,
            studentId: $student->id,
            studentNo: $student->studentNo,
            studentName: $student->fullName,
            academicPeriodId: $period?->termId,
            academicPeriodLabel: $period === null ? null : self::periodLabel($period),
            mentorEmployeeId: $mentor?->id,
            mentorName: $mentor?->name,
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

    private function activePeriod(?string $expectedPeriodId): ?ActiveAcademicPeriodData
    {
        $period = $this->periods->current();

        if ($expectedPeriodId !== null && ($period === null || $period->termId !== $expectedPeriodId)) {
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
