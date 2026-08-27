<?php

declare(strict_types=1);

namespace App\Modules\Organization\Organization\Presentation\Controllers;

use App\Http\ApiResponseFactory;
use App\Modules\Organization\Organization\Application\Actions\CreateOrganizationUnit;
use App\Modules\Organization\Organization\Application\Actions\UpdateOrganizationUnit;
use App\Modules\Organization\Organization\Application\DTO\OrganizationUnitData;
use App\Modules\Organization\Organization\Application\DTO\PaginatedOrganizationUnitData;
use App\Modules\Organization\Organization\Application\Queries\ListOrganizationUnits;
use App\Modules\Organization\Organization\Presentation\Requests\ListOrganizationUnitsApiRequest;
use App\Modules\Organization\Organization\Presentation\Requests\StoreOrganizationUnitApiRequest;
use App\Modules\Organization\Organization\Presentation\Requests\UpdateOrganizationUnitApiRequest;
use App\Modules\Organization\Organization\Presentation\Resources\OrganizationUnitResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final readonly class OrganizationUnitApiController implements HasMiddleware
{
    public function __construct(
        private ListOrganizationUnits $listOrganizationUnits,
        private CreateOrganizationUnit $createOrganizationUnit,
        private UpdateOrganizationUnit $updateOrganizationUnit,
        private ApiResponseFactory $responses,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:organization.view', only: ['index']),
            new Middleware('can:organization.manage', only: ['store', 'update']),
        ];
    }

    public function index(ListOrganizationUnitsApiRequest $request): JsonResponse
    {
        $result = $this->listOrganizationUnits->execute($request->toFilter());

        return $this->responses->success(
            $request,
            'Daftar unit organisasi berhasil dibaca.',
            array_map(
                static fn (OrganizationUnitData $unit): array => (new OrganizationUnitResource($unit))->toArray($request),
                $result->data,
            ),
            $this->paginationMeta($result),
        );
    }

    public function store(StoreOrganizationUnitApiRequest $request): JsonResponse
    {
        $unit = $this->createOrganizationUnit->execute(
            $request->user(),
            $request->toData(),
            $this->responses->correlationId($request),
        );

        return $this->responses->success(
            $request,
            'Unit organisasi berhasil dibuat.',
            (new OrganizationUnitResource($unit))->toArray($request),
            status: 201,
        );
    }

    public function update(UpdateOrganizationUnitApiRequest $request, string $unit): JsonResponse
    {
        $updated = $this->updateOrganizationUnit->execute(
            $request->user(),
            $unit,
            $request->changes(),
            $this->responses->correlationId($request),
        );

        abort_if($updated === null, 404);

        return $this->responses->success(
            $request,
            'Unit organisasi berhasil diperbarui.',
            (new OrganizationUnitResource($updated))->toArray($request),
        );
    }

    /** @return array{current_page: int, per_page: int, total: int, last_page: int} */
    private function paginationMeta(PaginatedOrganizationUnitData $result): array
    {
        return [
            'current_page' => $result->currentPage,
            'per_page' => $result->perPage,
            'total' => $result->total,
            'last_page' => $result->lastPage,
        ];
    }
}
