<?php

declare(strict_types=1);

namespace App\Modules\Organization\Organization\Presentation\Controllers;

use App\Modules\Organization\Organization\Application\DTO\OrganizationUnitData;
use App\Modules\Organization\Organization\Application\DTO\PaginatedOrganizationUnitData;
use App\Modules\Organization\Organization\Application\Queries\ListOrganizationUnits;
use App\Modules\Organization\Organization\Presentation\Requests\ListOrganizationUnitsApiRequest;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

final readonly class OrganizationUnitController implements HasMiddleware
{
    public function __construct(private ListOrganizationUnits $listOrganizationUnits) {}

    public static function middleware(): array
    {
        return [new Middleware('can:organization.view')];
    }

    public function index(ListOrganizationUnitsApiRequest $request): Response
    {
        $result = $this->listOrganizationUnits->execute($request->toFilter());

        return Inertia::render('Organization/Organization/pages/Index', [
            'units' => [
                'data' => array_map(
                    static fn (OrganizationUnitData $unit): array => $unit->toArray(),
                    $result->data,
                ),
                'meta' => $this->paginationMeta($result),
            ],
            'filters' => $request->safe()->only(['search', 'filter', 'page', 'per_page', 'sort']),
            'pagination' => [
                'perPageOptions' => [10, 25, 50, 100],
                'defaultPerPage' => 25,
            ],
        ]);
    }

    /** @return array{currentPage: int, perPage: int, total: int, lastPage: int} */
    private function paginationMeta(PaginatedOrganizationUnitData $result): array
    {
        return [
            'currentPage' => $result->currentPage,
            'perPage' => $result->perPage,
            'total' => $result->total,
            'lastPage' => $result->lastPage,
        ];
    }
}
