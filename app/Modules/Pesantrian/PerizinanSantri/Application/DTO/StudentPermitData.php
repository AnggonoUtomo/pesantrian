<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PerizinanSantri\Application\DTO;

final readonly class StudentPermitData
{
    /** @param list<StudentPermitRevisionData> $revisions */
    public function __construct(
        public string $id,
        public string $permitNo,
        public string $studentId,
        public string $studentNo,
        public string $studentName,
        public string $permitType,
        public string $startsAt,
        public string $endsAt,
        public ?string $destination,
        public string $reason,
        public ?string $guardianName,
        public ?string $guardianPhone,
        public ?string $guardianRelation,
        public string $status,
        public ?string $submittedAt,
        public ?string $submittedBy,
        public ?string $reviewedAt,
        public ?string $reviewedBy,
        public ?string $reviewNote,
        public ?string $checkedOutAt,
        public ?string $checkedOutBy,
        public ?string $returnedAt,
        public ?string $returnedBy,
        public ?string $returnNote,
        public ?string $voidedAt,
        public ?string $voidedBy,
        public ?string $voidReason,
        public ?string $createdBy,
        public ?string $createdAt,
        public ?string $updatedAt,
        public StudentPermitSummaryData $summary,
        public array $revisions = [],
    ) {}

    /** @return array<string, mixed> */
    public function toArray(bool $includeRevisions = true): array
    {
        $data = [
            'id' => $this->id,
            'permit_no' => $this->permitNo,
            'student_id' => $this->studentId,
            'student_no' => $this->studentNo,
            'student_name' => $this->studentName,
            'permit_type' => $this->permitType,
            'starts_at' => $this->startsAt,
            'ends_at' => $this->endsAt,
            'destination' => $this->destination,
            'reason' => $this->reason,
            'guardian_name' => $this->guardianName,
            'guardian_phone' => $this->guardianPhone,
            'guardian_relation' => $this->guardianRelation,
            'status' => $this->status,
            'submitted_at' => $this->submittedAt,
            'submitted_by' => $this->submittedBy,
            'reviewed_at' => $this->reviewedAt,
            'reviewed_by' => $this->reviewedBy,
            'review_note' => $this->reviewNote,
            'checked_out_at' => $this->checkedOutAt,
            'checked_out_by' => $this->checkedOutBy,
            'returned_at' => $this->returnedAt,
            'returned_by' => $this->returnedBy,
            'return_note' => $this->returnNote,
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
                static fn (StudentPermitRevisionData $revision): array => $revision->toArray(),
                $this->revisions,
            );
        }

        return $data;
    }
}
