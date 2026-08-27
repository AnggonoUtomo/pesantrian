<?php

declare(strict_types=1);

namespace App\Modules\Organization\Organization\Presentation\Controllers;

use App\Modules\Organization\Organization\Application\Actions\ArchiveOrganizationUnit;
use App\Modules\Organization\Organization\Application\Actions\CreateOrganizationUnit;
use App\Modules\Organization\Organization\Application\Actions\RestoreOrganizationUnit;
use App\Modules\Organization\Organization\Application\Actions\UpdateOrganizationUnit;
use App\Modules\Organization\Organization\Application\DTO\OrganizationUnitData;
use App\Modules\Organization\Organization\Application\DTO\OrganizationUnitParentOptionData;
use App\Modules\Organization\Organization\Application\DTO\PaginatedOrganizationUnitData;
use App\Modules\Organization\Organization\Application\Queries\ListOrganizationUnitParentOptions;
use App\Modules\Organization\Organization\Application\Queries\ListOrganizationUnits;
use App\Modules\Organization\Organization\Presentation\Requests\ListOrganizationUnitsApiRequest;
use App\Modules\Organization\Organization\Presentation\Requests\StoreOrganizationUnitApiRequest;
use App\Modules\Organization\Organization\Presentation\Requests\UpdateOrganizationUnitApiRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use InvalidArgumentException;

final readonly class OrganizationUnitController implements HasMiddleware
{
    public function __construct(
        private ListOrganizationUnits $listOrganizationUnits,
        private ListOrganizationUnitParentOptions $listOrganizationUnitParentOptions,
        private CreateOrganizationUnit $createOrganizationUnit,
        private UpdateOrganizationUnit $updateOrganizationUnit,
        private ArchiveOrganizationUnit $archiveOrganizationUnit,
        private RestoreOrganizationUnit $restoreOrganizationUnit,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:organization.view', only: ['index']),
            new Middleware('can:organization.manage', only: ['store', 'update', 'archive', 'restore']),
        ];
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
            'parentOptions' => $this->parentOptions(),
        ]);
    }

    public function store(StoreOrganizationUnitApiRequest $request): RedirectResponse
    {
        $this->createOrganizationUnit->execute($request->user(), $request->toData());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Unit organisasi berhasil dibuat.']);

        return back();
    }

    public function update(UpdateOrganizationUnitApiRequest $request, string $unit): RedirectResponse
    {
        $updated = $this->updateOrganizationUnit->execute($request->user(), $unit, $request->changes());

        abort_if($updated === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Unit organisasi berhasil diperbarui.']);

        return back();
    }

    public function archive(Request $request, string $unit): RedirectResponse
    {
        try {
            $archived = $this->archiveOrganizationUnit->execute($request->user(), $unit);
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['unit' => $exception->getMessage()]);
        }

        abort_if($archived === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Unit organisasi berhasil diarsipkan.']);

        return back();
    }

    public function restore(Request $request, string $unit): RedirectResponse
    {
        $restored = $this->restoreOrganizationUnit->execute($request->user(), $unit);

        abort_if($restored === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Unit organisasi berhasil diaktifkan kembali.']);

        return back();
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

    /** @return list<array{id: string, code: string, name: string}> */
    private function parentOptions(): array
    {
        return array_map(
            static fn (OrganizationUnitParentOptionData $unit): array => $unit->toArray(),
            $this->listOrganizationUnitParentOptions->execute(),
        );
    }
}
