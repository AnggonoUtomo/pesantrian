<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Santri\Presentation\Controllers;

use App\Modules\Pesantrian\Santri\Application\DTO\PaginatedStudentData;
use App\Modules\Pesantrian\Santri\Application\DTO\StudentData;
use App\Modules\Pesantrian\Santri\Application\Queries\ListStudents;
use App\Modules\Pesantrian\Santri\Application\Queries\ShowStudent;
use App\Modules\Pesantrian\Santri\Presentation\Requests\ListStudentsApiRequest;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

final readonly class StudentController implements HasMiddleware
{
    public function __construct(
        private ListStudents $listStudents,
        private ShowStudent $showStudent,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:santri.view', only: ['index', 'show']),
        ];
    }

    public function index(ListStudentsApiRequest $request): Response
    {
        $result = $this->listStudents->execute($request->toFilter());

        return Inertia::render('Pesantrian/Santri/pages/Index', [
            'students' => [
                'data' => array_map(
                    static fn (StudentData $student): array => $student->toArray(includeGuardians: false),
                    $result->data,
                ),
                'meta' => $this->paginationMeta($result),
            ],
            'filters' => $request->safe()->only(['search', 'filter', 'page', 'per_page', 'sort']),
            'pagination' => [
                'perPageOptions' => [10, 25, 50, 100],
                'defaultPerPage' => 25,
            ],
            'primaryUnitOptions' => $this->primaryUnitOptions(),
        ]);
    }

    public function show(string $student): Response
    {
        $data = $this->showStudent->execute($student);

        abort_if($data === null, 404);

        return Inertia::render('Pesantrian/Santri/pages/Show', [
            'student' => $data->toArray(),
            'primaryUnitOptions' => $this->primaryUnitOptions(),
        ]);
    }

    /** @return array{currentPage: int, perPage: int, total: int, lastPage: int} */
    private function paginationMeta(PaginatedStudentData $result): array
    {
        return [
            'currentPage' => $result->currentPage,
            'perPage' => $result->perPage,
            'total' => $result->total,
            'lastPage' => $result->lastPage,
        ];
    }

    /** @return list<array{id: string, code: string, name: string}> */
    private function primaryUnitOptions(): array
    {
        $units = DB::table('organization_units')
            ->select(['id', 'code', 'name'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
        $options = [];

        foreach ($units as $unit) {
            $options[] = [
                'id' => (string) $unit->id,
                'code' => (string) $unit->code,
                'name' => (string) $unit->name,
            ];
        }

        return $options;
    }
}
