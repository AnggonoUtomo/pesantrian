<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PenerimaanSantri\Presentation\Controllers;

use App\Http\ApiResponseFactory;
use App\Modules\Pesantrian\PenerimaanSantri\Application\Actions\CreateStudentAdmission;
use App\Modules\Pesantrian\PenerimaanSantri\Application\Actions\TransitionStudentAdmission;
use App\Modules\Pesantrian\PenerimaanSantri\Application\Actions\UpdateStudentAdmission;
use App\Modules\Pesantrian\PenerimaanSantri\Application\DTO\PaginatedStudentAdmissionData;
use App\Modules\Pesantrian\PenerimaanSantri\Application\DTO\StudentAdmissionData;
use App\Modules\Pesantrian\PenerimaanSantri\Application\Queries\ListStudentAdmissions;
use App\Modules\Pesantrian\PenerimaanSantri\Presentation\Requests\ListStudentAdmissionsApiRequest;
use App\Modules\Pesantrian\PenerimaanSantri\Presentation\Requests\StoreStudentAdmissionApiRequest;
use App\Modules\Pesantrian\PenerimaanSantri\Presentation\Requests\TransitionStudentAdmissionApiRequest;
use App\Modules\Pesantrian\PenerimaanSantri\Presentation\Requests\UpdateStudentAdmissionApiRequest;
use App\Modules\Pesantrian\PenerimaanSantri\Presentation\Resources\StudentAdmissionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final readonly class StudentAdmissionApiController implements HasMiddleware
{
    public function __construct(
        private ListStudentAdmissions $listStudentAdmissions,
        private CreateStudentAdmission $createStudentAdmission,
        private UpdateStudentAdmission $updateStudentAdmission,
        private TransitionStudentAdmission $transitionStudentAdmission,
        private ApiResponseFactory $responses,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:penerimaan_santri.view', only: ['index']),
            new Middleware('can:penerimaan_santri.manage', only: ['store', 'update']),
            new Middleware('can:penerimaan_santri.decide', only: ['verify', 'accept', 'reject', 'cancel']),
        ];
    }

    public function index(ListStudentAdmissionsApiRequest $request): JsonResponse
    {
        $result = $this->listStudentAdmissions->execute($request->toFilter());

        return $this->responses->success(
            $request,
            'Daftar pendaftaran santri berhasil dibaca.',
            array_map(
                static fn (StudentAdmissionData $admission): array => (new StudentAdmissionResource($admission))->toArray($request),
                $result->data,
            ),
            $this->paginationMeta($result),
        );
    }

    public function store(StoreStudentAdmissionApiRequest $request): JsonResponse
    {
        $admission = $this->createStudentAdmission->execute(
            $request->user(),
            $request->toData(),
            $this->responses->correlationId($request),
        );

        return $this->responses->success(
            $request,
            'Pendaftaran santri berhasil dibuat.',
            (new StudentAdmissionResource($admission))->toArray($request),
            status: 201,
        );
    }

    public function update(UpdateStudentAdmissionApiRequest $request, string $admission): JsonResponse
    {
        $updated = $this->updateStudentAdmission->execute(
            $request->user(),
            $admission,
            $request->changes(),
            $this->responses->correlationId($request),
        );

        abort_if($updated === null, 404);

        return $this->responses->success(
            $request,
            'Pendaftaran santri berhasil diperbarui.',
            (new StudentAdmissionResource($updated))->toArray($request),
        );
    }

    public function verify(TransitionStudentAdmissionApiRequest $request, string $admission): JsonResponse
    {
        return $this->transition($request, $admission, 'verified', 'Pendaftaran santri berhasil diverifikasi.');
    }

    public function accept(TransitionStudentAdmissionApiRequest $request, string $admission): JsonResponse
    {
        return $this->transition($request, $admission, 'accepted', 'Pendaftaran santri berhasil diterima.');
    }

    public function reject(TransitionStudentAdmissionApiRequest $request, string $admission): JsonResponse
    {
        return $this->transition($request, $admission, 'rejected', 'Pendaftaran santri berhasil ditolak.');
    }

    public function cancel(TransitionStudentAdmissionApiRequest $request, string $admission): JsonResponse
    {
        return $this->transition($request, $admission, 'cancelled', 'Pendaftaran santri berhasil dibatalkan.');
    }

    private function transition(
        TransitionStudentAdmissionApiRequest $request,
        string $admission,
        string $targetStatus,
        string $message,
    ): JsonResponse {
        $updated = $this->transitionStudentAdmission->execute(
            $request->user(),
            $admission,
            $targetStatus,
            $this->responses->correlationId($request),
        );

        abort_if($updated === null, 404);

        return $this->responses->success(
            $request,
            $message,
            (new StudentAdmissionResource($updated))->toArray($request),
        );
    }

    /** @return array{current_page: int, per_page: int, total: int, last_page: int} */
    private function paginationMeta(PaginatedStudentAdmissionData $result): array
    {
        return [
            'current_page' => $result->currentPage,
            'per_page' => $result->perPage,
            'total' => $result->total,
            'last_page' => $result->lastPage,
        ];
    }
}
