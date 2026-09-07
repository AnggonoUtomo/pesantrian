<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PerizinanSantri\Presentation\Controllers;

use App\Modules\Pesantrian\PerizinanSantri\Application\DTO\PaginatedStudentPermitData;
use App\Modules\Pesantrian\PerizinanSantri\Application\DTO\StudentPermitData;
use App\Modules\Pesantrian\PerizinanSantri\Application\Queries\ListStudentPermits;
use App\Modules\Pesantrian\PerizinanSantri\Application\Queries\ShowStudentPermit;
use App\Modules\Pesantrian\PerizinanSantri\Presentation\Requests\ListStudentPermitsApiRequest;
use App\Modules\Pesantrian\PerizinanSantri\Presentation\Resources\StudentPermitResource;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

final readonly class StudentPermitController implements HasMiddleware
{
    public function __construct(
        private ListStudentPermits $listStudentPermits,
        private ShowStudentPermit $showStudentPermit,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:perizinan_santri.view', only: ['index', 'show']),
        ];
    }

    public function index(ListStudentPermitsApiRequest $request): Response
    {
        $result = $this->listStudentPermits->execute($request->toFilter());

        return Inertia::render('Pesantrian/PerizinanSantri/pages/Index', [
            'permits' => [
                'data' => array_map(
                    static fn (StudentPermitData $permit): array => (new StudentPermitResource($permit, includeRevisions: false))->toArray($request),
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
                'permitTypes' => [
                    ['value' => 'leave', 'label' => 'Keluar pesantren'],
                    ['value' => 'home_visit', 'label' => 'Pulang ke rumah'],
                    ['value' => 'sick', 'label' => 'Sakit / klinik'],
                    ['value' => 'activity', 'label' => 'Kegiatan khusus'],
                ],
                'statuses' => [
                    ['value' => 'draft', 'label' => 'Draft'],
                    ['value' => 'submitted', 'label' => 'Menunggu review'],
                    ['value' => 'approved', 'label' => 'Disetujui'],
                    ['value' => 'rejected', 'label' => 'Ditolak'],
                    ['value' => 'checked_out', 'label' => 'Sedang izin'],
                    ['value' => 'returned', 'label' => 'Sudah kembali'],
                    ['value' => 'void', 'label' => 'Dibatalkan'],
                ],
            ],
            'canManage' => $request->user()?->can('perizinan_santri.manage') === true,
            'canApprove' => $request->user()?->can('perizinan_santri.approve') === true,
            'canCheckout' => $request->user()?->can('perizinan_santri.checkout') === true,
            'canReturn' => $request->user()?->can('perizinan_santri.return') === true,
            'canArchive' => $request->user()?->can('perizinan_santri.archive') === true,
        ]);
    }

    public function show(Request $request, string $permit): Response
    {
        $data = $this->showStudentPermit->execute($permit);

        abort_if($data === null, 404);

        return Inertia::render('Pesantrian/PerizinanSantri/pages/Show', [
            'permit' => (new StudentPermitResource($data))->toArray($request),
            'canManage' => $request->user()?->can('perizinan_santri.manage') === true,
            'canApprove' => $request->user()?->can('perizinan_santri.approve') === true,
            'canCheckout' => $request->user()?->can('perizinan_santri.checkout') === true,
            'canReturn' => $request->user()?->can('perizinan_santri.return') === true,
            'canArchive' => $request->user()?->can('perizinan_santri.archive') === true,
        ]);
    }

    /** @return array{currentPage: int, perPage: int, total: int, lastPage: int} */
    private function paginationMeta(PaginatedStudentPermitData $result): array
    {
        return [
            'currentPage' => $result->currentPage,
            'perPage' => $result->perPage,
            'total' => $result->total,
            'lastPage' => $result->lastPage,
        ];
    }
}
