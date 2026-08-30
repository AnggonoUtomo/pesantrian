<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PenerimaanSantri\Presentation\Controllers;

use App\Modules\Pesantrian\PenerimaanSantri\Application\DTO\PaginatedStudentAdmissionData;
use App\Modules\Pesantrian\PenerimaanSantri\Application\DTO\StudentAdmissionData;
use App\Modules\Pesantrian\PenerimaanSantri\Application\Queries\ListStudentAdmissions;
use App\Modules\Pesantrian\PenerimaanSantri\Presentation\Requests\ListStudentAdmissionsApiRequest;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

final readonly class StudentAdmissionController implements HasMiddleware
{
    public function __construct(private ListStudentAdmissions $listStudentAdmissions) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:penerimaan_santri.view', only: ['index']),
        ];
    }

    public function index(ListStudentAdmissionsApiRequest $request): Response
    {
        $result = $this->listStudentAdmissions->execute($request->toFilter());

        return Inertia::render('Pesantrian/PenerimaanSantri/pages/Index', [
            'admissions' => [
                'data' => array_map(
                    static fn (StudentAdmissionData $admission): array => $admission->toArray(),
                    $result->data,
                ),
                'meta' => $this->paginationMeta($result),
            ],
            'filters' => $request->safe()->only(['search', 'filter', 'page', 'per_page', 'sort']),
            'pagination' => [
                'perPageOptions' => [10, 25, 50, 100],
                'defaultPerPage' => 25,
            ],
            'targetUnitOptions' => $this->targetUnitOptions(),
        ]);
    }

    /** @return array{currentPage: int, perPage: int, total: int, lastPage: int} */
    private function paginationMeta(PaginatedStudentAdmissionData $result): array
    {
        return [
            'currentPage' => $result->currentPage,
            'perPage' => $result->perPage,
            'total' => $result->total,
            'lastPage' => $result->lastPage,
        ];
    }

    /** @return list<array{id: string, code: string, name: string}> */
    private function targetUnitOptions(): array
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
