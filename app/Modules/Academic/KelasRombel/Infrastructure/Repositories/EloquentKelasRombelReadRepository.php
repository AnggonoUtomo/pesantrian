<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Infrastructure\Repositories;

use App\Modules\Academic\KelasRombel\Application\Contracts\KelasRombelReadRepository;
use App\Modules\Academic\KelasRombel\Application\DTO\ClassGroupData;
use App\Modules\Academic\KelasRombel\Application\DTO\ClassGroupHomeroomData;
use App\Modules\Academic\KelasRombel\Application\DTO\ClassGroupListFilter;
use App\Modules\Academic\KelasRombel\Application\DTO\ClassGroupStudentData;
use App\Modules\Academic\KelasRombel\Application\DTO\PaginatedClassGroupData;
use App\Modules\Academic\KelasRombel\Application\DTO\ReferenceData;
use App\Modules\Academic\KelasRombel\Infrastructure\Models\ClassGroupHomeroomRecord;
use App\Modules\Academic\KelasRombel\Infrastructure\Models\ClassGroupRecord;
use App\Modules\Academic\KelasRombel\Infrastructure\Models\ClassGroupStudentRecord;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class EloquentKelasRombelReadRepository implements KelasRombelReadRepository
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
