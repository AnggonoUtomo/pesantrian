<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PenerimaanSantri\Presentation\Controllers;

use App\Http\ApiResponseFactory;
use App\Modules\Pesantrian\PenerimaanSantri\Application\Actions\CreateStudentAdmission;
use App\Modules\Pesantrian\PenerimaanSantri\Application\Actions\UpdateStudentAdmission;
use App\Modules\Pesantrian\PenerimaanSantri\Application\DTO\PaginatedStudentAdmissionData;
use App\Modules\Pesantrian\PenerimaanSantri\Application\DTO\StudentAdmissionData;
use App\Modules\Pesantrian\PenerimaanSantri\Application\Queries\ListStudentAdmissions;
use App\Modules\Pesantrian\PenerimaanSantri\Presentation\Requests\ListStudentAdmissionsApiRequest;
use App\Modules\Pesantrian\PenerimaanSantri\Presentation\Requests\StoreStudentAdmissionApiRequest;
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
        private ApiResponseFactory $responses,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:penerimaan_santri.view', only: ['index']),
            new Middleware('can:penerimaan_santri.manage', only: ['store', 'update']),
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
        $admission = $this->createStudentAdmission->execute($request->toData());

        return $this->responses->success(
            $request,
            'Pendaftaran santri berhasil dibuat.',
            (new StudentAdmissionResource($admission))->toArray($request),
            status: 201,
        );
    }

    public function update(UpdateStudentAdmissionApiRequest $request, string $admission): JsonResponse
    {
        $updated = $this->updateStudentAdmission->execute($admission, $request->changes());

        abort_if($updated === null, 404);

        return $this->responses->success(
            $request,
            'Pendaftaran santri berhasil diperbarui.',
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
