<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Presentation\Controllers;

use App\Http\ApiResponseFactory;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\PaginatedStudentDisciplineCaseData;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCaseData;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCategoryData;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Queries\ListStudentDisciplineCases;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Queries\ListStudentDisciplineCategories;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Queries\ShowStudentDisciplineCase;
use App\Modules\Pesantrian\KedisiplinanSantri\Presentation\Requests\ListStudentDisciplineCasesApiRequest;
use App\Modules\Pesantrian\KedisiplinanSantri\Presentation\Requests\ListStudentDisciplineCategoriesApiRequest;
use App\Modules\Pesantrian\KedisiplinanSantri\Presentation\Resources\StudentDisciplineCaseResource;
use App\Modules\Pesantrian\KedisiplinanSantri\Presentation\Resources\StudentDisciplineCategoryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final readonly class StudentDisciplineApiController implements HasMiddleware
{
    public function __construct(
        private ListStudentDisciplineCategories $listCategories,
        private ListStudentDisciplineCases $listCases,
        private ShowStudentDisciplineCase $showCase,
        private ApiResponseFactory $responses,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:kedisiplinan_santri.view', only: ['categories', 'index', 'show']),
        ];
    }

    public function categories(ListStudentDisciplineCategoriesApiRequest $request): JsonResponse
    {
        $categories = $this->listCategories->execute($request->toFilter());

        return $this->responses->success(
            $request,
            'Daftar kategori kedisiplinan santri berhasil dibaca.',
            array_map(
                static fn (StudentDisciplineCategoryData $category): array => (new StudentDisciplineCategoryResource($category))->toArray($request),
                $categories,
            ),
        );
    }

    public function index(ListStudentDisciplineCasesApiRequest $request): JsonResponse
    {
        $result = $this->listCases->execute($request->toFilter());

        return $this->responses->success(
            $request,
            'Daftar kasus kedisiplinan santri berhasil dibaca.',
            array_map(
                static fn (StudentDisciplineCaseData $case): array => (new StudentDisciplineCaseResource($case, includeRevisions: false))->toArray($request),
                $result->data,
            ),
            $this->paginationMeta($result),
        );
    }

    public function show(Request $request, string $case): JsonResponse
    {
        $data = $this->showCase->execute($case);

        abort_if($data === null, 404);

        return $this->responses->success(
            $request,
            'Detail kasus kedisiplinan santri berhasil dibaca.',
            (new StudentDisciplineCaseResource($data))->toArray($request),
        );
    }

    /** @return array{current_page: int, per_page: int, total: int, last_page: int} */
    private function paginationMeta(PaginatedStudentDisciplineCaseData $result): array
    {
        return [
            'current_page' => $result->currentPage,
            'per_page' => $result->perPage,
            'total' => $result->total,
            'last_page' => $result->lastPage,
        ];
    }
}
