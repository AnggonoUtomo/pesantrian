<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Application\DTO;

final readonly class StudentAchievementMutationData
{
    public function __construct(
        public ?string $categoryId,
        public ?string $categoryName,
        public ?string $studentId,
        public ?string $studentNo,
        public ?string $studentName,
        public ?string $academicPeriodId,
        public ?string $academicPeriodLabel,
        public ?string $mentorEmployeeId,
        public ?string $mentorName,
        public ?string $title,
        public ?string $achievementType,
        public ?string $level,
        public ?string $result,
        public ?string $organizer,
        public ?string $eventName,
        public ?string $eventLocation,
        public ?string $achievedOn,
        public ?string $periodStartedOn,
        public ?string $periodEndedOn,
        public ?string $description,
        public ?string $notes,
    ) {}

    /** @return array<string, string|null> */
    public function toDatabasePayload(bool $includeNull = false): array
    {
        $payload = [
            'category_id' => $this->categoryId,
            'category_name' => $this->categoryName,
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
        ];

        if ($includeNull) {
            return $payload;
        }

        return array_filter($payload, static fn (mixed $value): bool => $value !== null);
    }
}
