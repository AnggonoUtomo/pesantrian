<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Santri\Presentation\Controllers;

use App\Http\ApiResponseFactory;
use App\Modules\Pesantrian\Santri\Application\Actions\ArchiveStudent;
use App\Modules\Pesantrian\Santri\Application\Actions\ChangeStudentLifecycle;
use App\Modules\Pesantrian\Santri\Application\Actions\CreateStudent;
use App\Modules\Pesantrian\Santri\Application\Actions\CreateStudentFromAcceptedAdmission;
use App\Modules\Pesantrian\Santri\Application\Actions\RestoreStudent;
use App\Modules\Pesantrian\Santri\Application\Actions\UpdateStudent;
use App\Modules\Pesantrian\Santri\Application\DTO\PaginatedStudentData;
use App\Modules\Pesantrian\Santri\Application\DTO\StudentData;
use App\Modules\Pesantrian\Santri\Application\Queries\ListStudents;
use App\Modules\Pesantrian\Santri\Application\Queries\ShowStudent;
use App\Modules\Pesantrian\Santri\Presentation\Requests\ArchiveStudentApiRequest;
use App\Modules\Pesantrian\Santri\Presentation\Requests\ChangeStudentLifecycleApiRequest;
use App\Modules\Pesantrian\Santri\Presentation\Requests\ListStudentsApiRequest;
use App\Modules\Pesantrian\Santri\Presentation\Requests\StoreStudentApiRequest;
use App\Modules\Pesantrian\Santri\Presentation\Requests\UpdateStudentApiRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

final readonly class StudentController implements HasMiddleware
{
    public function __construct(
        private ListStudents $listStudents,
        private ShowStudent $showStudent,
        private CreateStudent $createStudent,
        private CreateStudentFromAcceptedAdmission $createStudentFromAcceptedAdmission,
        private UpdateStudent $updateStudent,
        private ChangeStudentLifecycle $changeStudentLifecycle,
        private ArchiveStudent $archiveStudent,
        private RestoreStudent $restoreStudent,
        private ApiResponseFactory $responses,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:santri.view', only: ['index', 'show']),
            new Middleware('can:santri.manage', only: ['store', 'storeFromAdmission', 'update']),
            new Middleware('can:santri.lifecycle', only: ['lifecycle']),
            new Middleware('can:santri.archive', only: ['archive', 'restore']),
        ];
    }

    public function index(ListStudentsApiRequest $request): Response
    {
        $result = $this->listStudents->execute($request->toFilter());

        return Inertia::render('Pesantrian/Santri/pages/Index', [
            'students' => [
                'data' => array_map(
                    static fn (StudentData $student): array => $student->toArray(includeGuardians: false),
                    $result->data,
                ),
                'meta' => $this->paginationMeta($result),
            ],
            'filters' => $request->safe()->only(['search', 'filter', 'page', 'per_page', 'sort']),
            'pagination' => [
                'perPageOptions' => [10, 25, 50, 100],
                'defaultPerPage' => 25,
            ],
            'primaryUnitOptions' => $this->primaryUnitOptions(),
        ]);
    }

    public function show(string $student): Response
    {
        $data = $this->showStudent->execute($student);

        abort_if($data === null, 404);

        return Inertia::render('Pesantrian/Santri/pages/Show', [
            'student' => $data->toArray(),
            'primaryUnitOptions' => $this->primaryUnitOptions(),
        ]);
    }

    public function store(StoreStudentApiRequest $request): RedirectResponse
    {
        $student = $this->createStudent->execute(
            $request->user(),
            $request->toData(),
            $this->responses->correlationId($request),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Data santri berhasil dibuat.']);

        return redirect()->route('pesantrian.students.show', $student->id);
    }

    public function storeFromAdmission(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'admission_id' => ['required', 'ulid', Rule::exists('student_admissions', 'id')],
        ]);

        $student = $this->createStudentFromAcceptedAdmission->execute(
            $request->user(),
            (string) $validated['admission_id'],
            $this->responses->correlationId($request),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Pendaftaran diterima berhasil dikonversi menjadi santri.']);

        return redirect()->route('pesantrian.students.show', $student->id);
    }

    public function update(UpdateStudentApiRequest $request, string $student): RedirectResponse
    {
        $updated = $this->updateStudent->execute(
            $request->user(),
            $student,
            $request->studentChanges(),
            $request->guardianChanges(),
            $this->responses->correlationId($request),
        );

        abort_if($updated === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Data santri berhasil diperbarui.']);

        return redirect()->route('pesantrian.students.show', $updated->id);
    }

    public function lifecycle(ChangeStudentLifecycleApiRequest $request, string $student): RedirectResponse
    {
        $updated = $this->changeStudentLifecycle->execute(
            $request->user(),
            $student,
            $request->status(),
            $request->reason(),
            $this->responses->correlationId($request),
        );

        abort_if($updated === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Status santri berhasil diperbarui.']);

        return redirect()->route('pesantrian.students.show', $updated->id);
    }

    public function archive(ArchiveStudentApiRequest $request, string $student): RedirectResponse
    {
        $archived = $this->archiveStudent->execute(
            $request->user(),
            $student,
            $request->reason(),
            $this->responses->correlationId($request),
        );

        abort_if($archived === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Data santri berhasil diarsipkan.']);

        return redirect()->route('pesantrian.students.index');
    }

    public function restore(ArchiveStudentApiRequest $request, string $student): RedirectResponse
    {
        $restored = $this->restoreStudent->execute(
            $request->user(),
            $student,
            $request->reason(),
            $this->responses->correlationId($request),
        );

        abort_if($restored === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Data santri berhasil dipulihkan.']);

        return redirect()->route('pesantrian.students.show', $restored->id);
    }

    /** @return array{currentPage: int, perPage: int, total: int, lastPage: int} */
    private function paginationMeta(PaginatedStudentData $result): array
    {
        return [
            'currentPage' => $result->currentPage,
            'perPage' => $result->perPage,
            'total' => $result->total,
            'lastPage' => $result->lastPage,
        ];
    }

    /** @return list<array{id: string, code: string, name: string}> */
    private function primaryUnitOptions(): array
    {
        $units = DB::table('organization_units')
            ->select(['id', 'code', 'name'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
        $options = [];

        foreach ($units as $unit) {
            $options[] = [
                'id' => (string) $unit->id,
                'code' => (string) $unit->code,
                'name' => (string) $unit->name,
            ];
        }

        return $options;
    }
}
