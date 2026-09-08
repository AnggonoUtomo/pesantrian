<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Presentation\Controllers;

use App\Http\ApiResponseFactory;
use App\Modules\HumanResource\HumanResource\Application\Contracts\ActiveEmployeeReader;
use App\Modules\HumanResource\HumanResource\Application\DTO\ActiveEmployeeOptionData;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Actions\AssignStudentDisciplineCaseAction;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Actions\CreateStudentDisciplineCaseDraft;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Actions\ResolveStudentDisciplineCase;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Actions\ReviewStudentDisciplineCase;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Actions\SubmitStudentDisciplineCaseDraft;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Actions\UpdateStudentDisciplineCaseDraft;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Actions\VoidStudentDisciplineCase;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\PaginatedStudentDisciplineCaseData;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCaseData;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCategoryData;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCategoryListFilter;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Exceptions\StudentDisciplineMutationException;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Queries\ListStudentDisciplineCases;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Queries\ListStudentDisciplineCategories;
use App\Modules\Pesantrian\KedisiplinanSantri\Application\Queries\ShowStudentDisciplineCase;
use App\Modules\Pesantrian\KedisiplinanSantri\Presentation\Requests\AssignStudentDisciplineCaseActionApiRequest;
use App\Modules\Pesantrian\KedisiplinanSantri\Presentation\Requests\ListStudentDisciplineCasesApiRequest;
use App\Modules\Pesantrian\KedisiplinanSantri\Presentation\Requests\ResolveStudentDisciplineCaseApiRequest;
use App\Modules\Pesantrian\KedisiplinanSantri\Presentation\Requests\ReviewStudentDisciplineCaseApiRequest;
use App\Modules\Pesantrian\KedisiplinanSantri\Presentation\Requests\StoreStudentDisciplineCaseApiRequest;
use App\Modules\Pesantrian\KedisiplinanSantri\Presentation\Requests\UpdateStudentDisciplineCaseApiRequest;
use App\Modules\Pesantrian\KedisiplinanSantri\Presentation\Requests\VoidStudentDisciplineCaseApiRequest;
use App\Modules\Pesantrian\KedisiplinanSantri\Presentation\Resources\StudentDisciplineCaseResource;
use App\Modules\Pesantrian\Santri\Application\Contracts\ActiveStudentReader;
use App\Modules\Pesantrian\Santri\Application\DTO\ActiveStudentOptionData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

final readonly class StudentDisciplineController implements HasMiddleware
{
    public function __construct(
        private ListStudentDisciplineCases $listCases,
        private ShowStudentDisciplineCase $showCase,
        private ListStudentDisciplineCategories $listCategories,
        private CreateStudentDisciplineCaseDraft $createCaseDraft,
        private UpdateStudentDisciplineCaseDraft $updateCaseDraft,
        private SubmitStudentDisciplineCaseDraft $submitCaseDraft,
        private ReviewStudentDisciplineCase $reviewCase,
        private AssignStudentDisciplineCaseAction $assignCaseAction,
        private ResolveStudentDisciplineCase $resolveCase,
        private VoidStudentDisciplineCase $voidCase,
        private ActiveStudentReader $students,
        private ActiveEmployeeReader $employees,
        private ApiResponseFactory $responses,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:kedisiplinan_santri.view', only: ['index', 'show']),
            new Middleware('can:kedisiplinan_santri.manage', only: ['store', 'update', 'submit']),
            new Middleware('can:kedisiplinan_santri.review', only: ['review', 'assignAction']),
            new Middleware('can:kedisiplinan_santri.resolve', only: ['resolve', 'void']),
        ];
    }

    public function index(ListStudentDisciplineCasesApiRequest $request): Response
    {
        $result = $this->listCases->execute($request->toFilter());

        return Inertia::render('Pesantrian/KedisiplinanSantri/pages/Index', [
            'cases' => [
                'data' => array_map(
                    static fn (StudentDisciplineCaseData $case): array => (new StudentDisciplineCaseResource($case, includeRevisions: false))->toArray($request),
                    $result->data,
                ),
                'meta' => $this->paginationMeta($result),
            ],
            'filters' => $request->safe()->only(['search', 'filter', 'page', 'per_page', 'sort']),
            'pagination' => [
                'perPageOptions' => [10, 25, 50, 100],
                'defaultPerPage' => 25,
            ],
            'options' => $this->options(),
            'canManage' => $request->user()?->can('kedisiplinan_santri.manage') === true,
            'canReview' => $request->user()?->can('kedisiplinan_santri.review') === true,
            'canResolve' => $request->user()?->can('kedisiplinan_santri.resolve') === true,
            'canArchive' => $request->user()?->can('kedisiplinan_santri.archive') === true,
        ]);
    }

    public function show(Request $request, string $case): Response
    {
        $data = $this->showCase->execute($case);

        abort_if($data === null, 404);

        return Inertia::render('Pesantrian/KedisiplinanSantri/pages/Show', [
            'case' => (new StudentDisciplineCaseResource($data))->toArray($request),
            'options' => $this->options(),
            'canManage' => $request->user()?->can('kedisiplinan_santri.manage') === true,
            'canReview' => $request->user()?->can('kedisiplinan_santri.review') === true,
            'canResolve' => $request->user()?->can('kedisiplinan_santri.resolve') === true,
            'canArchive' => $request->user()?->can('kedisiplinan_santri.archive') === true,
        ]);
    }

    public function store(StoreStudentDisciplineCaseApiRequest $request): RedirectResponse
    {
        try {
            $case = $this->createCaseDraft->execute(
                $request->user(),
                $request->toData(),
                $this->responses->correlationId($request),
            );
        } catch (StudentDisciplineMutationException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Draft kasus kedisiplinan santri berhasil dibuat.']);

        return to_route('pesantrian.student-discipline-cases.show', $case->id);
    }

    public function update(UpdateStudentDisciplineCaseApiRequest $request, string $case): RedirectResponse
    {
        try {
            $updated = $this->updateCaseDraft->execute(
                $request->user(),
                $case,
                $request->toData(),
                $request->revisionReason(),
                $this->responses->correlationId($request),
            );
        } catch (StudentDisciplineMutationException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        abort_if($updated === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Draft kasus kedisiplinan santri berhasil diperbarui.']);

        return to_route('pesantrian.student-discipline-cases.show', $updated->id);
    }

    public function submit(Request $request, string $case): RedirectResponse
    {
        try {
            $updated = $this->submitCaseDraft->execute(
                $request->user(),
                $case,
                $this->responses->correlationId($request),
            );
        } catch (StudentDisciplineMutationException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        abort_if($updated === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Draft kasus kedisiplinan santri berhasil disubmit.']);

        return to_route('pesantrian.student-discipline-cases.show', $updated->id);
    }

    public function review(ReviewStudentDisciplineCaseApiRequest $request, string $case): RedirectResponse
    {
        try {
            $updated = $this->reviewCase->execute(
                $request->user(),
                $case,
                $request->reviewNote(),
                $this->responses->correlationId($request),
            );
        } catch (StudentDisciplineMutationException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        abort_if($updated === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Kasus kedisiplinan santri berhasil direview.']);

        return to_route('pesantrian.student-discipline-cases.show', $updated->id);
    }

    public function assignAction(AssignStudentDisciplineCaseActionApiRequest $request, string $case): RedirectResponse
    {
        try {
            $updated = $this->assignCaseAction->execute(
                $request->user(),
                $case,
                $request->actionPlan(),
                $request->assignedEmployeeId(),
                $this->responses->correlationId($request),
            );
        } catch (StudentDisciplineMutationException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        abort_if($updated === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Tindakan pembinaan kasus kedisiplinan berhasil ditetapkan.']);

        return to_route('pesantrian.student-discipline-cases.show', $updated->id);
    }

    public function resolve(ResolveStudentDisciplineCaseApiRequest $request, string $case): RedirectResponse
    {
        try {
            $updated = $this->resolveCase->execute(
                $request->user(),
                $case,
                $request->resolutionNote(),
                $this->responses->correlationId($request),
            );
        } catch (StudentDisciplineMutationException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        abort_if($updated === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Kasus kedisiplinan santri berhasil diselesaikan.']);

        return to_route('pesantrian.student-discipline-cases.show', $updated->id);
    }

    public function void(VoidStudentDisciplineCaseApiRequest $request, string $case): RedirectResponse
    {
        try {
            $updated = $this->voidCase->execute(
                $request->user(),
                $case,
                $request->voidReason(),
                $this->responses->correlationId($request),
            );
        } catch (StudentDisciplineMutationException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        abort_if($updated === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Kasus kedisiplinan santri berhasil dibatalkan.']);

        return to_route('pesantrian.student-discipline-cases.show', $updated->id);
    }

    /** @return array{currentPage: int, perPage: int, total: int, lastPage: int} */
    private function paginationMeta(PaginatedStudentDisciplineCaseData $result): array
    {
        return [
            'currentPage' => $result->currentPage,
            'perPage' => $result->perPage,
            'total' => $result->total,
            'lastPage' => $result->lastPage,
        ];
    }

    /** @return array{categories: list<array{value: string, label: string}>, students: list<array{value: string, label: string}>, officers: list<array{value: string, label: string}>, severities: list<array{value: string, label: string}>, statuses: list<array{value: string, label: string}>} */
    private function options(): array
    {
        return [
            'categories' => array_map(
                static fn (StudentDisciplineCategoryData $category): array => [
                    'value' => $category->id,
                    'label' => $category->name,
                ],
                $this->listCategories->execute(new StudentDisciplineCategoryListFilter(null, 'active')),
            ),
            'students' => array_map(
                static fn (ActiveStudentOptionData $student): array => [
                    'value' => $student->id,
                    'label' => $student->fullName.' ('.$student->studentNo.')',
                ],
                $this->students->options(limit: 200),
            ),
            'officers' => array_map(
                static fn (ActiveEmployeeOptionData $employee): array => [
                    'value' => $employee->id,
                    'label' => $employee->name.' ('.$employee->employeeNo.')',
                ],
                $this->employees->options(limit: 200),
            ),
            'severities' => [
                ['value' => 'minor', 'label' => 'Ringan'],
                ['value' => 'moderate', 'label' => 'Sedang'],
                ['value' => 'major', 'label' => 'Berat'],
            ],
            'statuses' => [
                ['value' => 'draft', 'label' => 'Draft'],
                ['value' => 'submitted', 'label' => 'Menunggu review'],
                ['value' => 'in_review', 'label' => 'Dalam review'],
                ['value' => 'action_assigned', 'label' => 'Tindakan ditetapkan'],
                ['value' => 'resolved', 'label' => 'Selesai'],
                ['value' => 'void', 'label' => 'Dibatalkan'],
            ],
        ];
    }
}
