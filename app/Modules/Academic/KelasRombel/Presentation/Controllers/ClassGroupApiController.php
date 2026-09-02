<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Presentation\Controllers;

use App\Http\ApiResponseFactory;
use App\Modules\Academic\KelasRombel\Application\DTO\ClassGroupData;
use App\Modules\Academic\KelasRombel\Application\DTO\PaginatedClassGroupData;
use App\Modules\Academic\KelasRombel\Application\Queries\ListClassGroups;
use App\Modules\Academic\KelasRombel\Application\Queries\ShowClassGroup;
use App\Modules\Academic\KelasRombel\Presentation\Requests\ListClassGroupsApiRequest;
use App\Modules\Academic\KelasRombel\Presentation\Resources\ClassGroupResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final readonly class ClassGroupApiController implements HasMiddleware
{
    public function __construct(
        private ListClassGroups $listClassGroups,
        private ShowClassGroup $showClassGroup,
        private ApiResponseFactory $responses,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:kelas_rombel.view', only: ['index', 'show']),
        ];
    }

    public function index(ListClassGroupsApiRequest $request): JsonResponse
    {
        $result = $this->listClassGroups->execute($request->toFilter());

        return $this->responses->success(
            $request,
            'Daftar rombel berhasil dibaca.',
            array_map(
                static fn (ClassGroupData $classGroup): array => (new ClassGroupResource($classGroup))->toArray($request),
                $result->data,
            ),
            $this->paginationMeta($result),
        );
    }

    public function show(Request $request, string $classGroup): JsonResponse
    {
        $data = $this->showClassGroup->execute($classGroup);

        abort_if($data === null, 404);

        return $this->responses->success(
            $request,
            'Detail rombel berhasil dibaca.',
            (new ClassGroupResource($data, includeDetails: true))->toArray($request),
        );
    }

    /** @return array{current_page: int, per_page: int, total: int, last_page: int} */
    private function paginationMeta(PaginatedClassGroupData $result): array
    {
        return [
            'current_page' => $result->currentPage,
            'per_page' => $result->perPage,
            'total' => $result->total,
            'last_page' => $result->lastPage,
        ];
    }
}
