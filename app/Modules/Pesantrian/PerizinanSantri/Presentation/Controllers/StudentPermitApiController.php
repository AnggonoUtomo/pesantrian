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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final readonly class StudentPermitApiController implements HasMiddleware
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

    public function index(ListStudentPermitsApiRequest $request): JsonResponse
    {
        $result = $this->listStudentPermits->execute($request->toFilter());

        return $this->responses->success(
            $request,
            'Daftar perizinan santri berhasil dibaca.',
            array_map(
                static fn (StudentPermitData $permit): array => (new StudentPermitResource($permit, includeRevisions: false))->toArray($request),
                $result->data,
            ),
            $this->paginationMeta($result),
        );
    }

    public function show(Request $request, string $permit): JsonResponse
    {
        $data = $this->showStudentPermit->execute($permit);

        abort_if($data === null, 404);

        return $this->responses->success(
            $request,
            'Detail perizinan santri berhasil dibaca.',
            (new StudentPermitResource($data))->toArray($request),
        );
    }

    public function store(StoreStudentPermitApiRequest $request): JsonResponse
    {
        try {
            $data = $this->createStudentPermitDraft->execute(
                $request->user(),
                $request->toData(),
                $this->responses->correlationId($request),
            );
        } catch (StudentPermitMutationException $exception) {
            return $this->invalidMutation($request, $exception);
        }

        return $this->responses->success(
            $request,
            'Permohonan izin santri berhasil dibuat.',
            (new StudentPermitResource($data))->toArray($request),
            status: 201,
        );
    }

    public function update(UpdateStudentPermitApiRequest $request, string $permit): JsonResponse
    {
        try {
            $data = $this->updateStudentPermitDraft->execute(
                $request->user(),
                $permit,
                $request->toData(),
                $request->revisionReason(),
                $this->responses->correlationId($request),
            );
        } catch (StudentPermitMutationException $exception) {
            return $this->invalidMutation($request, $exception);
        }

        abort_if($data === null, 404);

        return $this->responses->success(
            $request,
            'Permohonan izin santri berhasil diperbarui.',
            (new StudentPermitResource($data))->toArray($request),
        );
    }

    public function submit(Request $request, string $permit): JsonResponse
    {
        try {
            $data = $this->submitStudentPermitDraft->execute(
                $request->user(),
                $permit,
                $this->responses->correlationId($request),
            );
        } catch (StudentPermitMutationException $exception) {
            return $this->invalidMutation($request, $exception);
        }

        abort_if($data === null, 404);

        return $this->responses->success(
            $request,
            'Permohonan izin santri berhasil disubmit.',
            (new StudentPermitResource($data))->toArray($request),
        );
    }

    public function approve(ApproveStudentPermitApiRequest $request, string $permit): JsonResponse
    {
        try {
            $data = $this->approveStudentPermit->execute(
                $request->user(),
                $permit,
                $request->reviewNote(),
                $this->responses->correlationId($request),
            );
        } catch (StudentPermitMutationException $exception) {
            return $this->invalidMutation($request, $exception);
        }

        abort_if($data === null, 404);

        return $this->responses->success(
            $request,
            'Permohonan izin santri berhasil disetujui.',
            (new StudentPermitResource($data))->toArray($request),
        );
    }

    public function reject(RejectStudentPermitApiRequest $request, string $permit): JsonResponse
    {
        try {
            $data = $this->rejectStudentPermit->execute(
                $request->user(),
                $permit,
                $request->reason(),
                $this->responses->correlationId($request),
            );
        } catch (StudentPermitMutationException $exception) {
            return $this->invalidMutation($request, $exception);
        }

        abort_if($data === null, 404);

        return $this->responses->success(
            $request,
            'Permohonan izin santri berhasil ditolak.',
            (new StudentPermitResource($data))->toArray($request),
        );
    }

    public function checkout(Request $request, string $permit): JsonResponse
    {
        try {
            $data = $this->checkoutStudentPermit->execute(
                $request->user(),
                $permit,
                $this->responses->correlationId($request),
            );
        } catch (StudentPermitMutationException $exception) {
            return $this->invalidMutation($request, $exception);
        }

        abort_if($data === null, 404);

        return $this->responses->success(
            $request,
            'Izin santri berhasil di-check-out.',
            (new StudentPermitResource($data))->toArray($request),
        );
    }

    public function returnPermit(ReturnStudentPermitApiRequest $request, string $permit): JsonResponse
    {
        try {
            $data = $this->returnStudentPermit->execute(
                $request->user(),
                $permit,
                $request->returnedAt(),
                $request->returnNote(),
                $this->responses->correlationId($request),
            );
        } catch (StudentPermitMutationException $exception) {
            return $this->invalidMutation($request, $exception);
        }

        abort_if($data === null, 404);

        return $this->responses->success(
            $request,
            'Kepulangan santri berhasil dicatat.',
            (new StudentPermitResource($data))->toArray($request),
        );
    }

    public function void(VoidStudentPermitApiRequest $request, string $permit): JsonResponse
    {
        try {
            $data = $this->voidStudentPermit->execute(
                $request->user(),
                $permit,
                $request->reason(),
                $this->responses->correlationId($request),
            );
        } catch (StudentPermitMutationException $exception) {
            return $this->invalidMutation($request, $exception);
        }

        abort_if($data === null, 404);

        return $this->responses->success(
            $request,
            'Izin santri berhasil dibatalkan.',
            (new StudentPermitResource($data))->toArray($request),
        );
    }

    /** @return array{current_page: int, per_page: int, total: int, last_page: int} */
    private function paginationMeta(PaginatedStudentPermitData $result): array
    {
        return [
            'current_page' => $result->currentPage,
            'per_page' => $result->perPage,
            'total' => $result->total,
            'last_page' => $result->lastPage,
        ];
    }

    private function invalidMutation(Request $request, StudentPermitMutationException $exception): JsonResponse
    {
        return $this->responses->error(
            $request,
            $exception->getMessage(),
            'PERIZINAN_SANTRI_MUTATION_INVALID',
            422,
            $exception->errors(),
        );
    }
}
