<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PresensiSantri\Presentation\Controllers;

use App\Http\ApiResponseFactory;
use App\Modules\Pesantrian\PresensiSantri\Application\Actions\CreateStudentAttendance;
use App\Modules\Pesantrian\PresensiSantri\Application\Actions\ReviseStudentAttendance;
use App\Modules\Pesantrian\PresensiSantri\Application\Actions\SubmitStudentAttendance;
use App\Modules\Pesantrian\PresensiSantri\Application\Actions\UpdateStudentAttendance;
use App\Modules\Pesantrian\PresensiSantri\Application\Actions\UpdateStudentAttendanceEntries;
use App\Modules\Pesantrian\PresensiSantri\Application\Actions\VoidStudentAttendance;
use App\Modules\Pesantrian\PresensiSantri\Application\DTO\PaginatedStudentAttendanceData;
use App\Modules\Pesantrian\PresensiSantri\Application\DTO\StudentAttendanceData;
use App\Modules\Pesantrian\PresensiSantri\Application\Exceptions\StudentAttendanceMutationException;
use App\Modules\Pesantrian\PresensiSantri\Application\Queries\ListStudentAttendances;
use App\Modules\Pesantrian\PresensiSantri\Application\Queries\ShowStudentAttendance;
use App\Modules\Pesantrian\PresensiSantri\Presentation\Requests\ListStudentAttendancesApiRequest;
use App\Modules\Pesantrian\PresensiSantri\Presentation\Requests\StoreStudentAttendanceApiRequest;
use App\Modules\Pesantrian\PresensiSantri\Presentation\Requests\StudentAttendanceReasonApiRequest;
use App\Modules\Pesantrian\PresensiSantri\Presentation\Requests\UpdateStudentAttendanceApiRequest;
use App\Modules\Pesantrian\PresensiSantri\Presentation\Requests\UpdateStudentAttendanceEntriesApiRequest;
use App\Modules\Pesantrian\PresensiSantri\Presentation\Resources\StudentAttendanceResource;
use App\Modules\Pesantrian\Santri\Application\Contracts\ActiveStudentReader;
use App\Modules\Pesantrian\Santri\Application\DTO\ActiveStudentOptionData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

final readonly class StudentAttendanceController implements HasMiddleware
{
    public function __construct(
        private ListStudentAttendances $listStudentAttendances,
        private ShowStudentAttendance $showStudentAttendance,
        private CreateStudentAttendance $createStudentAttendance,
        private UpdateStudentAttendance $updateStudentAttendance,
        private UpdateStudentAttendanceEntries $updateStudentAttendanceEntries,
        private SubmitStudentAttendance $submitStudentAttendance,
        private ReviseStudentAttendance $reviseStudentAttendance,
        private VoidStudentAttendance $voidStudentAttendance,
        private ActiveStudentReader $students,
        private ApiResponseFactory $responses,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:presensi_santri.view', only: ['index', 'show']),
            new Middleware('can:presensi_santri.manage', only: ['store', 'update', 'updateEntries']),
            new Middleware('can:presensi_santri.submit', only: ['submit']),
            new Middleware('can:presensi_santri.revise', only: ['revise']),
            new Middleware('can:presensi_santri.archive', only: ['void']),
        ];
    }

    public function index(ListStudentAttendancesApiRequest $request): Response
    {
        $result = $this->listStudentAttendances->execute($request->toFilter());

        return Inertia::render('Pesantrian/PresensiSantri/pages/Index', [
            'attendances' => [
                'data' => array_map(
                    static fn (StudentAttendanceData $attendance): array => (new StudentAttendanceResource($attendance, includeEntries: false))->toArray($request),
                    $result->data,
                ),
                'meta' => $this->paginationMeta($result),
            ],
            'filters' => $request->safe()->only(['search', 'filter', 'page', 'per_page', 'sort']),
            'pagination' => [
                'perPageOptions' => [10, 25, 50, 100],
                'defaultPerPage' => 25,
            ],
            'options' => $this->options(),
            'canManage' => $request->user()?->can('presensi_santri.manage') === true,
            'canSubmit' => $request->user()?->can('presensi_santri.submit') === true,
            'canRevise' => $request->user()?->can('presensi_santri.revise') === true,
            'canArchive' => $request->user()?->can('presensi_santri.archive') === true,
        ]);
    }

    public function show(Request $request, string $attendance): Response
    {
        $data = $this->showStudentAttendance->execute($attendance);

        abort_if($data === null, 404);

        return Inertia::render('Pesantrian/PresensiSantri/pages/Show', [
            'attendance' => (new StudentAttendanceResource($data))->toArray($request),
            'options' => $this->options(),
            'canManage' => $request->user()?->can('presensi_santri.manage') === true,
            'canSubmit' => $request->user()?->can('presensi_santri.submit') === true,
            'canRevise' => $request->user()?->can('presensi_santri.revise') === true,
            'canArchive' => $request->user()?->can('presensi_santri.archive') === true,
        ]);
    }

    public function store(StoreStudentAttendanceApiRequest $request): RedirectResponse
    {
        try {
            $attendance = $this->createStudentAttendance->execute(
                $request->user(),
                $request->toData(),
                $request->entries(),
                $this->responses->correlationId($request),
            );
        } catch (StudentAttendanceMutationException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Sesi presensi santri berhasil dibuat.']);

        return to_route('pesantrian.student-attendances.show', $attendance->id);
    }

    public function update(UpdateStudentAttendanceApiRequest $request, string $attendance): RedirectResponse
    {
        try {
            $updated = $this->updateStudentAttendance->execute(
                $request->user(),
                $attendance,
                $request->toData(),
                $this->responses->correlationId($request),
            );
        } catch (StudentAttendanceMutationException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        abort_if($updated === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Sesi presensi santri berhasil diperbarui.']);

        return to_route('pesantrian.student-attendances.show', $attendance);
    }

    public function updateEntries(UpdateStudentAttendanceEntriesApiRequest $request, string $attendance): RedirectResponse
    {
        try {
            $updated = $this->updateStudentAttendanceEntries->execute(
                $request->user(),
                $attendance,
                $request->entries(),
                $this->responses->correlationId($request),
            );
        } catch (StudentAttendanceMutationException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        abort_if($updated === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Entry presensi santri berhasil diperbarui.']);

        return to_route('pesantrian.student-attendances.show', $attendance);
    }

    public function submit(Request $request, string $attendance): RedirectResponse
    {
        try {
            $updated = $this->submitStudentAttendance->execute(
                $request->user(),
                $attendance,
                $this->responses->correlationId($request),
            );
        } catch (StudentAttendanceMutationException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        abort_if($updated === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Sesi presensi santri berhasil disubmit.']);

        return to_route('pesantrian.student-attendances.show', $attendance);
    }

    public function revise(StudentAttendanceReasonApiRequest $request, string $attendance): RedirectResponse
    {
        try {
            $updated = $this->reviseStudentAttendance->execute(
                $request->user(),
                $attendance,
                (string) $request->validated('reason'),
                $this->responses->correlationId($request),
            );
        } catch (StudentAttendanceMutationException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        abort_if($updated === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Sesi presensi santri berhasil dibuka untuk revisi.']);

        return to_route('pesantrian.student-attendances.show', $attendance);
    }

    public function void(StudentAttendanceReasonApiRequest $request, string $attendance): RedirectResponse
    {
        try {
            $updated = $this->voidStudentAttendance->execute(
                $request->user(),
                $attendance,
                (string) $request->validated('reason'),
                $this->responses->correlationId($request),
            );
        } catch (StudentAttendanceMutationException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        abort_if($updated === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Sesi presensi santri berhasil dibatalkan.']);

        return to_route('pesantrian.student-attendances.index');
    }

    /** @return array{currentPage: int, perPage: int, total: int, lastPage: int} */
    private function paginationMeta(PaginatedStudentAttendanceData $result): array
    {
        return [
            'currentPage' => $result->currentPage,
            'perPage' => $result->perPage,
            'total' => $result->total,
            'lastPage' => $result->lastPage,
        ];
    }

    /** @return array{contexts: list<array{value: string, label: string}>, statuses: list<array{value: string, label: string}>, students: list<array{id: string, code: string, name: string}>} */
    private function options(): array
    {
        return [
            'contexts' => [
                ['value' => 'class_group', 'label' => 'Kelas / Rombel'],
                ['value' => 'dormitory', 'label' => 'Asrama'],
                ['value' => 'activity', 'label' => 'Kegiatan umum'],
            ],
            'statuses' => [
                ['value' => 'draft', 'label' => 'Draft'],
                ['value' => 'submitted', 'label' => 'Submit'],
                ['value' => 'revised', 'label' => 'Revisi'],
                ['value' => 'void', 'label' => 'Dibatalkan'],
            ],
            'students' => $this->studentOptions(),
        ];
    }

    /** @return list<array{id: string, code: string, name: string}> */
    private function studentOptions(): array
    {
        $options = [];

        foreach ($this->students->options(limit: 200) as $record) {
            /** @var ActiveStudentOptionData $record */
            $options[] = [
                'id' => $record->id,
                'code' => $record->studentNo,
                'name' => $record->fullName,
            ];
        }

        return $options;
    }
}
