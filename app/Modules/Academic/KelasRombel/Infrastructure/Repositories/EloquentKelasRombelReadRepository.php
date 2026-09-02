<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Infrastructure\Repositories;

use App\Modules\Academic\KelasRombel\Application\Contracts\KelasRombelMutationRepository;
use App\Modules\Academic\KelasRombel\Application\Contracts\KelasRombelReadRepository;
use App\Modules\Academic\KelasRombel\Application\DTO\AssignHomeroomData;
use App\Modules\Academic\KelasRombel\Application\DTO\ClassGroupData;
use App\Modules\Academic\KelasRombel\Application\DTO\ClassGroupHomeroomData;
use App\Modules\Academic\KelasRombel\Application\DTO\ClassGroupListFilter;
use App\Modules\Academic\KelasRombel\Application\DTO\ClassGroupStudentData;
use App\Modules\Academic\KelasRombel\Application\DTO\ClassLevelData;
use App\Modules\Academic\KelasRombel\Application\DTO\CurriculumData;
use App\Modules\Academic\KelasRombel\Application\DTO\HomeroomAssignmentData;
use App\Modules\Academic\KelasRombel\Application\DTO\PaginatedClassGroupData;
use App\Modules\Academic\KelasRombel\Application\DTO\PlaceStudentData;
use App\Modules\Academic\KelasRombel\Application\DTO\ReferenceData;
use App\Modules\Academic\KelasRombel\Application\DTO\StudentPlacementData;
use App\Modules\Academic\KelasRombel\Application\DTO\StudentTransferData;
use App\Modules\Academic\KelasRombel\Application\DTO\UpsertClassGroupData;
use App\Modules\Academic\KelasRombel\Application\DTO\UpsertClassLevelData;
use App\Modules\Academic\KelasRombel\Application\DTO\UpsertCurriculumData;
use App\Modules\Academic\KelasRombel\Infrastructure\Models\AcademicCurriculumRecord;
use App\Modules\Academic\KelasRombel\Infrastructure\Models\ClassGroupHomeroomRecord;
use App\Modules\Academic\KelasRombel\Infrastructure\Models\ClassGroupRecord;
use App\Modules\Academic\KelasRombel\Infrastructure\Models\ClassGroupStudentRecord;
use App\Modules\Academic\KelasRombel\Infrastructure\Models\ClassLevelRecord;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class EloquentKelasRombelReadRepository implements KelasRombelMutationRepository, KelasRombelReadRepository
{
    public function paginateClassGroups(ClassGroupListFilter $filter): PaginatedClassGroupData
    {
        $query = $this->baseQuery()
            ->when($filter->search !== null, function (Builder $query) use ($filter): void {
                $query->where(function (Builder $query) use ($filter): void {
                    $query->where('class_groups.name', 'like', '%'.$filter->search.'%')
                        ->orWhere('class_groups.code', 'like', '%'.$filter->search.'%')
                        ->orWhere('class_levels.name', 'like', '%'.$filter->search.'%');
                });
            })
            ->when($filter->academicYearId !== null, fn (Builder $query) => $query->where('class_groups.academic_year_id', $filter->academicYearId))
            ->when($filter->academicTermId !== null, fn (Builder $query) => $query->where('class_groups.academic_term_id', $filter->academicTermId))
            ->when($filter->unitId !== null, fn (Builder $query) => $query->where('class_groups.unit_id', $filter->unitId))
            ->when($filter->curriculumId !== null, fn (Builder $query) => $query->where('class_groups.curriculum_id', $filter->curriculumId))
            ->when($filter->status !== null, fn (Builder $query) => $query->where('class_groups.status', $filter->status))
            ->when(
                $filter->archived === 'archived',
                fn (Builder $query) => $query->whereNotNull('class_groups.archived_at'),
                fn (Builder $query) => $query->whereNull('class_groups.archived_at'),
            )
            ->orderBy($this->qualifiedSortField($filter->sortField), $filter->sortDirection === 'desc' ? 'desc' : 'asc');

        /** @var LengthAwarePaginator<int, ClassGroupRecord> $page */
        $page = $query->paginate($filter->perPage, ['class_groups.*'], 'page', $filter->page);

        return new PaginatedClassGroupData(
            data: array_map(
                fn (ClassGroupRecord $record): ClassGroupData => $this->map($record),
                array_values($page->items()),
            ),
            currentPage: $page->currentPage(),
            perPage: $page->perPage(),
            total: $page->total(),
            lastPage: $page->lastPage(),
        );
    }

    public function findClassGroup(string $id): ?ClassGroupData
    {
        $record = $this->baseQuery()->find($id, ['class_groups.*']);

        if (! $record instanceof ClassGroupRecord) {
            return null;
        }

        return $this->map($record, includeDetails: true);
    }

    public function createCurriculum(UpsertCurriculumData $data): CurriculumData
    {
        /** @var AcademicCurriculumRecord $record */
        $record = AcademicCurriculumRecord::query()->create($data->toArray());

        return $this->mapCurriculum($record);
    }

    public function updateCurriculum(string $id, array $changes): ?CurriculumData
    {
        $record = AcademicCurriculumRecord::query()->find($id);

        if (! $record instanceof AcademicCurriculumRecord) {
            return null;
        }

        $record->fill($changes);
        $record->save();

        return $this->mapCurriculum($record->refresh());
    }

    public function createClassLevel(UpsertClassLevelData $data): ClassLevelData
    {
        /** @var ClassLevelRecord $record */
        $record = ClassLevelRecord::query()->create($data->toArray());

        return $this->mapClassLevel($record);
    }

    public function updateClassLevel(string $id, array $changes): ?ClassLevelData
    {
        $record = ClassLevelRecord::query()->find($id);

        if (! $record instanceof ClassLevelRecord) {
            return null;
        }

        $record->fill($changes);
        $record->save();

        return $this->mapClassLevel($record->refresh());
    }

    public function createClassGroup(UpsertClassGroupData $data): ClassGroupData
    {
        /** @var ClassGroupRecord $record */
        $record = ClassGroupRecord::query()->create($data->toArray());

        return $this->freshClassGroupData($record);
    }

    public function updateClassGroup(string $id, array $changes): ?ClassGroupData
    {
        $record = ClassGroupRecord::query()->find($id);

        if (! $record instanceof ClassGroupRecord) {
            return null;
        }

        $record->fill($changes);
        $record->save();

        return $this->freshClassGroupData($record);
    }

    public function findPlacement(string $id): ?StudentPlacementData
    {
        $record = ClassGroupStudentRecord::query()->find($id);

        return $record instanceof ClassGroupStudentRecord ? $this->mapPlacement($record) : null;
    }

    public function findActivePlacementForStudentInTerm(string $studentId, string $academicTermId): ?StudentPlacementData
    {
        $record = ClassGroupStudentRecord::query()
            ->where('student_id', $studentId)
            ->where('academic_term_id', $academicTermId)
            ->where('status', 'active')
            ->whereNotNull('active_period_student_key')
            ->first();

        return $record instanceof ClassGroupStudentRecord ? $this->mapPlacement($record) : null;
    }

    public function placeStudent(PlaceStudentData $data): StudentPlacementData
    {
        /** @var ClassGroupStudentRecord $record */
        $record = ClassGroupStudentRecord::query()->create($data->toArray());

        return $this->mapPlacement($record);
    }

    public function transferStudent(string $placementId, PlaceStudentData $target, string $reason): ?StudentTransferData
    {
        $previous = ClassGroupStudentRecord::query()->find($placementId);

        if (! $previous instanceof ClassGroupStudentRecord || $previous->status !== 'active') {
            return null;
        }

        $joinedOn = Carbon::parse($target->joinedOn)->toImmutable();
        $previous->forceFill([
            'left_on' => $joinedOn->subDay()->toDateString(),
            'status' => 'transferred',
            'reason' => $reason,
            'active_period_student_key' => null,
        ])->save();

        /** @var ClassGroupStudentRecord $current */
        $current = ClassGroupStudentRecord::query()->create($target->toArray());

        return new StudentTransferData(
            previous: $this->mapPlacement($previous->refresh()),
            current: $this->mapPlacement($current),
        );
    }

    public function removeStudent(string $placementId, string $leftOn, string $reason): ?StudentPlacementData
    {
        $record = ClassGroupStudentRecord::query()->find($placementId);

        if (! $record instanceof ClassGroupStudentRecord || $record->status !== 'active') {
            return null;
        }

        $record->forceFill([
            'left_on' => $leftOn,
            'status' => 'removed',
            'reason' => $reason,
            'active_period_student_key' => null,
        ])->save();

        return $this->mapPlacement($record->refresh());
    }

    public function findHomeroom(string $id): ?HomeroomAssignmentData
    {
        $record = ClassGroupHomeroomRecord::query()->find($id);

        return $record instanceof ClassGroupHomeroomRecord ? $this->mapHomeroom($record) : null;
    }

    public function findActiveHomeroomForClassGroup(string $classGroupId): ?HomeroomAssignmentData
    {
        $record = ClassGroupHomeroomRecord::query()
            ->where('class_group_id', $classGroupId)
            ->where('status', 'active')
            ->whereNotNull('active_class_group_key')
            ->first();

        return $record instanceof ClassGroupHomeroomRecord ? $this->mapHomeroom($record) : null;
    }

    public function assignHomeroom(AssignHomeroomData $data): HomeroomAssignmentData
    {
        /** @var ClassGroupHomeroomRecord $record */
        $record = ClassGroupHomeroomRecord::query()->create($data->toArray());

        return $this->mapHomeroom($record);
    }

    public function endHomeroom(string $homeroomId, string $endedOn, string $reason): ?HomeroomAssignmentData
    {
        $record = ClassGroupHomeroomRecord::query()->find($homeroomId);

        if (! $record instanceof ClassGroupHomeroomRecord || $record->status !== 'active') {
            return null;
        }

        $record->forceFill([
            'ended_on' => $endedOn,
            'status' => 'ended',
            'reason' => $reason,
            'active_class_group_key' => null,
        ])->save();

        return $this->mapHomeroom($record->refresh());
    }

    public function archiveClassGroup(string $id, ?string $actorId): ?ClassGroupData
    {
        $record = ClassGroupRecord::query()
            ->whereKey($id)
            ->whereNull('archived_at')
            ->first();

        if (! $record instanceof ClassGroupRecord) {
            return null;
        }

        $record->forceFill([
            'status' => 'archived',
            'archived_at' => now(),
            'archived_by' => $actorId,
        ])->save();

        return $this->freshClassGroupData($record);
    }

    public function restoreClassGroup(string $id): ?ClassGroupData
    {
        $record = ClassGroupRecord::query()
            ->whereKey($id)
            ->whereNotNull('archived_at')
            ->first();

        if (! $record instanceof ClassGroupRecord) {
            return null;
        }

        $record->forceFill([
            'status' => 'active',
            'archived_at' => null,
            'archived_by' => null,
        ])->save();

        return $this->freshClassGroupData($record);
    }

    /** @return Builder<ClassGroupRecord> */
    private function baseQuery(): Builder
    {
        return ClassGroupRecord::query()
            ->with(['curriculum', 'classLevel'])
            ->leftJoin('academic_years', 'academic_years.id', '=', 'class_groups.academic_year_id')
            ->leftJoin('academic_terms', 'academic_terms.id', '=', 'class_groups.academic_term_id')
            ->leftJoin('organization_units', 'organization_units.id', '=', 'class_groups.unit_id')
            ->leftJoin('class_levels', 'class_levels.id', '=', 'class_groups.class_level_id')
            ->leftJoin('academic_curricula', 'academic_curricula.id', '=', 'class_groups.curriculum_id')
            ->select('class_groups.*')
            ->addSelect([
                DB::raw('academic_years.code as academic_year_code'),
                DB::raw('academic_years.name as academic_year_name'),
                DB::raw('academic_terms.code as academic_term_code'),
                DB::raw('academic_terms.name as academic_term_name'),
                DB::raw('organization_units.code as unit_code'),
                DB::raw('organization_units.name as unit_name'),
                DB::raw('class_levels.code as class_level_code'),
                DB::raw('class_levels.name as class_level_name'),
                DB::raw('academic_curricula.code as curriculum_code'),
                DB::raw('academic_curricula.name as curriculum_name'),
            ]);
    }

    private function map(ClassGroupRecord $record, bool $includeDetails = false): ClassGroupData
    {
        return new ClassGroupData(
            id: (string) $record->getKey(),
            academicYear: new ReferenceData(
                id: (string) $record->academic_year_id,
                code: (string) $record->getAttribute('academic_year_code'),
                name: (string) $record->getAttribute('academic_year_name'),
            ),
            academicTerm: new ReferenceData(
                id: (string) $record->academic_term_id,
                code: (string) $record->getAttribute('academic_term_code'),
                name: (string) $record->getAttribute('academic_term_name'),
            ),
            unit: new ReferenceData(
                id: (string) $record->unit_id,
                code: (string) $record->getAttribute('unit_code'),
                name: (string) $record->getAttribute('unit_name'),
            ),
            curriculum: $record->curriculum_id === null ? null : new ReferenceData(
                id: (string) $record->curriculum_id,
                code: (string) $record->getAttribute('curriculum_code'),
                name: (string) $record->getAttribute('curriculum_name'),
            ),
            classLevel: new ReferenceData(
                id: (string) $record->class_level_id,
                code: (string) $record->getAttribute('class_level_code'),
                name: (string) $record->getAttribute('class_level_name'),
            ),
            code: (string) $record->code,
            name: (string) $record->name,
            capacity: $record->capacity === null ? null : (int) $record->capacity,
            status: (string) $record->status,
            archivedAt: $record->archived_at?->toJSON(),
            createdAt: $record->created_at?->toJSON(),
            updatedAt: $record->updated_at?->toJSON(),
            students: $includeDetails ? $this->studentsFor($record) : [],
            homerooms: $includeDetails ? $this->homeroomsFor($record) : [],
        );
    }

    private function mapCurriculum(AcademicCurriculumRecord $record): CurriculumData
    {
        return new CurriculumData(
            id: (string) $record->getKey(),
            code: (string) $record->code,
            name: (string) $record->name,
            description: $record->description === null ? null : (string) $record->description,
            status: (string) $record->status,
            createdAt: $record->created_at?->toJSON(),
            updatedAt: $record->updated_at?->toJSON(),
        );
    }

    private function mapClassLevel(ClassLevelRecord $record): ClassLevelData
    {
        return new ClassLevelData(
            id: (string) $record->getKey(),
            unitId: (string) $record->unit_id,
            code: (string) $record->code,
            name: (string) $record->name,
            sequence: (int) $record->sequence,
            status: (string) $record->status,
            createdAt: $record->created_at?->toJSON(),
            updatedAt: $record->updated_at?->toJSON(),
        );
    }

    private function mapPlacement(ClassGroupStudentRecord $record): StudentPlacementData
    {
        return new StudentPlacementData(
            id: (string) $record->getKey(),
            classGroupId: (string) $record->class_group_id,
            academicTermId: (string) $record->academic_term_id,
            studentId: (string) $record->student_id,
            studentNo: (string) $record->student_no,
            joinedOn: $record->joined_on->toDateString(),
            leftOn: $record->left_on?->toDateString(),
            status: (string) $record->status,
            reason: $record->reason === null ? null : (string) $record->reason,
            createdAt: $record->created_at?->toJSON(),
            updatedAt: $record->updated_at?->toJSON(),
        );
    }

    private function mapHomeroom(ClassGroupHomeroomRecord $record): HomeroomAssignmentData
    {
        return new HomeroomAssignmentData(
            id: (string) $record->getKey(),
            classGroupId: (string) $record->class_group_id,
            employeeId: (string) $record->employee_id,
            employeeName: (string) $record->employee_name,
            assignedOn: $record->assigned_on->toDateString(),
            endedOn: $record->ended_on?->toDateString(),
            status: (string) $record->status,
            reason: $record->reason === null ? null : (string) $record->reason,
            createdAt: $record->created_at?->toJSON(),
            updatedAt: $record->updated_at?->toJSON(),
        );
    }

    private function freshClassGroupData(ClassGroupRecord $record): ClassGroupData
    {
        $fresh = $this->findClassGroup((string) $record->getKey());

        if (! $fresh instanceof ClassGroupData) {
            throw new RuntimeException('Rombel gagal dibaca ulang setelah mutation.');
        }

        return $fresh;
    }

    /** @return list<ClassGroupStudentData> */
    private function studentsFor(ClassGroupRecord $record): array
    {
        return ClassGroupStudentRecord::query()
            ->leftJoin('students', 'students.id', '=', 'class_group_students.student_id')
            ->where('class_group_students.class_group_id', $record->id)
            ->orderByDesc('class_group_students.status')
            ->orderBy('class_group_students.joined_on')
            ->get([
                'class_group_students.*',
                'students.full_name as student_name',
            ])
            ->map(static fn (ClassGroupStudentRecord $placement): ClassGroupStudentData => new ClassGroupStudentData(
                id: (string) $placement->getKey(),
                studentId: (string) $placement->student_id,
                studentNo: (string) $placement->student_no,
                studentName: $placement->getAttribute('student_name') === null ? null : (string) $placement->getAttribute('student_name'),
                joinedOn: $placement->joined_on->toDateString(),
                leftOn: $placement->left_on?->toDateString(),
                status: (string) $placement->status,
                reason: $placement->reason === null ? null : (string) $placement->reason,
            ))
            ->values()
            ->all();
    }

    /** @return list<ClassGroupHomeroomData> */
    private function homeroomsFor(ClassGroupRecord $record): array
    {
        return ClassGroupHomeroomRecord::query()
            ->where('class_group_id', $record->id)
            ->orderByDesc('status')
            ->orderBy('assigned_on')
            ->get()
            ->map(static fn (ClassGroupHomeroomRecord $homeroom): ClassGroupHomeroomData => new ClassGroupHomeroomData(
                id: (string) $homeroom->getKey(),
                employeeId: (string) $homeroom->employee_id,
                employeeName: (string) $homeroom->employee_name,
                assignedOn: $homeroom->assigned_on->toDateString(),
                endedOn: $homeroom->ended_on?->toDateString(),
                status: (string) $homeroom->status,
                reason: $homeroom->reason === null ? null : (string) $homeroom->reason,
            ))
            ->values()
            ->all();
    }

    private function qualifiedSortField(string $field): string
    {
        return match ($field) {
            'code' => 'class_groups.code',
            'name' => 'class_groups.name',
            'capacity' => 'class_groups.capacity',
            'status' => 'class_groups.status',
            default => 'class_groups.created_at',
        };
    }
}
