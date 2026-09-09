<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO;

final readonly class StudentDisciplineCaseCandidateData
{
    /** @param array<string, mixed> $metadata */
    public function __construct(
        public string $sourceType,
        public string $sourceId,
        public string $studentId,
        public string $studentNo,
        public string $studentName,
        public string $occurredAt,
        public string $title,
        public string $description,
        public string $suggestedSeverity,
        public array $metadata,
        public bool $requiresHumanReview = true,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'source_type' => $this->sourceType,
            'source_id' => $this->sourceId,
            'student_id' => $this->studentId,
            'student_no' => $this->studentNo,
            'student_name' => $this->studentName,
            'occurred_at' => $this->occurredAt,
            'title' => $this->title,
            'description' => $this->description,
            'suggested_severity' => $this->suggestedSeverity,
            'metadata' => $this->metadata,
            'requires_human_review' => $this->requiresHumanReview,
        ];
    }
}
