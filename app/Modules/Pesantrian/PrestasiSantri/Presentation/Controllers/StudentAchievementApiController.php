<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Presentation\Controllers;

use App\Http\ApiResponseFactory;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\PaginatedStudentAchievementData;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementCategoryData;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementData;
use App\Modules\Pesantrian\PrestasiSantri\Application\Queries\ListStudentAchievementCategories;
use App\Modules\Pesantrian\PrestasiSantri\Application\Queries\ListStudentAchievements;
use App\Modules\Pesantrian\PrestasiSantri\Application\Queries\ShowStudentAchievement;
use App\Modules\Pesantrian\PrestasiSantri\Presentation\Requests\ListStudentAchievementCategoriesApiRequest;
use App\Modules\Pesantrian\PrestasiSantri\Presentation\Requests\ListStudentAchievementsApiRequest;
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
        private ApiResponseFactory $responses,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:prestasi_santri.view', only: ['categories', 'index', 'show']),
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
}
