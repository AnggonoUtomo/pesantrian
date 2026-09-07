<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PerizinanSantri\Presentation\Controllers;

use App\Http\ApiResponseFactory;
use App\Modules\Pesantrian\PerizinanSantri\Application\DTO\PaginatedStudentPermitData;
use App\Modules\Pesantrian\PerizinanSantri\Application\DTO\StudentPermitData;
use App\Modules\Pesantrian\PerizinanSantri\Application\Queries\ListStudentPermits;
use App\Modules\Pesantrian\PerizinanSantri\Application\Queries\ShowStudentPermit;
use App\Modules\Pesantrian\PerizinanSantri\Presentation\Requests\ListStudentPermitsApiRequest;
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
        private ApiResponseFactory $responses,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:perizinan_santri.view', only: ['index', 'show']),
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
}
