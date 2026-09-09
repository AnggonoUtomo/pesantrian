<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Infrastructure\Repositories;

use App\Modules\Pesantrian\PrestasiSantri\Application\Contracts\StudentAchievementCategoryMutationRepository;
use App\Modules\Pesantrian\PrestasiSantri\Application\Contracts\StudentAchievementMutationRepository;
use App\Modules\Pesantrian\PrestasiSantri\Application\Contracts\StudentAchievementReadRepository;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\PaginatedStudentAchievementData;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementCategoryData;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementCategoryListFilter;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementData;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementListFilter;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementMutationData;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementRevisionData;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementSummaryData;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\UpsertStudentAchievementCategoryData;
use App\Modules\Pesantrian\PrestasiSantri\Infrastructure\Models\StudentAchievementCategoryRecord;
use App\Modules\Pesantrian\PrestasiSantri\Infrastructure\Models\StudentAchievementRecord;
use App\Modules\Pesantrian\PrestasiSantri\Infrastructure\Models\StudentAchievementRevisionRecord;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class EloquentStudentAchievementReadRepository implements StudentAchievementCategoryMutationRepository, StudentAchievementMutationRepository, StudentAchievementReadRepository
{
    /** @var list<string> */
    private const FINAL_STATUSES = ['verified', 'void'];

    /** @var list<string> */
    private const NEEDS_ACTION_STATUSES = ['submitted', 'needs_revision'];

    public function findActiveCategory(string $id): ?StudentAchievementCategoryData
    {
        $record = StudentAchievementCategoryRecord::query()
            ->whereKey($id)
            ->where('status', 'active')
            ->first();

        if (! $record instanceof StudentAchievementCategoryRecord) {
            return null;
        }

        return $this->mapCategory($record);
    }

    public function createCategory(UpsertStudentAchievementCategoryData $data, ?string $actorId): StudentAchievementCategoryData
    {
        $record = StudentAchievementCategoryRecord::query()->create([
            ...$data->toArray(),
            'status' => 'active',
        ]);

        return $this->mapCategory($record);
    }

    /** @param array<string, string|null> $changes */
    public function updateCategory(string $id, array $changes): ?StudentAchievementCategoryData
    {
        $record = StudentAchievementCategoryRecord::query()->find($id);

        if (! $record instanceof StudentAchievementCategoryRecord) {
            return null;
        }

        $record->forceFill($changes)->save();

        return $this->mapCategory($record->refresh());
    }

    public function archiveCategory(string $id, string $reason, ?string $actorId): ?StudentAchievementCategoryData
    {
        $record = StudentAchievementCategoryRecord::query()->find($id);

        if (! $record instanceof StudentAchievementCategoryRecord) {
            return null;
        }

        $record->forceFill([
            'status' => 'archived',
            'archived_at' => now(),
            'archived_by' => $actorId,
            'archive_reason' => $reason,
        ])->save();

        return $this->mapCategory($record->refresh());
    }

    public function createDraft(StudentAchievementMutationData $data, ?string $actorId): StudentAchievementData
    {
        $record = StudentAchievementRecord::query()->create([
            ...$data->toDatabasePayload(includeNull: true),
            'achievement_no' => $this->nextAchievementNo(),
            'status' => 'draft',
            'submitted_at' => null,
            'submitted_by' => null,
            'verified_at' => null,
            'verified_by' => null,
            'verification_note' => null,
            'voided_at' => null,
            'voided_by' => null,
            'void_reason' => null,
            'created_by' => $actorId,
        ]);
        $this->createRevision($record, 'Draft prestasi dibuat.', $actorId, null, 'draft', [
            'action' => 'create',
            'changed_fields' => ['category_id', 'student_id', 'student_no', 'student_name', 'academic_period_id', 'mentor_employee_id', 'title', 'achievement_type', 'level', 'result', 'organizer', 'event_name', 'event_location', 'achieved_on', 'period_started_on', 'period_ended_on', 'description', 'notes', 'status'],
            'to_status' => 'draft',
        ]);

        return $this->mapAchievement($record->refresh()->load(['category', 'revisions'])->loadCount('revisions'));
    }

    public function updateDraft(string $id, StudentAchievementMutationData $data, string $reason, ?string $actorId): ?StudentAchievementData
    {
        $record = StudentAchievementRecord::query()->find($id);

        if (! $record instanceof StudentAchievementRecord) {
            return null;
        }

        $payload = $data->toDatabasePayload();

        $record->forceFill($payload)->save();
        $this->createRevision($record, $reason, $actorId, (string) $record->status, (string) $record->status, [
            'action' => 'update',
            'changed_fields' => array_keys($payload),
            'status' => $record->status,
        ]);

        return $this->mapAchievement($record->refresh()->load(['category', 'revisions'])->loadCount('revisions'));
    }

    public function submitDraft(string $id, string $actorId): ?StudentAchievementData
    {
        $record = StudentAchievementRecord::query()->find($id);

        if (! $record instanceof StudentAchievementRecord) {
            return null;
        }

        $fromStatus = (string) $record->status;
        $record->forceFill([
            'status' => 'submitted',
            'submitted_at' => now(),
            'submitted_by' => $actorId,
            'verified_at' => null,
            'verified_by' => null,
        ])->save();

        $this->createRevision($record, 'Draft prestasi disubmit untuk verifikasi.', $actorId, $fromStatus, 'submitted', [
            'action' => 'submit',
            'changed_fields' => ['status', 'submitted_at', 'submitted_by'],
            'from_status' => $fromStatus,
            'to_status' => 'submitted',
        ]);

        return $this->mapAchievement($record->refresh()->load(['category', 'revisions'])->loadCount('revisions'));
    }

    public function verify(string $id, ?string $verificationNote, string $actorId): ?StudentAchievementData
    {
        $record = StudentAchievementRecord::query()->find($id);

        if (! $record instanceof StudentAchievementRecord) {
            return null;
        }

        $record->forceFill([
            'status' => 'verified',
            'verified_at' => now(),
            'verified_by' => $actorId,
            'verification_note' => $verificationNote,
        ])->save();

        $this->createRevision($record, $verificationNote ?? 'Prestasi diverifikasi.', $actorId, 'submitted', 'verified', [
            'action' => 'verify',
            'changed_fields' => ['status', 'verified_at', 'verified_by', 'verification_note'],
            'from_status' => 'submitted',
            'to_status' => 'verified',
        ]);

        return $this->mapAchievement($record->refresh()->load(['category', 'revisions'])->loadCount('revisions'));
    }

    public function requestRevision(string $id, string $verificationNote, string $actorId): ?StudentAchievementData
    {
        $record = StudentAchievementRecord::query()->find($id);

        if (! $record instanceof StudentAchievementRecord) {
            return null;
        }

        $record->forceFill([
            'status' => 'needs_revision',
            'verification_note' => $verificationNote,
        ])->save();

        $this->createRevision($record, $verificationNote, $actorId, 'submitted', 'needs_revision', [
            'action' => 'request_revision',
            'changed_fields' => ['status', 'verification_note'],
            'from_status' => 'submitted',
            'to_status' => 'needs_revision',
        ]);

        return $this->mapAchievement($record->refresh()->load(['category', 'revisions'])->loadCount('revisions'));
    }

    /** @return list<StudentAchievementCategoryData> */
    public function categories(StudentAchievementCategoryListFilter $filter): array
    {
        return StudentAchievementCategoryRecord::query()
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
            ->map(fn (StudentAchievementCategoryRecord $record): StudentAchievementCategoryData => $this->mapCategory($record))
            ->values()
            ->all();
    }

    public function paginate(StudentAchievementListFilter $filter): PaginatedStudentAchievementData
    {
        $query = $this->filteredAchievementQuery($filter)
            ->with('category')
            ->withCount('revisions')
            ->orderBy($this->qualifiedSortField($filter->sortField), $filter->sortDirection === 'asc' ? 'asc' : 'desc')
            ->orderBy('student_achievements.created_at', 'desc');

        /** @var LengthAwarePaginator<int, StudentAchievementRecord> $page */
        $page = $query->paginate($filter->perPage, ['student_achievements.*'], 'page', $filter->page);

        return new PaginatedStudentAchievementData(
            data: array_map(
                fn (StudentAchievementRecord $record): StudentAchievementData => $this->mapAchievement($record, includeRevisions: false),
                array_values($page->items()),
            ),
            currentPage: $page->currentPage(),
            perPage: $page->perPage(),
            total: $page->total(),
            lastPage: $page->lastPage(),
        );
    }

    public function find(string $id): ?StudentAchievementData
    {
        $record = StudentAchievementRecord::query()
            ->with('category')
            ->with(['revisions' => fn ($query) => $query->orderBy('changed_at')])
            ->withCount('revisions')
            ->find($id);

        if (! $record instanceof StudentAchievementRecord) {
            return null;
        }

        return $this->mapAchievement($record);
    }

    /** @return Builder<StudentAchievementRecord> */
    private function filteredAchievementQuery(StudentAchievementListFilter $filter): Builder
    {
        return StudentAchievementRecord::query()
            ->when($filter->search !== null, function (Builder $query) use ($filter): void {
                $query->where(function (Builder $query) use ($filter): void {
                    $query->where('achievement_no', 'like', '%'.$filter->search.'%')
                        ->orWhere('student_no', 'like', '%'.$filter->search.'%')
                        ->orWhere('student_name', 'like', '%'.$filter->search.'%')
                        ->orWhere('category_name', 'like', '%'.$filter->search.'%')
                        ->orWhere('academic_period_label', 'like', '%'.$filter->search.'%')
                        ->orWhere('mentor_name', 'like', '%'.$filter->search.'%')
                        ->orWhere('title', 'like', '%'.$filter->search.'%')
                        ->orWhere('result', 'like', '%'.$filter->search.'%')
                        ->orWhere('organizer', 'like', '%'.$filter->search.'%')
                        ->orWhere('event_name', 'like', '%'.$filter->search.'%')
                        ->orWhere('event_location', 'like', '%'.$filter->search.'%')
                        ->orWhere('description', 'like', '%'.$filter->search.'%');
                });
            })
            ->when($filter->dateFrom !== null, fn (Builder $query) => $query->where('achieved_on', '>=', $filter->dateFrom))
            ->when($filter->dateTo !== null, fn (Builder $query) => $query->where('achieved_on', '<=', $filter->dateTo))
            ->when($filter->status !== null, fn (Builder $query) => $query->where('status', $filter->status))
            ->when($filter->level !== null, fn (Builder $query) => $query->where('level', $filter->level))
            ->when($filter->categoryId !== null, fn (Builder $query) => $query->where('category_id', $filter->categoryId))
            ->when($filter->studentId !== null, fn (Builder $query) => $query->where('student_id', $filter->studentId))
            ->when($filter->mentorEmployeeId !== null, fn (Builder $query) => $query->where('mentor_employee_id', $filter->mentorEmployeeId))
            ->when($filter->academicPeriodId !== null, fn (Builder $query) => $query->where('academic_period_id', $filter->academicPeriodId));
    }

    private function mapCategory(StudentAchievementCategoryRecord $record): StudentAchievementCategoryData
    {
        return new StudentAchievementCategoryData(
            id: (string) $record->getKey(),
            code: (string) $record->code,
            name: (string) $record->name,
            description: $record->description === null ? null : (string) $record->description,
            status: (string) $record->status,
            createdAt: $record->created_at?->toJSON(),
            updatedAt: $record->updated_at?->toJSON(),
        );
    }

    private function mapAchievement(StudentAchievementRecord $record, bool $includeRevisions = true): StudentAchievementData
    {
        $revisions = [];

        if ($includeRevisions) {
            /** @var iterable<StudentAchievementRevisionRecord> $revisionRecords */
            $revisionRecords = $record->revisions;

            foreach ($revisionRecords as $revisionRecord) {
                $revisions[] = $this->mapRevision($revisionRecord);
            }
        }

        /** @var StudentAchievementCategoryRecord $category */
        $category = $record->category;

        return new StudentAchievementData(
            id: (string) $record->getKey(),
            achievementNo: (string) $record->achievement_no,
            category: $this->mapCategory($category),
            studentId: (string) $record->student_id,
            studentNo: (string) $record->student_no,
            studentName: (string) $record->student_name,
            academicPeriodId: $record->academic_period_id === null ? null : (string) $record->academic_period_id,
            academicPeriodLabel: $record->academic_period_label === null ? null : (string) $record->academic_period_label,
            mentorEmployeeId: $record->mentor_employee_id === null ? null : (string) $record->mentor_employee_id,
            mentorName: $record->mentor_name === null ? null : (string) $record->mentor_name,
            title: (string) $record->title,
            achievementType: (string) $record->achievement_type,
            level: (string) $record->level,
            result: (string) $record->result,
            organizer: $record->organizer === null ? null : (string) $record->organizer,
            eventName: $record->event_name === null ? null : (string) $record->event_name,
            eventLocation: $record->event_location === null ? null : (string) $record->event_location,
            achievedOn: $record->achieved_on?->toJSON(),
            periodStartedOn: $record->period_started_on?->toJSON(),
            periodEndedOn: $record->period_ended_on?->toJSON(),
            description: $record->description === null ? null : (string) $record->description,
            notes: $record->notes === null ? null : (string) $record->notes,
            status: (string) $record->status,
            submittedAt: $record->submitted_at?->toJSON(),
            submittedBy: $record->submitted_by === null ? null : (string) $record->submitted_by,
            verifiedAt: $record->verified_at?->toJSON(),
            verifiedBy: $record->verified_by === null ? null : (string) $record->verified_by,
            verificationNote: $record->verification_note === null ? null : (string) $record->verification_note,
            voidedAt: $record->voided_at?->toJSON(),
            voidedBy: $record->voided_by === null ? null : (string) $record->voided_by,
            voidReason: $record->void_reason === null ? null : (string) $record->void_reason,
            createdBy: $record->created_by === null ? null : (string) $record->created_by,
            createdAt: $record->created_at?->toJSON(),
            updatedAt: $record->updated_at?->toJSON(),
            summary: new StudentAchievementSummaryData(
                isFinal: in_array($record->status, self::FINAL_STATUSES, true),
                needsAction: in_array($record->status, self::NEEDS_ACTION_STATUSES, true),
                revisionCount: (int) ($record->revisions_count ?? count($revisions)),
            ),
            revisions: $revisions,
        );
    }

    private function mapRevision(StudentAchievementRevisionRecord $record): StudentAchievementRevisionData
    {
        return new StudentAchievementRevisionData(
            id: (string) $record->getKey(),
            achievementId: (string) $record->achievement_id,
            fromStatus: $record->from_status === null ? null : (string) $record->from_status,
            toStatus: (string) $record->to_status,
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
            'achievement_no' => 'student_achievements.achievement_no',
            'student_name' => 'student_achievements.student_name',
            'status' => 'student_achievements.status',
            'level' => 'student_achievements.level',
            'achieved_on' => 'student_achievements.achieved_on',
            'created_at' => 'student_achievements.created_at',
            default => 'student_achievements.achieved_on',
        };
    }

    private function nextAchievementNo(): string
    {
        $latestAchievementNo = StudentAchievementRecord::query()
            ->where('achievement_no', 'like', 'PRS-%')
            ->orderByDesc('achievement_no')
            ->value('achievement_no');

        $nextNumber = 1;

        if (is_string($latestAchievementNo) && preg_match('/^PRS-(\d+)$/', $latestAchievementNo, $matches) === 1) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        do {
            $achievementNo = 'PRS-'.str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
            $nextNumber++;
        } while (StudentAchievementRecord::query()->where('achievement_no', $achievementNo)->exists());

        return $achievementNo;
    }

    /** @param array<string, mixed> $summary */
    private function createRevision(StudentAchievementRecord $record, string $reason, ?string $actorId, ?string $fromStatus, string $toStatus, array $summary): void
    {
        StudentAchievementRevisionRecord::query()->create([
            'achievement_id' => $record->getKey(),
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'reason' => $reason,
            'changed_by' => $actorId,
            'changed_at' => now(),
            'summary' => $summary,
        ]);
    }
}
