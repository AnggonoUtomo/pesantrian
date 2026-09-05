<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Presentation\Controllers;

use App\Modules\Pesantrian\Asrama\Application\DTO\DormitoryData;
use App\Modules\Pesantrian\Asrama\Application\DTO\PaginatedDormitoryData;
use App\Modules\Pesantrian\Asrama\Application\Queries\ListDormitories;
use App\Modules\Pesantrian\Asrama\Application\Queries\ShowDormitory;
use App\Modules\Pesantrian\Asrama\Presentation\Requests\ListDormitoriesApiRequest;
use App\Modules\Pesantrian\Asrama\Presentation\Resources\DormitoryResource;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

final readonly class DormitoryController implements HasMiddleware
{
    public function __construct(
        private ListDormitories $listDormitories,
        private ShowDormitory $showDormitory,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:asrama.view', only: ['index', 'show']),
        ];
    }

    public function index(ListDormitoriesApiRequest $request): Response
    {
        $result = $this->listDormitories->execute($request->toFilter());

        return Inertia::render('Pesantrian/Asrama/pages/Index', [
            'dormitories' => [
                'data' => array_map(
                    static fn (DormitoryData $dormitory): array => (new DormitoryResource($dormitory))->toArray($request),
                    $result->data,
                ),
                'meta' => $this->paginationMeta($result),
            ],
            'filters' => $request->safe()->only(['search', 'filter', 'page', 'per_page', 'sort']),
            'pagination' => [
                'perPageOptions' => [10, 25, 50, 100],
                'defaultPerPage' => 25,
            ],
            'options' => [
                'units' => $this->unitOptions(),
            ],
            'canManage' => $request->user()?->can('asrama.manage') === true,
            'canPlacement' => $request->user()?->can('asrama.placement') === true,
            'canSupervisor' => $request->user()?->can('asrama.supervisor') === true,
            'canArchive' => $request->user()?->can('asrama.archive') === true,
        ]);
    }

    public function show(Request $request, string $dormitory): Response
    {
        $data = $this->showDormitory->execute($dormitory);

        abort_if($data === null, 404);

        return Inertia::render('Pesantrian/Asrama/pages/Show', [
            'dormitory' => (new DormitoryResource($data, includeDetails: true))->toArray($request),
            'canManage' => $request->user()?->can('asrama.manage') === true,
            'canPlacement' => $request->user()?->can('asrama.placement') === true,
            'canSupervisor' => $request->user()?->can('asrama.supervisor') === true,
            'canArchive' => $request->user()?->can('asrama.archive') === true,
        ]);
    }

    /** @return array{currentPage: int, perPage: int, total: int, lastPage: int} */
    private function paginationMeta(PaginatedDormitoryData $result): array
    {
        return [
            'currentPage' => $result->currentPage,
            'perPage' => $result->perPage,
            'total' => $result->total,
            'lastPage' => $result->lastPage,
        ];
    }

    /**
     * @return list<array{id: string, code: string, name: string}>
     */
    private function unitOptions(): array
    {
        $records = DB::table('organization_units')
            ->select(['id', 'code', 'name'])
            ->where('type', 'dormitory')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $options = [];

        foreach ($records as $record) {
            $options[] = [
                'id' => (string) $record->id,
                'code' => (string) $record->code,
                'name' => (string) $record->name,
            ];
        }

        return $options;
    }
}
