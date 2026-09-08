<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Presentation\Controllers;

use App\Http\ApiResponseFactory;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Actions\ArchiveStudentDisciplineCategory;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Actions\CreateStudentDisciplineCaseDraft;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Actions\CreateStudentDisciplineCategory;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Actions\SubmitStudentDisciplineCaseDraft;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Actions\UpdateStudentDisciplineCaseDraft;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Actions\UpdateStudentDisciplineCategory;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\PaginatedStudentDisciplineCaseData;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCaseData;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCategoryData;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Exceptions\StudentDisciplineMutationException;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Queries\ListStudentDisciplineCases;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Queries\ListStudentDisciplineCategories;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Queries\ShowStudentDisciplineCase;
use App\Modules\Pesantrian\KedisiplinanSantri\Presentation\Requests\ArchiveStudentDisciplineCategoryApiRequest;
use App\Modules\Pesantrian\KedisiplinanSantri\Presentation\Requests\ListStudentDisciplineCasesApiRequest;
use App\Modules\Pesantrian\KedisiplinanSantri\Presentation\Requests\ListStudentDisciplineCategoriesApiRequest;
use App\Modules\Pesantrian\KedisiplinanSantri\Presentation\Requests\StoreStudentDisciplineCaseApiRequest;
use App\Modules\Pesantrian\KedisiplinanSantri\Presentation\Requests\StoreStudentDisciplineCategoryApiRequest;
use App\Modules\Pesantrian\KedisiplinanSantri\Presentation\Requests\UpdateStudentDisciplineCaseApiRequest;
use App\Modules\Pesantrian\KedisiplinanSantri\Presentation\Requests\UpdateStudentDisciplineCategoryApiRequest;
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
        private CreateStudentDisciplineCategory $createCategory,
        private UpdateStudentDisciplineCategory $updateCategory,
        private ArchiveStudentDisciplineCategory $archiveCategory,
        private CreateStudentDisciplineCaseDraft $createCaseDraft,
        private UpdateStudentDisciplineCaseDraft $updateCaseDraft,
        private SubmitStudentDisciplineCaseDraft $submitCaseDraft,
        private ApiResponseFactory $responses,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:kedisiplinan_santri.view', only: ['categories', 'index', 'show']),
            new Middleware('can:kedisiplinan_santri.manage', only: ['storeCategory', 'updateCategory', 'store', 'update', 'submit']),
            new Middleware('can:kedisiplinan_santri.archive', only: ['archiveCategory']),
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

    public function storeCategory(StoreStudentDisciplineCategoryApiRequest $request): JsonResponse
    {
        $category = $this->createCategory->execute(
            $request->user(),
            $request->toData(),
            $this->responses->correlationId($request),
        );

        return $this->responses->success(
            $request,
            'Kategori kedisiplinan santri berhasil dibuat.',
            (new StudentDisciplineCategoryResource($category))->toArray($request),
            status: 201,
        );
    }

    public function updateCategory(UpdateStudentDisciplineCategoryApiRequest $request, string $category): JsonResponse
    {
        $updated = $this->updateCategory->execute(
            $request->user(),
            $category,
            $request->changes(),
            $this->responses->correlationId($request),
        );

        abort_if($updated === null, 404);

        return $this->responses->success(
            $request,
            'Kategori kedisiplinan santri berhasil diperbarui.',
            (new StudentDisciplineCategoryResource($updated))->toArray($request),
        );
    }

    public function archiveCategory(ArchiveStudentDisciplineCategoryApiRequest $request, string $category): JsonResponse
    {
        $archived = $this->archiveCategory->execute(
            $request->user(),
            $category,
            $request->reason(),
            $this->responses->correlationId($request),
        );

        abort_if($archived === null, 404);

        return $this->responses->success(
            $request,
            'Kategori kedisiplinan santri berhasil diarsipkan.',
            (new StudentDisciplineCategoryResource($archived))->toArray($request),
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

    public function store(StoreStudentDisciplineCaseApiRequest $request): JsonResponse
    {
        try {
            $data = $this->createCaseDraft->execute(
                $request->user(),
                $request->toData(),
                $this->responses->correlationId($request),
            );
        } catch (StudentDisciplineMutationException $exception) {
            return $this->invalidMutation($request, $exception);
        }

        return $this->responses->success(
            $request,
            'Draft kasus kedisiplinan santri berhasil dibuat.',
            (new StudentDisciplineCaseResource($data))->toArray($request),
            status: 201,
        );
    }

    public function update(UpdateStudentDisciplineCaseApiRequest $request, string $case): JsonResponse
    {
        try {
            $data = $this->updateCaseDraft->execute(
                $request->user(),
                $case,
                $request->toData(),
                $request->revisionReason(),
                $this->responses->correlationId($request),
            );
        } catch (StudentDisciplineMutationException $exception) {
            return $this->invalidMutation($request, $exception);
        }

        abort_if($data === null, 404);

        return $this->responses->success(
            $request,
            'Draft kasus kedisiplinan santri berhasil diperbarui.',
            (new StudentDisciplineCaseResource($data))->toArray($request),
        );
    }

    public function submit(Request $request, string $case): JsonResponse
    {
        try {
            $data = $this->submitCaseDraft->execute(
                $request->user(),
                $case,
                $this->responses->correlationId($request),
            );
        } catch (StudentDisciplineMutationException $exception) {
            return $this->invalidMutation($request, $exception);
        }

        abort_if($data === null, 404);

        return $this->responses->success(
            $request,
            'Draft kasus kedisiplinan santri berhasil disubmit.',
            (new StudentDisciplineCaseResource($data))->toArray($request),
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

    private function invalidMutation(Request $request, StudentDisciplineMutationException $exception): JsonResponse
    {
        return $this->responses->error(
            $request,
            $exception->getMessage(),
            'KEDISIPLINAN_SANTRI_MUTATION_INVALID',
            422,
            $exception->errors(),
        );
    }
}
