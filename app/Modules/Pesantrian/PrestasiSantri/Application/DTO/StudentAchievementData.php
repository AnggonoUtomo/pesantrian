<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Application\DTO;

final readonly class StudentAchievementData
{
    /** @param list<StudentAchievementRevisionData> $revisions */
    public function __construct(
        public string $id,
        public string $achievementNo,
        public StudentAchievementCategoryData $category,
        public string $studentId,
        public string $studentNo,
        public string $studentName,
        public ?string $academicPeriodId,
        public ?string $academicPeriodLabel,
        public ?string $mentorEmployeeId,
        public ?string $mentorName,
        public string $title,
        public string $achievementType,
        public string $level,
        public string $result,
        public ?string $organizer,
        public ?string $eventName,
        public ?string $eventLocation,
        public ?string $achievedOn,
        public ?string $periodStartedOn,
        public ?string $periodEndedOn,
        public ?string $description,
        public ?string $notes,
        public string $status,
        public ?string $submittedAt,
        public ?string $submittedBy,
        public ?string $verifiedAt,
        public ?string $verifiedBy,
        public ?string $verificationNote,
        public ?string $voidedAt,
        public ?string $voidedBy,
        public ?string $voidReason,
        public ?string $createdBy,
        public ?string $createdAt,
        public ?string $updatedAt,
        public StudentAchievementSummaryData $summary,
        public array $revisions = [],
    ) {}

    /** @return array<string, mixed> */
    public function toArray(bool $includeRevisions = true): array
    {
        $data = [
            'id' => $this->id,
            'achievement_no' => $this->achievementNo,
            'category' => $this->category->toArray(),
            'student_id' => $this->studentId,
            'student_no' => $this->studentNo,
            'student_name' => $this->studentName,
            'academic_period_id' => $this->academicPeriodId,
            'academic_period_label' => $this->academicPeriodLabel,
            'mentor_employee_id' => $this->mentorEmployeeId,
            'mentor_name' => $this->mentorName,
            'title' => $this->title,
            'achievement_type' => $this->achievementType,
            'level' => $this->level,
            'result' => $this->result,
            'organizer' => $this->organizer,
            'event_name' => $this->eventName,
            'event_location' => $this->eventLocation,
            'achieved_on' => $this->achievedOn,
            'period_started_on' => $this->periodStartedOn,
            'period_ended_on' => $this->periodEndedOn,
            'description' => $this->description,
            'notes' => $this->notes,
            'status' => $this->status,
            'submitted_at' => $this->submittedAt,
            'submitted_by' => $this->submittedBy,
            'verified_at' => $this->verifiedAt,
            'verified_by' => $this->verifiedBy,
            'verification_note' => $this->verificationNote,
            'voided_at' => $this->voidedAt,
            'voided_by' => $this->voidedBy,
            'void_reason' => $this->voidReason,
            'created_by' => $this->createdBy,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'summary' => $this->summary->toArray(),
        ];

        if ($includeRevisions) {
            $data['revisions'] = array_map(
                static fn (StudentAchievementRevisionData $revision): array => $revision->toArray(),
                $this->revisions,
            );
        }

        return $data;
    }
}
