<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Presentation\Controllers;

use App\Http\ApiResponseFactory;
use App\Modules\Academic\KelasRombel\Application\Actions\ArchiveClassGroup;
use App\Modules\Academic\KelasRombel\Application\Actions\AssignHomeroom;
use App\Modules\Academic\KelasRombel\Application\Actions\CreateClassGroup;
use App\Modules\Academic\KelasRombel\Application\Actions\CreateClassLevel;
use App\Modules\Academic\KelasRombel\Application\Actions\CreateCurriculum;
use App\Modules\Academic\KelasRombel\Application\Actions\EndHomeroom;
use App\Modules\Academic\KelasRombel\Application\Actions\PlaceStudent;
use App\Modules\Academic\KelasRombel\Application\Actions\RestoreClassGroup;
use App\Modules\Academic\KelasRombel\Application\Actions\UpdateClassGroup;
use App\Modules\Academic\KelasRombel\Application\Actions\UpdateClassLevel;
use App\Modules\Academic\KelasRombel\Application\Actions\UpdateCurriculum;
use App\Modules\Academic\KelasRombel\Application\DTO\ClassGroupData;
use App\Modules\Academic\KelasRombel\Application\DTO\PaginatedClassGroupData;
use App\Modules\Academic\KelasRombel\Application\Exceptions\KelasRombelHomeroomException;
use App\Modules\Academic\KelasRombel\Application\Exceptions\KelasRombelPlacementException;
use App\Modules\Academic\KelasRombel\Application\Queries\ListClassGroups;
use App\Modules\Academic\KelasRombel\Application\Queries\ShowClassGroup;
use App\Modules\Academic\KelasRombel\Presentation\Requests\ArchiveClassGroupApiRequest;
use App\Modules\Academic\KelasRombel\Presentation\Requests\AssignHomeroomApiRequest;
use App\Modules\Academic\KelasRombel\Presentation\Requests\EndHomeroomApiRequest;
use App\Modules\Academic\KelasRombel\Presentation\Requests\ListClassGroupsApiRequest;
use App\Modules\Academic\KelasRombel\Presentation\Requests\PlaceStudentApiRequest;
use App\Modules\Academic\KelasRombel\Presentation\Requests\StoreClassGroupApiRequest;
use App\Modules\Academic\KelasRombel\Presentation\Requests\StoreClassLevelApiRequest;
use App\Modules\Academic\KelasRombel\Presentation\Requests\StoreCurriculumApiRequest;
use App\Modules\Academic\KelasRombel\Presentation\Requests\UpdateClassGroupApiRequest;
use App\Modules\Academic\KelasRombel\Presentation\Requests\UpdateClassLevelApiRequest;
use App\Modules\Academic\KelasRombel\Presentation\Requests\UpdateCurriculumApiRequest;
use App\Modules\Academic\KelasRombel\Presentation\Resources\ClassGroupResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

final readonly class ClassGroupController implements HasMiddleware
{
    public function __construct(
        private ListClassGroups $listClassGroups,
        private ShowClassGroup $showClassGroup,
        private CreateCurriculum $createCurriculum,
        private UpdateCurriculum $updateCurriculum,
        private CreateClassLevel $createClassLevel,
        private UpdateClassLevel $updateClassLevel,
        private CreateClassGroup $createClassGroup,
        private UpdateClassGroup $updateClassGroup,
        private ArchiveClassGroup $archiveClassGroup,
        private RestoreClassGroup $restoreClassGroup,
        private PlaceStudent $placeStudent,
        private AssignHomeroom $assignHomeroom,
        private EndHomeroom $endHomeroom,
        private ApiResponseFactory $responses,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:kelas_rombel.view', only: ['index', 'show']),
            new Middleware('can:kelas_rombel.manage', only: [
                'storeCurriculum',
                'updateCurriculum',
                'storeLevel',
                'updateLevel',
                'store',
                'update',
                'storeHomeroom',
                'endHomeroom',
            ]),
            new Middleware('can:kelas_rombel.placement', only: ['storeStudent']),
            new Middleware('can:kelas_rombel.archive', only: ['archive', 'restore']),
        ];
    }

    public function index(ListClassGroupsApiRequest $request): Response
    {
        $result = $this->listClassGroups->execute($request->toFilter());

        return Inertia::render('Academic/KelasRombel/pages/Index', [
            'classGroups' => [
                'data' => array_map(
                    static fn (ClassGroupData $classGroup): array => (new ClassGroupResource($classGroup))->toArray($request),
                    $result->data,
                ),
                'meta' => $this->paginationMeta($result),
            ],
            'filters' => $request->safe()->only(['search', 'filter', 'page', 'per_page', 'sort']),
            'pagination' => [
                'perPageOptions' => [10, 25, 50, 100],
                'defaultPerPage' => 25,
            ],
            'options' => [
                'academicYears' => $this->referenceOptions('academic_years'),
                'academicTerms' => $this->referenceOptions('academic_terms'),
                'units' => $this->referenceOptions('organization_units'),
                'curricula' => $this->referenceOptions('academic_curricula'),
                'classLevels' => $this->referenceOptions('class_levels'),
            ],
            'canManage' => $request->user()?->can('kelas_rombel.manage') === true,
            'canPlacement' => $request->user()?->can('kelas_rombel.placement') === true,
            'canArchive' => $request->user()?->can('kelas_rombel.archive') === true,
        ]);
    }

    public function show(Request $request, string $classGroup): Response
    {
        $data = $this->showClassGroup->execute($classGroup);

        abort_if($data === null, 404);

        return Inertia::render('Academic/KelasRombel/pages/Show', [
            'classGroup' => (new ClassGroupResource($data, includeDetails: true))->toArray($request),
            'options' => [
                'students' => $this->studentOptions((string) $data->unit->id),
                'employees' => $this->employeeOptions((string) $data->unit->id),
            ],
            'canManage' => $request->user()?->can('kelas_rombel.manage') === true,
            'canPlacement' => $request->user()?->can('kelas_rombel.placement') === true,
            'canArchive' => $request->user()?->can('kelas_rombel.archive') === true,
        ]);
    }

    public function storeCurriculum(StoreCurriculumApiRequest $request): RedirectResponse
    {
        $this->createCurriculum->execute($request->user(), $request->toData(), $this->responses->correlationId($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Kurikulum berhasil dibuat.']);

        return back();
    }

    public function updateCurriculum(UpdateCurriculumApiRequest $request, string $curriculum): RedirectResponse
    {
        $updated = $this->updateCurriculum->execute($request->user(), $curriculum, $request->changes(), $this->responses->correlationId($request));

        abort_if($updated === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Kurikulum berhasil diperbarui.']);

        return back();
    }

    public function storeLevel(StoreClassLevelApiRequest $request): RedirectResponse
    {
        $this->createClassLevel->execute($request->user(), $request->toData(), $this->responses->correlationId($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Tingkat kelas berhasil dibuat.']);

        return back();
    }

    public function updateLevel(UpdateClassLevelApiRequest $request, string $level): RedirectResponse
    {
        $updated = $this->updateClassLevel->execute($request->user(), $level, $request->changes(), $this->responses->correlationId($request));

        abort_if($updated === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Tingkat kelas berhasil diperbarui.']);

        return back();
    }

    public function store(StoreClassGroupApiRequest $request): RedirectResponse
    {
        $classGroup = $this->createClassGroup->execute($request->user(), $request->toData(), $this->responses->correlationId($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Rombel berhasil dibuat.']);

        return to_route('academic.class-groups.show', $classGroup->id);
    }

    public function update(UpdateClassGroupApiRequest $request, string $classGroup): RedirectResponse
    {
        $updated = $this->updateClassGroup->execute($request->user(), $classGroup, $request->changes(), $this->responses->correlationId($request));

        abort_if($updated === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Rombel berhasil diperbarui.']);

        return back();
    }

    public function archive(ArchiveClassGroupApiRequest $request, string $classGroup): RedirectResponse
    {
        $archived = $this->archiveClassGroup->execute(
            $request->user(),
            $classGroup,
            (string) $request->validated('reason'),
            $this->responses->correlationId($request),
        );

        abort_if($archived === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Rombel berhasil diarsipkan.']);

        return back();
    }

    public function restore(Request $request, string $classGroup): RedirectResponse
    {
        $restored = $this->restoreClassGroup->execute($request->user(), $classGroup, $this->responses->correlationId($request));

        abort_if($restored === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Rombel berhasil dipulihkan.']);

        return back();
    }

    public function storeStudent(PlaceStudentApiRequest $request, string $classGroup): RedirectResponse
    {
        try {
            $placement = $this->placeStudent->execute(
                $request->user(),
                $classGroup,
                (string) $request->validated('student_id'),
                (string) $request->validated('joined_on'),
                $this->responses->correlationId($request),
            );
        } catch (KelasRombelPlacementException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        abort_if($placement === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Santri berhasil ditempatkan ke rombel.']);

        return back();
    }

    public function storeHomeroom(AssignHomeroomApiRequest $request, string $classGroup): RedirectResponse
    {
        try {
            $homeroom = $this->assignHomeroom->execute(
                $request->user(),
                $classGroup,
                (string) $request->validated('employee_id'),
                (string) $request->validated('assigned_on'),
                $this->responses->correlationId($request),
            );
        } catch (KelasRombelHomeroomException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        abort_if($homeroom === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Wali kelas berhasil ditetapkan.']);

        return back();
    }

    public function endHomeroom(EndHomeroomApiRequest $request, string $classGroup, string $homeroom): RedirectResponse
    {
        try {
            $ended = $this->endHomeroom->execute(
                $request->user(),
                $classGroup,
                $homeroom,
                (string) $request->validated('ended_on'),
                (string) $request->validated('reason'),
                $this->responses->correlationId($request),
            );
        } catch (KelasRombelHomeroomException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        abort_if($ended === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Wali kelas berhasil diakhiri.']);

        return back();
    }

    /** @return array{currentPage: int, perPage: int, total: int, lastPage: int} */
    private function paginationMeta(PaginatedClassGroupData $result): array
    {
        return [
            'currentPage' => $result->currentPage,
            'perPage' => $result->perPage,
            'total' => $result->total,
            'lastPage' => $result->lastPage,
        ];
    }

    /**
     * @return list<array{id: string, code: string, name: string}>
     */
    private function referenceOptions(string $table): array
    {
        $records = DB::table($table)
            ->select(['id', 'code', 'name'])
            ->orderBy('name')
            ->get();

        $options = [];

        foreach ($records as $record) {
            $options[] = [
                'id' => (string) $record->id,
                'code' => (string) $record->code,
                'name' => (string) $record->name,
            ];
        }

        return $options;
    }

    /**
     * @return list<array{id: string, code: string, name: string}>
     */
    private function studentOptions(string $unitId): array
    {
        $records = DB::table('students')
            ->select(['id', 'student_no', 'full_name'])
            ->where('primary_unit_id', $unitId)
            ->where('status', 'active')
            ->orderBy('full_name')
            ->limit(200)
            ->get();

        $options = [];

        foreach ($records as $record) {
            $options[] = [
                'id' => (string) $record->id,
                'code' => (string) $record->student_no,
                'name' => (string) $record->full_name,
            ];
        }

        return $options;
    }

    /**
     * @return list<array{id: string, code: string, name: string}>
     */
    private function employeeOptions(string $unitId): array
    {
        $records = DB::table('employees')
            ->select(['id', 'employee_no', 'name'])
            ->where('status', 'active')
            ->where('primary_unit_id', $unitId)
            ->where('employment_type', 'teacher')
            ->orderBy('name')
            ->limit(200)
            ->get();

        $options = [];

        foreach ($records as $record) {
            $options[] = [
                'id' => (string) $record->id,
                'code' => (string) $record->employee_no,
                'name' => (string) $record->name,
            ];
        }

        return $options;
    }
}
