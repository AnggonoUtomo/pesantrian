<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PerizinanSantri\Presentation\Controllers;

use App\Http\ApiResponseFactory;
use App\Modules\Pesantrian\PerizinanSantri\Application\Actions\ApproveStudentPermit;
use App\Modules\Pesantrian\PerizinanSantri\Application\Actions\CheckoutStudentPermit;
use App\Modules\Pesantrian\PerizinanSantri\Application\Actions\CreateStudentPermitDraft;
use App\Modules\Pesantrian\PerizinanSantri\Application\Actions\RejectStudentPermit;
use App\Modules\Pesantrian\PerizinanSantri\Application\Actions\ReturnStudentPermit;
use App\Modules\Pesantrian\PerizinanSantri\Application\Actions\SubmitStudentPermitDraft;
use App\Modules\Pesantrian\PerizinanSantri\Application\Actions\UpdateStudentPermitDraft;
use App\Modules\Pesantrian\PerizinanSantri\Application\Actions\VoidStudentPermit;
use App\Modules\Pesantrian\PerizinanSantri\Application\DTO\PaginatedStudentPermitData;
use App\Modules\Pesantrian\PerizinanSantri\Application\DTO\StudentPermitData;
use App\Modules\Pesantrian\PerizinanSantri\Application\Exceptions\StudentPermitMutationException;
use App\Modules\Pesantrian\PerizinanSantri\Application\Queries\ListStudentPermits;
use App\Modules\Pesantrian\PerizinanSantri\Application\Queries\ShowStudentPermit;
use App\Modules\Pesantrian\PerizinanSantri\Presentation\Requests\ApproveStudentPermitApiRequest;
use App\Modules\Pesantrian\PerizinanSantri\Presentation\Requests\ListStudentPermitsApiRequest;
use App\Modules\Pesantrian\PerizinanSantri\Presentation\Requests\RejectStudentPermitApiRequest;
use App\Modules\Pesantrian\PerizinanSantri\Presentation\Requests\ReturnStudentPermitApiRequest;
use App\Modules\Pesantrian\PerizinanSantri\Presentation\Requests\StoreStudentPermitApiRequest;
use App\Modules\Pesantrian\PerizinanSantri\Presentation\Requests\UpdateStudentPermitApiRequest;
use App\Modules\Pesantrian\PerizinanSantri\Presentation\Requests\VoidStudentPermitApiRequest;
use App\Modules\Pesantrian\PerizinanSantri\Presentation\Resources\StudentPermitResource;
use App\Modules\Pesantrian\Santri\Application\Contracts\ActiveStudentReader;
use App\Modules\Pesantrian\Santri\Application\DTO\ActiveStudentOptionData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

final readonly class StudentPermitController implements HasMiddleware
{
    public function __construct(
        private ListStudentPermits $listStudentPermits,
        private ShowStudentPermit $showStudentPermit,
        private CreateStudentPermitDraft $createStudentPermitDraft,
        private UpdateStudentPermitDraft $updateStudentPermitDraft,
        private SubmitStudentPermitDraft $submitStudentPermitDraft,
        private ApproveStudentPermit $approveStudentPermit,
        private RejectStudentPermit $rejectStudentPermit,
        private CheckoutStudentPermit $checkoutStudentPermit,
        private ReturnStudentPermit $returnStudentPermit,
        private VoidStudentPermit $voidStudentPermit,
        private ActiveStudentReader $students,
        private ApiResponseFactory $responses,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:perizinan_santri.view', only: ['index', 'show']),
            new Middleware('can:perizinan_santri.manage', only: ['store', 'update', 'submit']),
            new Middleware('can:perizinan_santri.approve', only: ['approve', 'reject']),
            new Middleware('can:perizinan_santri.checkout', only: ['checkout']),
            new Middleware('can:perizinan_santri.return', only: ['returnPermit']),
            new Middleware('can:perizinan_santri.archive', only: ['void']),
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
            'options' => $this->options(),
            'canManage' => $request->user()?->can('perizinan_santri.manage') === true,
            'canApprove' => $request->user()?->can('perizinan_santri.approve') === true,
            'canCheckout' => $request->user()?->can('perizinan_santri.checkout') === true,
            'canReturn' => $request->user()?->can('perizinan_santri.return') === true,
            'canArchive' => $request->user()?->can('perizinan_santri.archive') === true,
        ]);
    }

    public function store(StoreStudentPermitApiRequest $request): RedirectResponse
    {
        try {
            $permit = $this->createStudentPermitDraft->execute(
                $request->user(),
                $request->toData(),
                $this->responses->correlationId($request),
            );
        } catch (StudentPermitMutationException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Permohonan izin santri berhasil dibuat.']);

        return to_route('pesantrian.student-permits.show', $permit->id);
    }

    public function update(UpdateStudentPermitApiRequest $request, string $permit): RedirectResponse
    {
        try {
            $updated = $this->updateStudentPermitDraft->execute(
                $request->user(),
                $permit,
                $request->toData(),
                $request->revisionReason(),
                $this->responses->correlationId($request),
            );
        } catch (StudentPermitMutationException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        abort_if($updated === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Permohonan izin santri berhasil diperbarui.']);

        return to_route('pesantrian.student-permits.show', $updated->id);
    }

    public function submit(Request $request, string $permit): RedirectResponse
    {
        try {
            $updated = $this->submitStudentPermitDraft->execute(
                $request->user(),
                $permit,
                $this->responses->correlationId($request),
            );
        } catch (StudentPermitMutationException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        abort_if($updated === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Permohonan izin santri berhasil disubmit.']);

        return to_route('pesantrian.student-permits.show', $updated->id);
    }

    public function approve(ApproveStudentPermitApiRequest $request, string $permit): RedirectResponse
    {
        try {
            $updated = $this->approveStudentPermit->execute(
                $request->user(),
                $permit,
                $request->reviewNote(),
                $this->responses->correlationId($request),
            );
        } catch (StudentPermitMutationException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        abort_if($updated === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Permohonan izin santri berhasil disetujui.']);

        return to_route('pesantrian.student-permits.show', $updated->id);
    }

    public function reject(RejectStudentPermitApiRequest $request, string $permit): RedirectResponse
    {
        try {
            $updated = $this->rejectStudentPermit->execute(
                $request->user(),
                $permit,
                $request->reason(),
                $this->responses->correlationId($request),
            );
        } catch (StudentPermitMutationException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        abort_if($updated === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Permohonan izin santri berhasil ditolak.']);

        return to_route('pesantrian.student-permits.show', $updated->id);
    }

    public function checkout(Request $request, string $permit): RedirectResponse
    {
        try {
            $updated = $this->checkoutStudentPermit->execute(
                $request->user(),
                $permit,
                $this->responses->correlationId($request),
            );
        } catch (StudentPermitMutationException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        abort_if($updated === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Izin santri berhasil di-check-out.']);

        return to_route('pesantrian.student-permits.show', $updated->id);
    }

    public function returnPermit(ReturnStudentPermitApiRequest $request, string $permit): RedirectResponse
    {
        try {
            $updated = $this->returnStudentPermit->execute(
                $request->user(),
                $permit,
                $request->returnedAt(),
                $request->returnNote(),
                $this->responses->correlationId($request),
            );
        } catch (StudentPermitMutationException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        abort_if($updated === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Kepulangan santri berhasil dicatat.']);

        return to_route('pesantrian.student-permits.show', $updated->id);
    }

    public function void(VoidStudentPermitApiRequest $request, string $permit): RedirectResponse
    {
        try {
            $updated = $this->voidStudentPermit->execute(
                $request->user(),
                $permit,
                $request->reason(),
                $this->responses->correlationId($request),
            );
        } catch (StudentPermitMutationException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        abort_if($updated === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Izin santri berhasil dibatalkan.']);

        return to_route('pesantrian.student-permits.show', $updated->id);
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
            'options' => $this->options(),
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

    /** @return array{students: list<array{value: string, label: string}>, permitTypes: list<array{value: string, label: string}>, statuses: list<array{value: string, label: string}>} */
    private function options(): array
    {
        return [
            'students' => array_map(
                static fn (ActiveStudentOptionData $student): array => [
                    'value' => $student->id,
                    'label' => $student->fullName.' ('.$student->studentNo.')',
                ],
                $this->students->options(limit: 200),
            ),
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
        ];
    }
}
