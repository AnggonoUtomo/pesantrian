<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PerizinanSantri\Infrastructure\Repositories;

use App\Modules\Pesantrian\PerizinanSantri\Application\Contracts\StudentPermitReadRepository;
use App\Modules\Pesantrian\PerizinanSantri\Application\DTO\PaginatedStudentPermitData;
use App\Modules\Pesantrian\PerizinanSantri\Application\DTO\StudentPermitData;
use App\Modules\Pesantrian\PerizinanSantri\Application\DTO\StudentPermitListFilter;
use App\Modules\Pesantrian\PerizinanSantri\Application\DTO\StudentPermitRevisionData;
use App\Modules\Pesantrian\PerizinanSantri\Application\DTO\StudentPermitSummaryData;
use App\Modules\Pesantrian\PerizinanSantri\Infrastructure\Models\StudentPermitRecord;
use App\Modules\Pesantrian\PerizinanSantri\Infrastructure\Models\StudentPermitRevisionRecord;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class EloquentStudentPermitReadRepository implements StudentPermitReadRepository
{
    /** @var list<string> */
    private const ACTIVE_STATUSES = ['submitted', 'approved', 'checked_out'];

    /** @var list<string> */
    private const FINAL_STATUSES = ['rejected', 'returned', 'void'];

    public function paginate(StudentPermitListFilter $filter): PaginatedStudentPermitData
    {
        $query = $this->filteredQuery($filter)
            ->withCount('revisions')
            ->orderBy($this->qualifiedSortField($filter->sortField), $filter->sortDirection === 'asc' ? 'asc' : 'desc')
            ->orderBy('student_permits.created_at', 'desc');

        /** @var LengthAwarePaginator<int, StudentPermitRecord> $page */
        $page = $query->paginate($filter->perPage, ['student_permits.*'], 'page', $filter->page);

        return new PaginatedStudentPermitData(
            data: array_map(
                fn (StudentPermitRecord $record): StudentPermitData => $this->map($record, includeRevisions: false),
                array_values($page->items()),
            ),
            currentPage: $page->currentPage(),
            perPage: $page->perPage(),
            total: $page->total(),
            lastPage: $page->lastPage(),
        );
    }

    public function find(string $id): ?StudentPermitData
    {
        $record = StudentPermitRecord::query()
            ->with(['revisions' => fn ($query) => $query->orderBy('changed_at')])
            ->withCount('revisions')
            ->find($id);

        if (! $record instanceof StudentPermitRecord) {
            return null;
        }

        return $this->map($record);
    }

    /** @return Builder<StudentPermitRecord> */
    private function filteredQuery(StudentPermitListFilter $filter): Builder
    {
        return StudentPermitRecord::query()
            ->when($filter->search !== null, function (Builder $query) use ($filter): void {
                $query->where(function (Builder $query) use ($filter): void {
                    $query->where('permit_no', 'like', '%'.$filter->search.'%')
                        ->orWhere('student_no', 'like', '%'.$filter->search.'%')
                        ->orWhere('student_name', 'like', '%'.$filter->search.'%')
                        ->orWhere('destination', 'like', '%'.$filter->search.'%')
                        ->orWhere('reason', 'like', '%'.$filter->search.'%');
                });
            })
            ->when($filter->dateFrom !== null, fn (Builder $query) => $query->where('ends_at', '>=', $filter->dateFrom.' 00:00:00'))
            ->when($filter->dateTo !== null, fn (Builder $query) => $query->where('starts_at', '<=', $filter->dateTo.' 23:59:59'))
            ->when($filter->permitType !== null, fn (Builder $query) => $query->where('permit_type', $filter->permitType))
            ->when($filter->status !== null, fn (Builder $query) => $query->where('status', $filter->status))
            ->when($filter->studentId !== null, fn (Builder $query) => $query->where('student_id', $filter->studentId))
            ->when($filter->isLate !== null, function (Builder $query) use ($filter): void {
                $lateConstraint = function (Builder $query): void {
                    $query->whereColumn('returned_at', '>', 'ends_at')
                        ->orWhere(function (Builder $query): void {
                            $query->where('status', 'checked_out')
                                ->whereNull('returned_at')
                                ->where('ends_at', '<', now());
                        });
                };

                if ($filter->isLate) {
                    $query->where($lateConstraint);

                    return;
                }

                $query->whereNot($lateConstraint);
            });
    }

    private function map(StudentPermitRecord $record, bool $includeRevisions = true): StudentPermitData
    {
        $revisions = [];

        if ($includeRevisions) {
            /** @var iterable<StudentPermitRevisionRecord> $revisionRecords */
            $revisionRecords = $record->revisions;

            foreach ($revisionRecords as $revisionRecord) {
                $revisions[] = $this->mapRevision($revisionRecord);
            }
        }

        return new StudentPermitData(
            id: (string) $record->getKey(),
            permitNo: (string) $record->permit_no,
            studentId: (string) $record->student_id,
            studentNo: (string) $record->student_no,
            studentName: (string) $record->student_name,
            permitType: (string) $record->permit_type,
            startsAt: $record->starts_at->toJSON(),
            endsAt: $record->ends_at->toJSON(),
            destination: $record->destination === null ? null : (string) $record->destination,
            reason: (string) $record->reason,
            guardianName: $record->guardian_name === null ? null : (string) $record->guardian_name,
            guardianPhone: $record->guardian_phone === null ? null : (string) $record->guardian_phone,
            guardianRelation: $record->guardian_relation === null ? null : (string) $record->guardian_relation,
            status: (string) $record->status,
            submittedAt: $record->submitted_at?->toJSON(),
            submittedBy: $record->submitted_by === null ? null : (string) $record->submitted_by,
            reviewedAt: $record->reviewed_at?->toJSON(),
            reviewedBy: $record->reviewed_by === null ? null : (string) $record->reviewed_by,
            reviewNote: $record->review_note === null ? null : (string) $record->review_note,
            checkedOutAt: $record->checked_out_at?->toJSON(),
            checkedOutBy: $record->checked_out_by === null ? null : (string) $record->checked_out_by,
            returnedAt: $record->returned_at?->toJSON(),
            returnedBy: $record->returned_by === null ? null : (string) $record->returned_by,
            returnNote: $record->return_note === null ? null : (string) $record->return_note,
            voidedAt: $record->voided_at?->toJSON(),
            voidedBy: $record->voided_by === null ? null : (string) $record->voided_by,
            voidReason: $record->void_reason === null ? null : (string) $record->void_reason,
            createdBy: $record->created_by === null ? null : (string) $record->created_by,
            createdAt: $record->created_at?->toJSON(),
            updatedAt: $record->updated_at?->toJSON(),
            summary: new StudentPermitSummaryData(
                isLate: $this->isLate($record),
                isFinal: in_array($record->status, self::FINAL_STATUSES, true),
                isActive: in_array($record->status, self::ACTIVE_STATUSES, true),
                revisionCount: (int) ($record->revisions_count ?? count($revisions)),
            ),
            revisions: $revisions,
        );
    }

    private function mapRevision(StudentPermitRevisionRecord $record): StudentPermitRevisionData
    {
        return new StudentPermitRevisionData(
            id: (string) $record->getKey(),
            permitId: (string) $record->permit_id,
            reason: (string) $record->reason,
            changedBy: $record->changed_by === null ? null : (string) $record->changed_by,
            changedAt: $record->changed_at->toJSON(),
            summary: $record->summary,
            createdAt: $record->created_at?->toJSON(),
            updatedAt: $record->updated_at?->toJSON(),
        );
    }

    private function isLate(StudentPermitRecord $record): bool
    {
        if ($record->returned_at !== null) {
            return $record->returned_at->isAfter($record->ends_at);
        }

        return $record->status === 'checked_out' && $record->ends_at->isPast();
    }

    private function qualifiedSortField(string $field): string
    {
        return match ($field) {
            'permit_no' => 'student_permits.permit_no',
            'student_name' => 'student_permits.student_name',
            'permit_type' => 'student_permits.permit_type',
            'status' => 'student_permits.status',
            'starts_at' => 'student_permits.starts_at',
            'ends_at' => 'student_permits.ends_at',
            default => 'student_permits.created_at',
        };
    }
}
