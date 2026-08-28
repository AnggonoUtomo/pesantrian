<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod\Presentation\Controllers;

use App\Http\ApiResponseFactory;
use App\Modules\Academic\AcademicPeriod\Application\Actions\CreateAcademicTerm;
use App\Modules\Academic\AcademicPeriod\Application\Actions\UpdateAcademicTerm;
use App\Modules\Academic\AcademicPeriod\Application\DTO\AcademicTermData;
use App\Modules\Academic\AcademicPeriod\Application\DTO\PaginatedAcademicTermData;
use App\Modules\Academic\AcademicPeriod\Application\Queries\ListAcademicTerms;
use App\Modules\Academic\AcademicPeriod\Presentation\Requests\ListAcademicTermsApiRequest;
use App\Modules\Academic\AcademicPeriod\Presentation\Requests\StoreAcademicTermApiRequest;
use App\Modules\Academic\AcademicPeriod\Presentation\Requests\UpdateAcademicTermApiRequest;
use App\Modules\Academic\AcademicPeriod\Presentation\Resources\AcademicTermResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final readonly class AcademicTermApiController implements HasMiddleware
{
    public function __construct(
        private ListAcademicTerms $listAcademicTerms,
        private CreateAcademicTerm $createAcademicTerm,
        private UpdateAcademicTerm $updateAcademicTerm,
        private ApiResponseFactory $responses,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:academic_period.view', only: ['index']),
            new Middleware('can:academic_period.manage', only: ['store', 'update']),
        ];
    }

    public function index(ListAcademicTermsApiRequest $request): JsonResponse
    {
        $result = $this->listAcademicTerms->execute($request->toFilter());

        return $this->responses->success(
            $request,
            'Daftar term akademik berhasil dibaca.',
            array_map(
                static fn (AcademicTermData $term): array => (new AcademicTermResource($term))->toArray($request),
                $result->data,
            ),
            $this->paginationMeta($result),
        );
    }

    public function store(StoreAcademicTermApiRequest $request): JsonResponse
    {
        $term = $this->createAcademicTerm->execute($request->toData());

        return $this->responses->success(
            $request,
            'Term akademik berhasil dibuat.',
            (new AcademicTermResource($term))->toArray($request),
            status: 201,
        );
    }

    public function update(UpdateAcademicTermApiRequest $request, string $term): JsonResponse
    {
        $updated = $this->updateAcademicTerm->execute($term, $request->changes());

        abort_if($updated === null, 404);

        return $this->responses->success(
            $request,
            'Term akademik berhasil diperbarui.',
            (new AcademicTermResource($updated))->toArray($request),
        );
    }

    /** @return array{current_page: int, per_page: int, total: int, last_page: int} */
    private function paginationMeta(PaginatedAcademicTermData $result): array
    {
        return [
            'current_page' => $result->currentPage,
            'per_page' => $result->perPage,
            'total' => $result->total,
            'last_page' => $result->lastPage,
        ];
    }
}
