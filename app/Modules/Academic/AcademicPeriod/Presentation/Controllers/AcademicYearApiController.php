<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod\Presentation\Controllers;

use App\Http\ApiResponseFactory;
use App\Modules\Academic\AcademicPeriod\Application\Actions\CreateAcademicYear;
use App\Modules\Academic\AcademicPeriod\Application\Actions\UpdateAcademicYear;
use App\Modules\Academic\AcademicPeriod\Application\DTO\AcademicYearData;
use App\Modules\Academic\AcademicPeriod\Application\DTO\PaginatedAcademicYearData;
use App\Modules\Academic\AcademicPeriod\Application\Queries\ListAcademicYears;
use App\Modules\Academic\AcademicPeriod\Presentation\Requests\ListAcademicYearsApiRequest;
use App\Modules\Academic\AcademicPeriod\Presentation\Requests\StoreAcademicYearApiRequest;
use App\Modules\Academic\AcademicPeriod\Presentation\Requests\UpdateAcademicYearApiRequest;
use App\Modules\Academic\AcademicPeriod\Presentation\Resources\AcademicYearResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final readonly class AcademicYearApiController implements HasMiddleware
{
    public function __construct(
        private ListAcademicYears $listAcademicYears,
        private CreateAcademicYear $createAcademicYear,
        private UpdateAcademicYear $updateAcademicYear,
        private ApiResponseFactory $responses,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:academic_period.view', only: ['index']),
            new Middleware('can:academic_period.manage', only: ['store', 'update']),
        ];
    }

    public function index(ListAcademicYearsApiRequest $request): JsonResponse
    {
        $result = $this->listAcademicYears->execute($request->toFilter());

        return $this->responses->success(
            $request,
            'Daftar tahun akademik berhasil dibaca.',
            array_map(
                static fn (AcademicYearData $year): array => (new AcademicYearResource($year))->toArray($request),
                $result->data,
            ),
            $this->paginationMeta($result),
        );
    }

    public function store(StoreAcademicYearApiRequest $request): JsonResponse
    {
        $year = $this->createAcademicYear->execute(
            $request->user(),
            $request->toData(),
            $this->responses->correlationId($request),
        );

        return $this->responses->success(
            $request,
            'Tahun akademik berhasil dibuat.',
            (new AcademicYearResource($year))->toArray($request),
            status: 201,
        );
    }

    public function update(UpdateAcademicYearApiRequest $request, string $year): JsonResponse
    {
        $updated = $this->updateAcademicYear->execute(
            $request->user(),
            $year,
            $request->changes(),
            $this->responses->correlationId($request),
        );

        abort_if($updated === null, 404);

        return $this->responses->success(
            $request,
            'Tahun akademik berhasil diperbarui.',
            (new AcademicYearResource($updated))->toArray($request),
        );
    }

    /** @return array{current_page: int, per_page: int, total: int, last_page: int} */
    private function paginationMeta(PaginatedAcademicYearData $result): array
    {
        return [
            'current_page' => $result->currentPage,
            'per_page' => $result->perPage,
            'total' => $result->total,
            'last_page' => $result->lastPage,
        ];
    }
}
