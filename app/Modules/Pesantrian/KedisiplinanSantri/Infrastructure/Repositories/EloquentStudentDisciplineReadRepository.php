<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Infrastructure\Repositories;

use App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts\StudentDisciplineCaseMutationRepository;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts\StudentDisciplineCategoryMutationRepository;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Contracts\StudentDisciplineReadRepository;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\PaginatedStudentDisciplineCaseData;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCaseData;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCaseListFilter;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCaseMutationData;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCategoryData;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCategoryListFilter;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineRevisionData;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineSummaryData;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\UpsertStudentDisciplineCategoryData;
use App\Modules\Pesantrian\KedisiplinanSantri\Infrastructure\Models\StudentDisciplineCaseRecord;
use App\Modules\Pesantrian\KedisiplinanSantri\Infrastructure\Models\StudentDisciplineCategoryRecord;
use App\Modules\Pesantrian\KedisiplinanSantri\Infrastructure\Models\StudentDisciplineRevisionRecord;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class EloquentStudentDisciplineReadRepository implements StudentDisciplineCaseMutationRepository, StudentDisciplineCategoryMutationRepository, StudentDisciplineReadRepository
{
    /** @var list<string> */
    private const FINAL_STATUSES = ['resolved', 'void'];

    /** @var list<string> */
    private const NEEDS_ACTION_STATUSES = ['submitted', 'in_review', 'action_assigned'];

    public function createCaseDraft(StudentDisciplineCaseMutationData $data, ?string $actorId): StudentDisciplineCaseData
    {
        $record = StudentDisciplineCaseRecord::query()->create([
            ...$data->toDatabasePayload(includeNull: true),
            'case_no' => $this->nextCaseNo(),
            'unit_name' => $this->unitName($data->unitId),
            'reported_by' => $actorId,
            'status' => 'draft',
            'submitted_at' => null,
            'reviewed_at' => null,
            'reviewed_by' => null,
            'review_note' => null,
            'action_plan' => null,
            'action_assigned_at' => null,
            'resolved_at' => null,
            'resolved_by' => null,
            'resolution_note' => null,
            'voided_at' => null,
            'voided_by' => null,
            'void_reason' => null,
            'created_by' => $actorId,
        ]);
        $this->createRevision($record, 'Draft kasus kedisiplinan dibuat.', $actorId, [
            'action' => 'create',
            'changed_fields' => ['student_id', 'category_id', 'severity', 'points', 'occurred_at', 'location', 'description', 'assigned_employee_id', 'status'],
            'to_status' => 'draft',
        ]);

        return $this->mapCase($record->refresh()->load(['category', 'revisions'])->loadCount('revisions'));
    }

    public function updateCaseDraft(string $id, StudentDisciplineCaseMutationData $data, string $reason, ?string $actorId): ?StudentDisciplineCaseData
    {
        $record = StudentDisciplineCaseRecord::query()->find($id);

        if (! $record instanceof StudentDisciplineCaseRecord) {
            return null;
        }

        $payload = $data->toDatabasePayload();

        if (array_key_exists('unit_id', $payload)) {
            $payload['unit_name'] = $this->unitName($payload['unit_id'] === null ? null : (string) $payload['unit_id']);
        }

        $record->forceFill($payload)->save();
        $this->createRevision($record, $reason, $actorId, [
            'action' => 'update',
            'changed_fields' => array_keys($payload),
            'status' => $record->status,
        ]);

        return $this->mapCase($record->refresh()->load(['category', 'revisions'])->loadCount('revisions'));
    }

    public function submitCaseDraft(string $id, string $actorId): ?StudentDisciplineCaseData
    {
        $record = StudentDisciplineCaseRecord::query()->find($id);

        if (! $record instanceof StudentDisciplineCaseRecord) {
            return null;
        }

        $record->forceFill([
            'status' => 'submitted',
            'submitted_at' => now(),
        ])->save();
        $this->createRevision($record, 'Draft kasus kedisiplinan disubmit untuk review.', $actorId, [
            'action' => 'submit',
            'changed_fields' => ['status', 'submitted_at'],
            'from_status' => 'draft',
            'to_status' => 'submitted',
        ]);

        return $this->mapCase($record->refresh()->load(['category', 'revisions'])->loadCount('revisions'));
    }

    public function reviewCase(string $id, string $reviewNote, string $actorId): ?StudentDisciplineCaseData
    {
        $record = StudentDisciplineCaseRecord::query()->find($id);

        if (! $record instanceof StudentDisciplineCaseRecord) {
            return null;
        }

        $record->forceFill([
            'status' => 'in_review',
            'reviewed_at' => now(),
            'reviewed_by' => $actorId,
            'review_note' => $reviewNote,
        ])->save();
        $this->createRevision($record, $reviewNote, $actorId, [
            'action' => 'review',
            'changed_fields' => ['status', 'reviewed_at', 'reviewed_by', 'review_note'],
            'from_status' => 'submitted',
            'to_status' => 'in_review',
        ]);

        return $this->mapCase($record->refresh()->load(['category', 'revisions'])->loadCount('revisions'));
    }

    public function assignCaseAction(string $id, string $actionPlan, ?string $assignedEmployeeId, ?string $assignedEmployeeName, string $actorId): ?StudentDisciplineCaseData
    {
        $record = StudentDisciplineCaseRecord::query()->find($id);

        if (! $record instanceof StudentDisciplineCaseRecord) {
            return null;
        }

        $fromStatus = (string) $record->status;
        $record->forceFill([
            'status' => 'action_assigned',
            'action_plan' => $actionPlan,
            'action_assigned_at' => now(),
            'assigned_employee_id' => $assignedEmployeeId,
            'assigned_employee_name' => $assignedEmployeeName,
        ])->save();
        $this->createRevision($record, $actionPlan, $actorId, [
            'action' => 'assign_action',
            'changed_fields' => ['status', 'action_plan', 'action_assigned_at', 'assigned_employee_id'],
            'from_status' => $fromStatus,
            'to_status' => 'action_assigned',
        ]);

        return $this->mapCase($record->refresh()->load(['category', 'revisions'])->loadCount('revisions'));
    }

    public function findActiveCategory(string $id): ?StudentDisciplineCategoryData
    {
        $record = StudentDisciplineCategoryRecord::query()
            ->whereKey($id)
            ->where('status', 'active')
            ->first();

        if (! $record instanceof StudentDisciplineCategoryRecord) {
            return null;
        }

        return $this->mapCategory($record);
    }

    public function createCategory(UpsertStudentDisciplineCategoryData $data, ?string $actorId): StudentDisciplineCategoryData
    {
        $record = StudentDisciplineCategoryRecord::query()->create([
            ...$data->toArray(),
            'status' => 'active',
        ]);

        return $this->mapCategory($record);
    }

    public function updateCategory(string $id, array $changes): ?StudentDisciplineCategoryData
    {
        $record = StudentDisciplineCategoryRecord::query()->find($id);

        if (! $record instanceof StudentDisciplineCategoryRecord) {
            return null;
        }

        $record->forceFill($changes)->save();

        return $this->mapCategory($record->refresh());
    }

    public function archiveCategory(string $id): ?StudentDisciplineCategoryData
    {
        $record = StudentDisciplineCategoryRecord::query()->find($id);

        if (! $record instanceof StudentDisciplineCategoryRecord) {
            return null;
        }

        $record->forceFill(['status' => 'archived'])->save();

        return $this->mapCategory($record->refresh());
    }

    /** @return list<StudentDisciplineCategoryData> */
    public function categories(StudentDisciplineCategoryListFilter $filter): array
    {
        return StudentDisciplineCategoryRecord::query()
            ->when($filter->search !== null, function (Builder $query) use ($filter): void {
                $query->where(function (Builder $query) use ($filter): void {
                    $query->where('code', 'like', '%'.$filter->search.'%')
                        ->orWhere('name', 'like', '%'.$filter->search.'%')
                        ->orWhere('description', 'like', '%'.$filter->search.'%');
                });
            })
            ->when($filter->status !== null, fn (Builder $query) => $query->where('status', $filter->status))
            ->orderBy('status')
            ->orderBy('name')
            ->get()
            ->map(fn (StudentDisciplineCategoryRecord $record): StudentDisciplineCategoryData => $this->mapCategory($record))
            ->values()
            ->all();
    }

    public function paginateCases(StudentDisciplineCaseListFilter $filter): PaginatedStudentDisciplineCaseData
    {
        $query = $this->filteredCaseQuery($filter)
            ->with('category')
            ->withCount('revisions')
            ->orderBy($this->qualifiedSortField($filter->sortField), $filter->sortDirection === 'asc' ? 'asc' : 'desc')
            ->orderBy('student_discipline_cases.created_at', 'desc');

        /** @var LengthAwarePaginator<int, StudentDisciplineCaseRecord> $page */
        $page = $query->paginate($filter->perPage, ['student_discipline_cases.*'], 'page', $filter->page);

        return new PaginatedStudentDisciplineCaseData(
            data: array_map(
                fn (StudentDisciplineCaseRecord $record): StudentDisciplineCaseData => $this->mapCase($record, includeRevisions: false),
                array_values($page->items()),
            ),
            currentPage: $page->currentPage(),
            perPage: $page->perPage(),
            total: $page->total(),
            lastPage: $page->lastPage(),
        );
    }

    public function findCase(string $id): ?StudentDisciplineCaseData
    {
        $record = StudentDisciplineCaseRecord::query()
            ->with('category')
            ->with(['revisions' => fn ($query) => $query->orderBy('changed_at')])
            ->withCount('revisions')
            ->find($id);

        if (! $record instanceof StudentDisciplineCaseRecord) {
            return null;
        }

        return $this->mapCase($record);
    }

    /** @return Builder<StudentDisciplineCaseRecord> */
    private function filteredCaseQuery(StudentDisciplineCaseListFilter $filter): Builder
    {
        return StudentDisciplineCaseRecord::query()
            ->when($filter->search !== null, function (Builder $query) use ($filter): void {
                $query->where(function (Builder $query) use ($filter): void {
                    $query->where('case_no', 'like', '%'.$filter->search.'%')
                        ->orWhere('student_no', 'like', '%'.$filter->search.'%')
                        ->orWhere('student_name', 'like', '%'.$filter->search.'%')
                        ->orWhere('unit_name', 'like', '%'.$filter->search.'%')
                        ->orWhere('category_name', 'like', '%'.$filter->search.'%')
                        ->orWhere('assigned_employee_name', 'like', '%'.$filter->search.'%')
                        ->orWhere('location', 'like', '%'.$filter->search.'%')
                        ->orWhere('description', 'like', '%'.$filter->search.'%');
                });
            })
            ->when($filter->dateFrom !== null, fn (Builder $query) => $query->where('occurred_at', '>=', $filter->dateFrom.' 00:00:00'))
            ->when($filter->dateTo !== null, fn (Builder $query) => $query->where('occurred_at', '<=', $filter->dateTo.' 23:59:59'))
            ->when($filter->status !== null, fn (Builder $query) => $query->where('status', $filter->status))
            ->when($filter->severity !== null, fn (Builder $query) => $query->where('severity', $filter->severity))
            ->when($filter->categoryId !== null, fn (Builder $query) => $query->where('category_id', $filter->categoryId))
            ->when($filter->studentId !== null, fn (Builder $query) => $query->where('student_id', $filter->studentId))
            ->when($filter->assignedEmployeeId !== null, fn (Builder $query) => $query->where('assigned_employee_id', $filter->assignedEmployeeId));
    }

    private function mapCategory(StudentDisciplineCategoryRecord $record): StudentDisciplineCategoryData
    {
        return new StudentDisciplineCategoryData(
            id: (string) $record->getKey(),
            code: (string) $record->code,
            name: (string) $record->name,
            description: $record->description === null ? null : (string) $record->description,
            defaultSeverity: (string) $record->default_severity,
            defaultPoints: $record->default_points === null ? null : (int) $record->default_points,
            status: (string) $record->status,
            createdAt: $record->created_at?->toJSON(),
            updatedAt: $record->updated_at?->toJSON(),
        );
    }

    private function mapCase(StudentDisciplineCaseRecord $record, bool $includeRevisions = true): StudentDisciplineCaseData
    {
        $revisions = [];

        if ($includeRevisions) {
            /** @var iterable<StudentDisciplineRevisionRecord> $revisionRecords */
            $revisionRecords = $record->revisions;

            foreach ($revisionRecords as $revisionRecord) {
                $revisions[] = $this->mapRevision($revisionRecord);
            }
        }

        /** @var StudentDisciplineCategoryRecord $category */
        $category = $record->category;

        return new StudentDisciplineCaseData(
            id: (string) $record->getKey(),
            caseNo: (string) $record->case_no,
            studentId: (string) $record->student_id,
            studentNo: (string) $record->student_no,
            studentName: (string) $record->student_name,
            unitId: $record->unit_id === null ? null : (string) $record->unit_id,
            unitName: $record->unit_name === null ? null : (string) $record->unit_name,
            category: $this->mapCategory($category),
            severity: (string) $record->severity,
            points: $record->points === null ? null : (int) $record->points,
            occurredAt: $record->occurred_at->toJSON(),
            location: $record->location === null ? null : (string) $record->location,
            description: (string) $record->description,
            reportedBy: $record->reported_by === null ? null : (string) $record->reported_by,
            assignedEmployeeId: $record->assigned_employee_id === null ? null : (string) $record->assigned_employee_id,
            assignedEmployeeName: $record->assigned_employee_name === null ? null : (string) $record->assigned_employee_name,
            status: (string) $record->status,
            submittedAt: $record->submitted_at?->toJSON(),
            reviewedAt: $record->reviewed_at?->toJSON(),
            reviewedBy: $record->reviewed_by === null ? null : (string) $record->reviewed_by,
            reviewNote: $record->review_note === null ? null : (string) $record->review_note,
            actionPlan: $record->action_plan === null ? null : (string) $record->action_plan,
            actionAssignedAt: $record->action_assigned_at?->toJSON(),
            resolvedAt: $record->resolved_at?->toJSON(),
            resolvedBy: $record->resolved_by === null ? null : (string) $record->resolved_by,
            resolutionNote: $record->resolution_note === null ? null : (string) $record->resolution_note,
            voidedAt: $record->voided_at?->toJSON(),
            voidedBy: $record->voided_by === null ? null : (string) $record->voided_by,
            voidReason: $record->void_reason === null ? null : (string) $record->void_reason,
            createdBy: $record->created_by === null ? null : (string) $record->created_by,
            createdAt: $record->created_at?->toJSON(),
            updatedAt: $record->updated_at?->toJSON(),
            summary: new StudentDisciplineSummaryData(
                isFinal: in_array($record->status, self::FINAL_STATUSES, true),
                needsAction: in_array($record->status, self::NEEDS_ACTION_STATUSES, true),
                revisionCount: (int) ($record->revisions_count ?? count($revisions)),
            ),
            revisions: $revisions,
        );
    }

    private function mapRevision(StudentDisciplineRevisionRecord $record): StudentDisciplineRevisionData
    {
        return new StudentDisciplineRevisionData(
            id: (string) $record->getKey(),
            caseId: (string) $record->case_id,
            reason: (string) $record->reason,
            changedBy: $record->changed_by === null ? null : (string) $record->changed_by,
            changedAt: $record->changed_at->toJSON(),
            summary: $record->summary,
            createdAt: $record->created_at?->toJSON(),
            updatedAt: $record->updated_at?->toJSON(),
        );
    }

    private function qualifiedSortField(string $field): string
    {
        return match ($field) {
            'case_no' => 'student_discipline_cases.case_no',
            'student_name' => 'student_discipline_cases.student_name',
            'status' => 'student_discipline_cases.status',
            'severity' => 'student_discipline_cases.severity',
            'occurred_at' => 'student_discipline_cases.occurred_at',
            'created_at' => 'student_discipline_cases.created_at',
            default => 'student_discipline_cases.occurred_at',
        };
    }

    private function nextCaseNo(): string
    {
        $latestCaseNo = StudentDisciplineCaseRecord::query()
            ->where('case_no', 'like', 'DIS-%')
            ->orderByDesc('case_no')
            ->value('case_no');

        $nextNumber = 1;

        if (is_string($latestCaseNo) && preg_match('/^DIS-(\d+)$/', $latestCaseNo, $matches) === 1) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        do {
            $caseNo = 'DIS-'.str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
            $nextNumber++;
        } while (StudentDisciplineCaseRecord::query()->where('case_no', $caseNo)->exists());

        return $caseNo;
    }

    private function unitName(?string $unitId): ?string
    {
        if ($unitId === null) {
            return null;
        }

        $name = DB::table('organization_units')
            ->where('id', $unitId)
            ->value('name');

        return is_string($name) ? $name : null;
    }

    /** @param array<string, mixed> $summary */
    private function createRevision(StudentDisciplineCaseRecord $record, string $reason, ?string $actorId, array $summary): void
    {
        StudentDisciplineRevisionRecord::query()->create([
            'case_id' => $record->getKey(),
            'reason' => $reason,
            'changed_by' => $actorId,
            'changed_at' => now(),
            'summary' => $summary,
        ]);
    }
}
