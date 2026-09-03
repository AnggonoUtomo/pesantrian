<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Presentation\Controllers;

use App\Modules\Academic\KelasRombel\Application\DTO\ClassGroupData;
use App\Modules\Academic\KelasRombel\Application\DTO\PaginatedClassGroupData;
use App\Modules\Academic\KelasRombel\Application\Queries\ListClassGroups;
use App\Modules\Academic\KelasRombel\Application\Queries\ShowClassGroup;
use App\Modules\Academic\KelasRombel\Presentation\Requests\ListClassGroupsApiRequest;
use App\Modules\Academic\KelasRombel\Presentation\Resources\ClassGroupResource;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

final readonly class ClassGroupController implements HasMiddleware
{
    public function __construct(
        private ListClassGroups $listClassGroups,
        private ShowClassGroup $showClassGroup,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:kelas_rombel.view', only: ['index', 'show']),
        ];
    }

    public function index(ListClassGroupsApiRequest $request): Response
    {
        $result = $this->listClassGroups->execute($request->toFilter());

        return Inertia::render('Academic/KelasRombel/pages/Index', [
            'classGroups' => [
                'data' => array_map(
                    static fn (ClassGroupData $classGroup): array => (new ClassGroupResource($classGroup))->toArray($request),
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
                'academicYears' => $this->referenceOptions('academic_years'),
                'academicTerms' => $this->referenceOptions('academic_terms'),
                'units' => $this->referenceOptions('organization_units'),
                'curricula' => $this->referenceOptions('academic_curricula'),
            ],
        ]);
    }

    public function show(Request $request, string $classGroup): Response
    {
        $data = $this->showClassGroup->execute($classGroup);

        abort_if($data === null, 404);

        return Inertia::render('Academic/KelasRombel/pages/Show', [
            'classGroup' => (new ClassGroupResource($data, includeDetails: true))->toArray($request),
        ]);
    }

    /** @return array{currentPage: int, perPage: int, total: int, lastPage: int} */
    private function paginationMeta(PaginatedClassGroupData $result): array
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
    private function referenceOptions(string $table): array
    {
        $records = DB::table($table)
            ->select(['id', 'code', 'name'])
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
