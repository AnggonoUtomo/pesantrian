<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PresensiSantri\Presentation\Controllers;

use App\Modules\Pesantrian\PresensiSantri\Application\DTO\PaginatedStudentAttendanceData;
use App\Modules\Pesantrian\PresensiSantri\Application\DTO\StudentAttendanceData;
use App\Modules\Pesantrian\PresensiSantri\Application\Queries\ListStudentAttendances;
use App\Modules\Pesantrian\PresensiSantri\Application\Queries\ShowStudentAttendance;
use App\Modules\Pesantrian\PresensiSantri\Presentation\Requests\ListStudentAttendancesApiRequest;
use App\Modules\Pesantrian\PresensiSantri\Presentation\Resources\StudentAttendanceResource;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

final readonly class StudentAttendanceController implements HasMiddleware
{
    public function __construct(
        private ListStudentAttendances $listStudentAttendances,
        private ShowStudentAttendance $showStudentAttendance,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:presensi_santri.view', only: ['index', 'show']),
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

    /** @return array{contexts: list<array{value: string, label: string}>, statuses: list<array{value: string, label: string}>} */
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
        ];
    }
}
