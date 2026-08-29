<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod\Presentation\Controllers;

use App\Modules\Academic\AcademicPeriod\Application\DTO\AcademicTermData;
use App\Modules\Academic\AcademicPeriod\Application\DTO\AcademicTermListFilter;
use App\Modules\Academic\AcademicPeriod\Application\DTO\AcademicYearData;
use App\Modules\Academic\AcademicPeriod\Application\DTO\AcademicYearListFilter;
use App\Modules\Academic\AcademicPeriod\Application\DTO\PaginatedAcademicTermData;
use App\Modules\Academic\AcademicPeriod\Application\DTO\PaginatedAcademicYearData;
use App\Modules\Academic\AcademicPeriod\Application\Exceptions\AcademicPeriodLifecycleException;
use App\Modules\Academic\AcademicPeriod\Application\Actions\ActivateAcademicTerm;
use App\Modules\Academic\AcademicPeriod\Application\Actions\CloseAcademicTerm;
use App\Modules\Academic\AcademicPeriod\Application\Actions\CreateAcademicTerm;
use App\Modules\Academic\AcademicPeriod\Application\Actions\CreateAcademicYear;
use App\Modules\Academic\AcademicPeriod\Application\Actions\UpdateAcademicTerm;
use App\Modules\Academic\AcademicPeriod\Application\Actions\UpdateAcademicYear;
use App\Modules\Academic\AcademicPeriod\Application\Queries\GetCurrentAcademicTerm;
use App\Modules\Academic\AcademicPeriod\Application\Queries\ListAcademicTerms;
use App\Modules\Academic\AcademicPeriod\Application\Queries\ListAcademicYears;
use App\Modules\Academic\AcademicPeriod\Presentation\Requests\StoreAcademicTermApiRequest;
use App\Modules\Academic\AcademicPeriod\Presentation\Requests\StoreAcademicYearApiRequest;
use App\Modules\Academic\AcademicPeriod\Presentation\Requests\UpdateAcademicTermApiRequest;
use App\Modules\Academic\AcademicPeriod\Presentation\Requests\UpdateAcademicYearApiRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

final readonly class AcademicPeriodController implements HasMiddleware
{
    public function __construct(
        private ListAcademicYears $listAcademicYears,
        private ListAcademicTerms $listAcademicTerms,
        private GetCurrentAcademicTerm $getCurrentAcademicTerm,
        private CreateAcademicYear $createAcademicYear,
        private UpdateAcademicYear $updateAcademicYear,
        private CreateAcademicTerm $createAcademicTerm,
        private UpdateAcademicTerm $updateAcademicTerm,
        private ActivateAcademicTerm $activateAcademicTerm,
        private CloseAcademicTerm $closeAcademicTerm,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:academic_period.view', only: ['index']),
            new Middleware('can:academic_period.manage', only: [
                'storeYear',
                'updateYear',
                'storeTerm',
                'updateTerm',
                'activateTerm',
                'closeTerm',
            ]),
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

        return Inertia::render('Academic/AcademicPeriod/pages/Index', [
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

    public function storeYear(StoreAcademicYearApiRequest $request): RedirectResponse
    {
        $this->createAcademicYear->execute($request->user(), $request->toData());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Tahun akademik berhasil dibuat.']);

        return back();
    }

    public function updateYear(UpdateAcademicYearApiRequest $request, string $year): RedirectResponse
    {
        $updated = $this->updateAcademicYear->execute($request->user(), $year, $request->changes());

        abort_if($updated === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Tahun akademik berhasil diperbarui.']);

        return back();
    }

    public function storeTerm(StoreAcademicTermApiRequest $request): RedirectResponse
    {
        $this->createAcademicTerm->execute($request->user(), $request->toData());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Term akademik berhasil dibuat.']);

        return back();
    }

    public function updateTerm(UpdateAcademicTermApiRequest $request, string $term): RedirectResponse
    {
        $updated = $this->updateAcademicTerm->execute($request->user(), $term, $request->changes());

        abort_if($updated === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Term akademik berhasil diperbarui.']);

        return back();
    }

    public function activateTerm(Request $request, string $term): RedirectResponse
    {
        try {
            $activated = $this->activateAcademicTerm->execute($request->user(), $term);
        } catch (AcademicPeriodLifecycleException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        abort_if($activated === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Term akademik berhasil diaktifkan.']);

        return back();
    }

    public function closeTerm(Request $request, string $term): RedirectResponse
    {
        $closed = $this->closeAcademicTerm->execute($request->user(), $term);

        abort_if($closed === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Term akademik berhasil ditutup.']);

        return back();
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
