<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO;

final readonly class StudentDisciplineCaseData
{
    /** @param list<StudentDisciplineRevisionData> $revisions */
    public function __construct(
        public string $id,
        public string $caseNo,
        public string $studentId,
        public string $studentNo,
        public string $studentName,
        public ?string $unitId,
        public ?string $unitName,
        public StudentDisciplineCategoryData $category,
        public string $severity,
        public ?int $points,
        public string $occurredAt,
        public ?string $location,
        public string $description,
        public ?string $reportedBy,
        public ?string $assignedEmployeeId,
        public ?string $assignedEmployeeName,
        public string $status,
        public ?string $submittedAt,
        public ?string $reviewedAt,
        public ?string $reviewedBy,
        public ?string $reviewNote,
        public ?string $actionPlan,
        public ?string $actionAssignedAt,
        public ?string $resolvedAt,
        public ?string $resolvedBy,
        public ?string $resolutionNote,
        public ?string $voidedAt,
        public ?string $voidedBy,
        public ?string $voidReason,
        public ?string $createdBy,
        public ?string $createdAt,
        public ?string $updatedAt,
        public StudentDisciplineSummaryData $summary,
        public array $revisions = [],
    ) {}

    /** @return array<string, mixed> */
    public function toArray(bool $includeRevisions = true): array
    {
        $data = [
            'id' => $this->id,
            'case_no' => $this->caseNo,
            'student_id' => $this->studentId,
            'student_no' => $this->studentNo,
            'student_name' => $this->studentName,
            'unit_id' => $this->unitId,
            'unit_name' => $this->unitName,
            'category' => $this->category->toArray(),
            'severity' => $this->severity,
            'points' => $this->points,
            'occurred_at' => $this->occurredAt,
            'location' => $this->location,
            'description' => $this->description,
            'reported_by' => $this->reportedBy,
            'assigned_employee_id' => $this->assignedEmployeeId,
            'assigned_employee_name' => $this->assignedEmployeeName,
            'status' => $this->status,
            'submitted_at' => $this->submittedAt,
            'reviewed_at' => $this->reviewedAt,
            'reviewed_by' => $this->reviewedBy,
            'review_note' => $this->reviewNote,
            'action_plan' => $this->actionPlan,
            'action_assigned_at' => $this->actionAssignedAt,
            'resolved_at' => $this->resolvedAt,
            'resolved_by' => $this->resolvedBy,
            'resolution_note' => $this->resolutionNote,
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
                static fn (StudentDisciplineRevisionData $revision): array => $revision->toArray(),
                $this->revisions,
            );
        }

        return $data;
    }
}
