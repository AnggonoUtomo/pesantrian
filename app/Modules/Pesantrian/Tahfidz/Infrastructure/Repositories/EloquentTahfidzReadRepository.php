<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Infrastructure\Repositories;

use App\Modules\Pesantrian\Tahfidz\Application\Contracts\TahfidzMutationRepository;
use App\Modules\Pesantrian\Tahfidz\Application\Contracts\TahfidzReadRepository;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\PaginatedTahfidzSubmissionData;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\TahfidzListFilter;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\TahfidzProgramData;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\TahfidzSubmissionData;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\TahfidzSubmissionRevisionData;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\TahfidzSubmissionSummaryData;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\TahfidzTargetData;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\UpsertTahfidzProgramData;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\UpsertTahfidzSubmissionData;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\UpsertTahfidzTargetData;
use App\Modules\Pesantrian\Tahfidz\Infrastructure\Models\TahfidzProgramRecord;
use App\Modules\Pesantrian\Tahfidz\Infrastructure\Models\TahfidzSubmissionRecord;
use App\Modules\Pesantrian\Tahfidz\Infrastructure\Models\TahfidzSubmissionRevisionRecord;
use App\Modules\Pesantrian\Tahfidz\Infrastructure\Models\TahfidzTargetRecord;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class EloquentTahfidzReadRepository implements TahfidzMutationRepository, TahfidzReadRepository
{
    public function createProgram(UpsertTahfidzProgramData $data, ?string $actorId): TahfidzProgramData
    {
        $record = TahfidzProgramRecord::query()->create([
            ...$data->toArray(),
            'created_by' => $actorId,
        ]);

        return $this->mapProgram($record);
    }

    public function updateProgram(string $id, array $changes): ?TahfidzProgramData
    {
        $record = TahfidzProgramRecord::query()
            ->whereKey($id)
            ->whereNull('archived_at')
            ->first();

        if (! $record instanceof TahfidzProgramRecord) {
            return null;
        }

        $record->forceFill($changes)->save();

        return $this->mapProgram($record->refresh());
    }

    public function createTarget(UpsertTahfidzTargetData $data, ?string $actorId): TahfidzTargetData
    {
        $payload = $data->toArray();
        $payload['period_label'] = $this->periodLabel($data->academicPeriodId);
        $payload['created_by'] = $actorId;

        $record = TahfidzTargetRecord::query()->create($payload);

        return $this->mapTarget($record);
    }

    public function updateTarget(string $id, array $changes): ?TahfidzTargetData
    {
        $record = TahfidzTargetRecord::query()->find($id);

        if (! $record instanceof TahfidzTargetRecord) {
            return null;
        }

        if (array_key_exists('academic_period_id', $changes)) {
            $changes['period_label'] = $this->periodLabel($changes['academic_period_id'] === null ? null : (string) $changes['academic_period_id']);
        }

        $record->forceFill($changes)->save();

        return $this->mapTarget($record->refresh());
    }

    public function createSubmission(UpsertTahfidzSubmissionData $data, ?string $actorId): TahfidzSubmissionData
    {
        $record = TahfidzSubmissionRecord::query()->create([
            ...$data->toArray(),
            'created_by' => $actorId,
        ]);

        return $this->map($record->load(['program', 'target', 'revisions']));
    }

    public function updateSubmission(string $id, array $changes): ?TahfidzSubmissionData
    {
        $record = TahfidzSubmissionRecord::query()->find($id);

        if (! $record instanceof TahfidzSubmissionRecord) {
            return null;
        }

        $record->forceFill($changes)->save();

        return $this->map($record->refresh()->load(['program', 'target', 'revisions']));
    }

    public function paginate(TahfidzListFilter $filter): PaginatedTahfidzSubmissionData
    {
        $query = $this->filteredQuery($filter)
            ->with(['program', 'target', 'revisions'])
            ->orderBy($this->qualifiedSortField($filter->sortField), $filter->sortDirection === 'asc' ? 'asc' : 'desc')
            ->orderBy('tahfidz_submissions.created_at', 'desc');

        /** @var LengthAwarePaginator<int, TahfidzSubmissionRecord> $page */
        $page = $query->paginate($filter->perPage, ['tahfidz_submissions.*'], 'page', $filter->page);

        return new PaginatedTahfidzSubmissionData(
            data: array_map(
                fn (TahfidzSubmissionRecord $record): TahfidzSubmissionData => $this->map($record, includeRevisions: false),
                array_values($page->items()),
            ),
            currentPage: $page->currentPage(),
            perPage: $page->perPage(),
            total: $page->total(),
            lastPage: $page->lastPage(),
        );
    }

    public function findSubmission(string $id): ?TahfidzSubmissionData
    {
        $record = TahfidzSubmissionRecord::query()
            ->with([
                'program',
                'target',
                'revisions' => fn ($query) => $query->orderBy('changed_at')->orderBy('created_at'),
            ])
            ->find($id);

        if (! $record instanceof TahfidzSubmissionRecord) {
            return null;
        }

        return $this->map($record);
    }

    /** @return Builder<TahfidzSubmissionRecord> */
    private function filteredQuery(TahfidzListFilter $filter): Builder
    {
        return TahfidzSubmissionRecord::query()
            ->when($filter->search !== null, function (Builder $query) use ($filter): void {
                $query->where(function (Builder $query) use ($filter): void {
                    $query->where('student_name', 'like', '%'.$filter->search.'%')
                        ->orWhere('student_no', 'like', '%'.$filter->search.'%')
                        ->orWhere('supervisor_name', 'like', '%'.$filter->search.'%')
                        ->orWhere('surah', 'like', '%'.$filter->search.'%')
                        ->orWhereHas('program', function (Builder $query) use ($filter): void {
                            $query->where('name', 'like', '%'.$filter->search.'%')
                                ->orWhere('code', 'like', '%'.$filter->search.'%');
                        });
                });
            })
            ->when($filter->programId !== null, fn (Builder $query) => $query->where('program_id', $filter->programId))
            ->when($filter->studentId !== null, fn (Builder $query) => $query->where('student_id', $filter->studentId))
            ->when($filter->supervisorId !== null, fn (Builder $query) => $query->where('supervisor_id', $filter->supervisorId))
            ->when($filter->academicPeriodId !== null, fn (Builder $query) => $query->whereHas('target', fn (Builder $query) => $query->where('academic_period_id', $filter->academicPeriodId)))
            ->when($filter->type !== null, fn (Builder $query) => $query->where('type', $filter->type))
            ->when($filter->status !== null, fn (Builder $query) => $query->where('status', $filter->status))
            ->when($filter->dateFrom !== null, fn (Builder $query) => $query->whereDate('submission_date', '>=', $filter->dateFrom))
            ->when($filter->dateTo !== null, fn (Builder $query) => $query->whereDate('submission_date', '<=', $filter->dateTo));
    }

    private function map(TahfidzSubmissionRecord $record, bool $includeRevisions = true): TahfidzSubmissionData
    {
        $program = $record->program;
        $target = $record->target;
        $revisions = $record->revisions;
        $revisionCount = $revisions->count();

        if (! $program instanceof TahfidzProgramRecord) {
            throw new \RuntimeException('Program Tahfidz tidak ditemukan untuk setoran.');
        }

        return new TahfidzSubmissionData(
            id: (string) $record->getKey(),
            program: $this->mapProgram($program),
            target: $target instanceof TahfidzTargetRecord ? $this->mapTarget($target) : null,
            studentId: (string) $record->student_id,
            studentNo: (string) $record->student_no,
            studentName: (string) $record->student_name,
            supervisorId: $record->supervisor_id === null ? null : (string) $record->supervisor_id,
            supervisorName: $record->supervisor_name === null ? null : (string) $record->supervisor_name,
            submissionDate: $record->submission_date->toDateString(),
            type: (string) $record->type,
            juz: $record->juz === null ? null : (int) $record->juz,
            surah: $record->surah === null ? null : (string) $record->surah,
            ayahFrom: $record->ayah_from === null ? null : (int) $record->ayah_from,
            ayahTo: $record->ayah_to === null ? null : (int) $record->ayah_to,
            status: (string) $record->status,
            qualityNote: $record->quality_note === null ? null : (string) $record->quality_note,
            createdBy: $record->created_by === null ? null : (string) $record->created_by,
            reviewedAt: $record->reviewed_at?->toJSON(),
            reviewedBy: $record->reviewed_by === null ? null : (string) $record->reviewed_by,
            voidedAt: $record->voided_at?->toJSON(),
            voidedBy: $record->voided_by === null ? null : (string) $record->voided_by,
            voidReason: $record->void_reason === null ? null : (string) $record->void_reason,
            createdAt: $record->created_at?->toJSON(),
            updatedAt: $record->updated_at?->toJSON(),
            summary: new TahfidzSubmissionSummaryData(
                hasTarget: $target instanceof TahfidzTargetRecord,
                hasSupervisor: $record->supervisor_id !== null,
                hasRevision: $revisionCount > 0,
                revisionCount: $revisionCount,
            ),
            revisions: $includeRevisions
                ? array_values($revisions
                    ->map(fn (TahfidzSubmissionRevisionRecord $revision): TahfidzSubmissionRevisionData => $this->mapRevision($revision))
                    ->all())
                : [],
        );
    }

    private function mapProgram(TahfidzProgramRecord $program): TahfidzProgramData
    {
        return new TahfidzProgramData(
            id: (string) $program->getKey(),
            code: (string) $program->code,
            name: (string) $program->name,
            description: $program->description === null ? null : (string) $program->description,
            status: (string) $program->status,
        );
    }

    private function mapTarget(TahfidzTargetRecord $target): TahfidzTargetData
    {
        return new TahfidzTargetData(
            id: (string) $target->getKey(),
            studentId: (string) $target->student_id,
            studentNo: (string) $target->student_no,
            studentName: (string) $target->student_name,
            academicPeriodId: $target->academic_period_id === null ? null : (string) $target->academic_period_id,
            periodLabel: $target->period_label === null ? null : (string) $target->period_label,
            targetJuz: $target->target_juz === null ? null : (int) $target->target_juz,
            targetSurah: $target->target_surah === null ? null : (string) $target->target_surah,
            targetAyahFrom: $target->target_ayah_from === null ? null : (int) $target->target_ayah_from,
            targetAyahTo: $target->target_ayah_to === null ? null : (int) $target->target_ayah_to,
            targetNote: $target->target_note === null ? null : (string) $target->target_note,
            status: (string) $target->status,
        );
    }

    private function mapRevision(TahfidzSubmissionRevisionRecord $revision): TahfidzSubmissionRevisionData
    {
        return new TahfidzSubmissionRevisionData(
            id: (string) $revision->getKey(),
            reason: (string) $revision->reason,
            changedBy: $revision->changed_by === null ? null : (string) $revision->changed_by,
            changedAt: $revision->changed_at->toJSON(),
            summary: $revision->summary,
            createdAt: $revision->created_at?->toJSON(),
            updatedAt: $revision->updated_at?->toJSON(),
        );
    }

    private function qualifiedSortField(string $field): string
    {
        return match ($field) {
            'submission_date' => 'tahfidz_submissions.submission_date',
            'student_name' => 'tahfidz_submissions.student_name',
            'supervisor_name' => 'tahfidz_submissions.supervisor_name',
            'type' => 'tahfidz_submissions.type',
            'status' => 'tahfidz_submissions.status',
            default => 'tahfidz_submissions.created_at',
        };
    }

    private function periodLabel(?string $academicPeriodId): ?string
    {
        if ($academicPeriodId === null) {
            return null;
        }

        $period = DB::table('academic_terms')
            ->leftJoin('academic_years', 'academic_years.id', '=', 'academic_terms.academic_year_id')
            ->where('academic_terms.id', $academicPeriodId)
            ->select([
                'academic_terms.name as term_name',
                'academic_terms.code as term_code',
                'academic_years.name as year_name',
                'academic_years.code as year_code',
            ])
            ->first();

        if ($period === null) {
            return null;
        }

        $term = trim((string) ($period->term_name ?: $period->term_code));
        $year = trim((string) ($period->year_name ?: $period->year_code));

        return trim($term.' '.$year);
    }
}
