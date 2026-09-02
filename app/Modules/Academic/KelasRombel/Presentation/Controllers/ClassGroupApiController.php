<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Presentation\Controllers;

use App\Http\ApiResponseFactory;
use App\Modules\Academic\KelasRombel\Application\Actions\ArchiveClassGroup;
use App\Modules\Academic\KelasRombel\Application\Actions\CreateClassGroup;
use App\Modules\Academic\KelasRombel\Application\Actions\RestoreClassGroup;
use App\Modules\Academic\KelasRombel\Application\Actions\UpdateClassGroup;
use App\Modules\Academic\KelasRombel\Application\DTO\ClassGroupData;
use App\Modules\Academic\KelasRombel\Application\DTO\PaginatedClassGroupData;
use App\Modules\Academic\KelasRombel\Application\Queries\ListClassGroups;
use App\Modules\Academic\KelasRombel\Application\Queries\ShowClassGroup;
use App\Modules\Academic\KelasRombel\Presentation\Requests\ArchiveClassGroupApiRequest;
use App\Modules\Academic\KelasRombel\Presentation\Requests\ListClassGroupsApiRequest;
use App\Modules\Academic\KelasRombel\Presentation\Requests\StoreClassGroupApiRequest;
use App\Modules\Academic\KelasRombel\Presentation\Requests\UpdateClassGroupApiRequest;
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
        private CreateClassGroup $createClassGroup,
        private UpdateClassGroup $updateClassGroup,
        private ArchiveClassGroup $archiveClassGroup,
        private RestoreClassGroup $restoreClassGroup,
        private ApiResponseFactory $responses,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:kelas_rombel.view', only: ['index', 'show']),
            new Middleware('can:kelas_rombel.manage', only: ['store', 'update']),
            new Middleware('can:kelas_rombel.archive', only: ['archive', 'restore']),
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

    public function store(StoreClassGroupApiRequest $request): JsonResponse
    {
        $classGroup = $this->createClassGroup->execute(
            $request->user(),
            $request->toData(),
            $this->responses->correlationId($request),
        );

        return $this->responses->success(
            $request,
            'Rombel berhasil dibuat.',
            (new ClassGroupResource($classGroup))->toArray($request),
            status: 201,
        );
    }

    public function update(UpdateClassGroupApiRequest $request, string $classGroup): JsonResponse
    {
        $updated = $this->updateClassGroup->execute(
            $request->user(),
            $classGroup,
            $request->changes(),
            $this->responses->correlationId($request),
        );

        abort_if($updated === null, 404);

        return $this->responses->success(
            $request,
            'Rombel berhasil diperbarui.',
            (new ClassGroupResource($updated))->toArray($request),
        );
    }

    public function archive(ArchiveClassGroupApiRequest $request, string $classGroup): JsonResponse
    {
        $archived = $this->archiveClassGroup->execute(
            $request->user(),
            $classGroup,
            (string) $request->validated('reason'),
            $this->responses->correlationId($request),
        );

        abort_if($archived === null, 404);

        return $this->responses->success(
            $request,
            'Rombel berhasil diarsipkan.',
            (new ClassGroupResource($archived))->toArray($request),
        );
    }

    public function restore(Request $request, string $classGroup): JsonResponse
    {
        $restored = $this->restoreClassGroup->execute(
            $request->user(),
            $classGroup,
            $this->responses->correlationId($request),
        );

        abort_if($restored === null, 404);

        return $this->responses->success(
            $request,
            'Rombel berhasil dipulihkan.',
            (new ClassGroupResource($restored))->toArray($request),
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
