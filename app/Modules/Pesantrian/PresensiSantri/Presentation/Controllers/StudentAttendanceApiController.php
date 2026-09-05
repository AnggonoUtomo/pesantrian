<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PresensiSantri\Presentation\Controllers;

use App\Http\ApiResponseFactory;
use App\Modules\Pesantrian\PresensiSantri\Application\DTO\PaginatedStudentAttendanceData;
use App\Modules\Pesantrian\PresensiSantri\Application\DTO\StudentAttendanceData;
use App\Modules\Pesantrian\PresensiSantri\Application\Queries\ListStudentAttendances;
use App\Modules\Pesantrian\PresensiSantri\Application\Queries\ShowStudentAttendance;
use App\Modules\Pesantrian\PresensiSantri\Presentation\Requests\ListStudentAttendancesApiRequest;
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
        private ApiResponseFactory $responses,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:presensi_santri.view', only: ['index', 'show']),
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
}
