<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Presentation\Resources;

use App\Modules\Pesantrian\Tahfidz\Application\DTO\TahfidzProgramData;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\TahfidzSubmissionData;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\TahfidzSubmissionRevisionData;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\TahfidzSubmissionSummaryData;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\TahfidzTargetData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TahfidzSubmissionData */
final class TahfidzSubmissionResource extends JsonResource
{
    public function __construct(TahfidzSubmissionData $resource, private readonly bool $includeRevisions = true)
    {
        parent::__construct($resource);
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var TahfidzSubmissionData $submission */
        $submission = $this->resource;

        $data = [
            'id' => $submission->id,
            'program' => $this->program($submission->program),
            'target' => $submission->target instanceof TahfidzTargetData ? $this->target($submission->target) : null,
            'student_id' => $submission->studentId,
            'student_no' => $submission->studentNo,
            'student_name' => $submission->studentName,
            'supervisor_id' => $submission->supervisorId,
            'supervisor_name' => $submission->supervisorName,
            'submission_date' => $submission->submissionDate,
            'type' => $submission->type,
            'juz' => $submission->juz,
            'surah' => $submission->surah,
            'ayah_from' => $submission->ayahFrom,
            'ayah_to' => $submission->ayahTo,
            'status' => $submission->status,
            'quality_note' => $submission->qualityNote,
            'created_by' => $submission->createdBy,
            'reviewed_at' => $submission->reviewedAt,
            'reviewed_by' => $submission->reviewedBy,
            'voided_at' => $submission->voidedAt,
            'voided_by' => $submission->voidedBy,
            'void_reason' => $submission->voidReason,
            'created_at' => $submission->createdAt,
            'updated_at' => $submission->updatedAt,
            'summary' => $this->summary($submission->summary),
        ];

        if ($this->includeRevisions) {
            $data['revisions'] = array_map(
                fn (TahfidzSubmissionRevisionData $revision): array => $this->revision($revision),
                $submission->revisions,
            );
        }

        return $data;
    }

    /** @return array{id: string, code: string, name: string, description: string|null, status: string} */
    private function program(TahfidzProgramData $program): array
    {
        return [
            'id' => $program->id,
            'code' => $program->code,
            'name' => $program->name,
            'description' => $program->description,
            'status' => $program->status,
        ];
    }

    /** @return array<string, mixed> */
    private function target(TahfidzTargetData $target): array
    {
        return [
            'id' => $target->id,
            'student_id' => $target->studentId,
            'student_no' => $target->studentNo,
            'student_name' => $target->studentName,
            'academic_period_id' => $target->academicPeriodId,
            'period_label' => $target->periodLabel,
            'target_juz' => $target->targetJuz,
            'target_surah' => $target->targetSurah,
            'target_ayah_from' => $target->targetAyahFrom,
            'target_ayah_to' => $target->targetAyahTo,
            'target_note' => $target->targetNote,
            'status' => $target->status,
        ];
    }

    /** @return array{has_target: bool, has_supervisor: bool, has_revision: bool, revision_count: int} */
    private function summary(TahfidzSubmissionSummaryData $summary): array
    {
        return [
            'has_target' => $summary->hasTarget,
            'has_supervisor' => $summary->hasSupervisor,
            'has_revision' => $summary->hasRevision,
            'revision_count' => $summary->revisionCount,
        ];
    }

    /** @return array<string, mixed> */
    private function revision(TahfidzSubmissionRevisionData $revision): array
    {
        return [
            'id' => $revision->id,
            'reason' => $revision->reason,
            'changed_by' => $revision->changedBy,
            'changed_at' => $revision->changedAt,
            'summary' => $revision->summary,
            'created_at' => $revision->createdAt,
            'updated_at' => $revision->updatedAt,
        ];
    }
}
