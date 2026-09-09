<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Presentation\Controllers;

use App\Http\ApiResponseFactory;
use App\Modules\Pesantrian\PrestasiSantri\Application\Actions\ArchiveStudentAchievementCategory;
use App\Modules\Pesantrian\PrestasiSantri\Application\Actions\CreateStudentAchievementCategory;
use App\Modules\Pesantrian\PrestasiSantri\Application\Actions\CreateStudentAchievementDraft;
use App\Modules\Pesantrian\PrestasiSantri\Application\Actions\UpdateStudentAchievementCategory;
use App\Modules\Pesantrian\PrestasiSantri\Application\Actions\UpdateStudentAchievementDraft;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\PaginatedStudentAchievementData;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementCategoryData;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementData;
use App\Modules\Pesantrian\PrestasiSantri\Application\Exceptions\StudentAchievementMutationException;
use App\Modules\Pesantrian\PrestasiSantri\Application\Queries\ListStudentAchievementCategories;
use App\Modules\Pesantrian\PrestasiSantri\Application\Queries\ListStudentAchievements;
use App\Modules\Pesantrian\PrestasiSantri\Application\Queries\ShowStudentAchievement;
use App\Modules\Pesantrian\PrestasiSantri\Presentation\Requests\ArchiveStudentAchievementCategoryApiRequest;
use App\Modules\Pesantrian\PrestasiSantri\Presentation\Requests\ListStudentAchievementCategoriesApiRequest;
use App\Modules\Pesantrian\PrestasiSantri\Presentation\Requests\ListStudentAchievementsApiRequest;
use App\Modules\Pesantrian\PrestasiSantri\Presentation\Requests\StoreStudentAchievementApiRequest;
use App\Modules\Pesantrian\PrestasiSantri\Presentation\Requests\StoreStudentAchievementCategoryApiRequest;
use App\Modules\Pesantrian\PrestasiSantri\Presentation\Requests\UpdateStudentAchievementApiRequest;
use App\Modules\Pesantrian\PrestasiSantri\Presentation\Requests\UpdateStudentAchievementCategoryApiRequest;
use App\Modules\Pesantrian\PrestasiSantri\Presentation\Resources\StudentAchievementCategoryResource;
use App\Modules\Pesantrian\PrestasiSantri\Presentation\Resources\StudentAchievementResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final readonly class StudentAchievementApiController implements HasMiddleware
{
    public function __construct(
        private ListStudentAchievementCategories $listCategories,
        private ListStudentAchievements $listAchievements,
        private ShowStudentAchievement $showAchievement,
        private CreateStudentAchievementCategory $createCategory,
        private UpdateStudentAchievementCategory $updateCategory,
        private ArchiveStudentAchievementCategory $archiveCategory,
        private CreateStudentAchievementDraft $createDraft,
        private UpdateStudentAchievementDraft $updateDraft,
        private ApiResponseFactory $responses,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:prestasi_santri.view', only: ['categories', 'index', 'show']),
            new Middleware('can:prestasi_santri.manage', only: ['storeCategory', 'updateCategory']),
            new Middleware('can:prestasi_santri.record', only: ['store', 'update']),
            new Middleware('can:prestasi_santri.archive', only: ['archiveCategory']),
        ];
    }

    public function categories(ListStudentAchievementCategoriesApiRequest $request): JsonResponse
    {
        $categories = $this->listCategories->execute($request->toFilter());

        return $this->responses->success(
            $request,
            'Daftar kategori prestasi santri berhasil dibaca.',
            array_map(
                static fn (StudentAchievementCategoryData $category): array => (new StudentAchievementCategoryResource($category))->toArray($request),
                $categories,
            ),
        );
    }

    public function storeCategory(StoreStudentAchievementCategoryApiRequest $request): JsonResponse
    {
        $category = $this->createCategory->execute(
            $request->user(),
            $request->toData(),
            $this->responses->correlationId($request),
        );

        return $this->responses->success(
            $request,
            'Kategori prestasi santri berhasil dibuat.',
            (new StudentAchievementCategoryResource($category))->toArray($request),
            status: 201,
        );
    }

    public function updateCategory(UpdateStudentAchievementCategoryApiRequest $request, string $category): JsonResponse
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
            'Kategori prestasi santri berhasil diperbarui.',
            (new StudentAchievementCategoryResource($updated))->toArray($request),
        );
    }

    public function archiveCategory(ArchiveStudentAchievementCategoryApiRequest $request, string $category): JsonResponse
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
            'Kategori prestasi santri berhasil diarsipkan.',
            (new StudentAchievementCategoryResource($archived))->toArray($request),
        );
    }

    public function index(ListStudentAchievementsApiRequest $request): JsonResponse
    {
        $result = $this->listAchievements->execute($request->toFilter());

        return $this->responses->success(
            $request,
            'Daftar prestasi santri berhasil dibaca.',
            array_map(
                static fn (StudentAchievementData $achievement): array => (new StudentAchievementResource($achievement, includeRevisions: false))->toArray($request),
                $result->data,
            ),
            $this->paginationMeta($result),
        );
    }

    public function store(StoreStudentAchievementApiRequest $request): JsonResponse
    {
        try {
            $data = $this->createDraft->execute(
                $request->user(),
                $request->toData(),
                $this->responses->correlationId($request),
            );
        } catch (StudentAchievementMutationException $exception) {
            return $this->invalidMutation($request, $exception);
        }

        return $this->responses->success(
            $request,
            'Draft prestasi santri berhasil dibuat.',
            (new StudentAchievementResource($data))->toArray($request),
            status: 201,
        );
    }

    public function update(UpdateStudentAchievementApiRequest $request, string $achievement): JsonResponse
    {
        try {
            $data = $this->updateDraft->execute(
                $request->user(),
                $achievement,
                $request->toData(),
                $request->revisionReason(),
                $this->responses->correlationId($request),
            );
        } catch (StudentAchievementMutationException $exception) {
            return $this->invalidMutation($request, $exception);
        }

        abort_if($data === null, 404);

        return $this->responses->success(
            $request,
            'Draft prestasi santri berhasil diperbarui.',
            (new StudentAchievementResource($data))->toArray($request),
        );
    }

    public function show(Request $request, string $achievement): JsonResponse
    {
        $data = $this->showAchievement->execute($achievement);

        abort_if($data === null, 404);

        return $this->responses->success(
            $request,
            'Detail prestasi santri berhasil dibaca.',
            (new StudentAchievementResource($data))->toArray($request),
        );
    }

    /** @return array{current_page: int, per_page: int, total: int, last_page: int} */
    private function paginationMeta(PaginatedStudentAchievementData $result): array
    {
        return [
            'current_page' => $result->currentPage,
            'per_page' => $result->perPage,
            'total' => $result->total,
            'last_page' => $result->lastPage,
        ];
    }

    private function invalidMutation(Request $request, StudentAchievementMutationException $exception): JsonResponse
    {
        return $this->responses->error(
            $request,
            $exception->getMessage(),
            'PRESTASI_SANTRI_MUTATION_INVALID',
            422,
            $exception->errors(),
        );
    }
}
