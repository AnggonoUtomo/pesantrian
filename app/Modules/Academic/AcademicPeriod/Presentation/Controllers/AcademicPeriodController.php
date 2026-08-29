<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod\Presentation\Controllers;

use App\Modules\Academic\AcademicPeriod\Application\DTO\AcademicTermData;
use App\Modules\Academic\AcademicPeriod\Application\DTO\AcademicTermListFilter;
use App\Modules\Academic\AcademicPeriod\Application\DTO\AcademicYearData;
use App\Modules\Academic\AcademicPeriod\Application\DTO\AcademicYearListFilter;
use App\Modules\Academic\AcademicPeriod\Application\DTO\PaginatedAcademicTermData;
use App\Modules\Academic\AcademicPeriod\Application\DTO\PaginatedAcademicYearData;
use App\Modules\Academic\AcademicPeriod\Application\Queries\GetCurrentAcademicTerm;
use App\Modules\Academic\AcademicPeriod\Application\Queries\ListAcademicTerms;
use App\Modules\Academic\AcademicPeriod\Application\Queries\ListAcademicYears;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

final readonly class AcademicPeriodController implements HasMiddleware
{
    public function __construct(
        private ListAcademicYears $listAcademicYears,
        private ListAcademicTerms $listAcademicTerms,
        private GetCurrentAcademicTerm $getCurrentAcademicTerm,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:academic_period.view', only: ['index']),
        ];
    }

    public function index(Request $request): Response
    {
        $perPage = $this->allowedPerPage($request->integer('per_page', 25));
        $yearSearch = $this->nullableString($request->query('year_search'));
        $termSearch = $this->nullableString($request->query('term_search'));
        $yearStatus = $this->allowedStatus($request->query('year_status'));
        $termStatus = $this->allowedStatus($request->query('term_status'));

        $years = $this->listAcademicYears->execute(new AcademicYearListFilter(
            search: $yearSearch,
            status: $yearStatus,
            page: max(1, $request->integer('year_page', 1)),
            perPage: $perPage,
            sortField: 'starts_on',
            sortDirection: 'desc',
        ));
        $terms = $this->listAcademicTerms->execute(new AcademicTermListFilter(
            search: $termSearch,
            academicYearId: null,
            status: $termStatus,
            isActive: null,
            page: max(1, $request->integer('term_page', 1)),
            perPage: $perPage,
            sortField: 'starts_on',
            sortDirection: 'desc',
        ));
        $currentTerm = $this->getCurrentAcademicTerm->execute();

        return Inertia::render('modules/academic-period/pages/Index', [
            'years' => [
                'data' => array_map(
                    static fn (AcademicYearData $year): array => $year->toArray(),
                    $years->data,
                ),
                'meta' => $this->yearPaginationMeta($years),
            ],
            'terms' => [
                'data' => array_map(
                    static fn (AcademicTermData $term): array => $term->toArray(),
                    $terms->data,
                ),
                'meta' => $this->termPaginationMeta($terms),
            ],
            'currentTerm' => $currentTerm?->toArray(),
            'filters' => [
                'year_search' => $yearSearch,
                'term_search' => $termSearch,
                'year_status' => $yearStatus,
                'term_status' => $termStatus,
                'per_page' => $request->query('per_page'),
            ],
            'pagination' => [
                'perPageOptions' => [10, 25, 50, 100],
                'defaultPerPage' => 25,
            ],
            'canManage' => $request->user()?->can('academic_period.manage') === true,
        ]);
    }

    /** @return array{currentPage: int, perPage: int, total: int, lastPage: int} */
    private function yearPaginationMeta(PaginatedAcademicYearData $result): array
    {
        return [
            'currentPage' => $result->currentPage,
            'perPage' => $result->perPage,
            'total' => $result->total,
            'lastPage' => $result->lastPage,
        ];
    }

    /** @return array{currentPage: int, perPage: int, total: int, lastPage: int} */
    private function termPaginationMeta(PaginatedAcademicTermData $result): array
    {
        return [
            'currentPage' => $result->currentPage,
            'perPage' => $result->perPage,
            'total' => $result->total,
            'lastPage' => $result->lastPage,
        ];
    }

    private function allowedPerPage(int $perPage): int
    {
        return in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 25;
    }

    private function allowedStatus(mixed $status): ?string
    {
        return is_string($status) && in_array($status, ['draft', 'active', 'closed'], true)
            ? $status
            : null;
    }

    private function nullableString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : mb_substr($value, 0, 100);
    }
}
