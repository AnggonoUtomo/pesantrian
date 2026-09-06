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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final readonly class StudentAttendanceApiController implements HasMiddleware
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

    public function index(ListStudentAttendancesApiRequest $request): JsonResponse
    {
        $result = $this->listStudentAttendances->execute($request->toFilter());

        return $this->responses->success(
            $request,
            'Daftar presensi santri berhasil dibaca.',
            array_map(
                static fn (StudentAttendanceData $attendance): array => (new StudentAttendanceResource($attendance, includeEntries: false))->toArray($request),
                $result->data,
            ),
            $this->paginationMeta($result),
        );
    }

    public function show(Request $request, string $attendance): JsonResponse
    {
        $data = $this->showStudentAttendance->execute($attendance);

        abort_if($data === null, 404);

        return $this->responses->success(
            $request,
            'Detail presensi santri berhasil dibaca.',
            (new StudentAttendanceResource($data))->toArray($request),
        );
    }

    public function store(StoreStudentAttendanceApiRequest $request): JsonResponse
    {
        try {
            $data = $this->createStudentAttendance->execute(
                $request->user(),
                $request->toData(),
                $request->entries(),
                $this->responses->correlationId($request),
            );
        } catch (StudentAttendanceMutationException $exception) {
            return $this->invalidMutation($request, $exception);
        }

        return $this->responses->success(
            $request,
            'Sesi presensi santri berhasil dibuat.',
            (new StudentAttendanceResource($data))->toArray($request),
            status: 201,
        );
    }

    public function update(UpdateStudentAttendanceApiRequest $request, string $attendance): JsonResponse
    {
        try {
            $data = $this->updateStudentAttendance->execute(
                $request->user(),
                $attendance,
                $request->toData(),
                $this->responses->correlationId($request),
            );
        } catch (StudentAttendanceMutationException $exception) {
            return $this->invalidMutation($request, $exception);
        }

        abort_if($data === null, 404);

        return $this->responses->success(
            $request,
            'Sesi presensi santri berhasil diperbarui.',
            (new StudentAttendanceResource($data))->toArray($request),
        );
    }

    public function updateEntries(UpdateStudentAttendanceEntriesApiRequest $request, string $attendance): JsonResponse
    {
        try {
            $data = $this->updateStudentAttendanceEntries->execute(
                $request->user(),
                $attendance,
                $request->entries(),
                $this->responses->correlationId($request),
            );
        } catch (StudentAttendanceMutationException $exception) {
            return $this->invalidMutation($request, $exception);
        }

        abort_if($data === null, 404);

        return $this->responses->success(
            $request,
            'Entry presensi santri berhasil diperbarui.',
            (new StudentAttendanceResource($data))->toArray($request),
        );
    }

    public function submit(Request $request, string $attendance): JsonResponse
    {
        try {
            $data = $this->submitStudentAttendance->execute(
                $request->user(),
                $attendance,
                $this->responses->correlationId($request),
            );
        } catch (StudentAttendanceMutationException $exception) {
            return $this->invalidMutation($request, $exception);
        }

        abort_if($data === null, 404);

        return $this->responses->success(
            $request,
            'Sesi presensi santri berhasil disubmit.',
            (new StudentAttendanceResource($data))->toArray($request),
        );
    }

    public function revise(StudentAttendanceReasonApiRequest $request, string $attendance): JsonResponse
    {
        try {
            $data = $this->reviseStudentAttendance->execute(
                $request->user(),
                $attendance,
                (string) $request->validated('reason'),
                $this->responses->correlationId($request),
            );
        } catch (StudentAttendanceMutationException $exception) {
            return $this->invalidMutation($request, $exception);
        }

        abort_if($data === null, 404);

        return $this->responses->success(
            $request,
            'Sesi presensi santri berhasil dibuka untuk revisi.',
            (new StudentAttendanceResource($data))->toArray($request),
        );
    }

    public function void(StudentAttendanceReasonApiRequest $request, string $attendance): JsonResponse
    {
        try {
            $data = $this->voidStudentAttendance->execute(
                $request->user(),
                $attendance,
                (string) $request->validated('reason'),
                $this->responses->correlationId($request),
            );
        } catch (StudentAttendanceMutationException $exception) {
            return $this->invalidMutation($request, $exception);
        }

        abort_if($data === null, 404);

        return $this->responses->success(
            $request,
            'Sesi presensi santri berhasil dibatalkan.',
            (new StudentAttendanceResource($data))->toArray($request),
        );
    }

    /** @return array{current_page: int, per_page: int, total: int, last_page: int} */
    private function paginationMeta(PaginatedStudentAttendanceData $result): array
    {
        return [
            'current_page' => $result->currentPage,
            'per_page' => $result->perPage,
            'total' => $result->total,
            'last_page' => $result->lastPage,
        ];
    }

    private function invalidMutation(Request $request, StudentAttendanceMutationException $exception): JsonResponse
    {
        return $this->responses->error(
            $request,
            $exception->getMessage(),
            'PRESENSI_SANTRI_MUTATION_INVALID',
            422,
            $exception->errors(),
        );
    }
}
