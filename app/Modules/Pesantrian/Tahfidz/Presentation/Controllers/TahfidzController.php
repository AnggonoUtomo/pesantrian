<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Presentation\Controllers;

use App\Http\ApiResponseFactory;
use App\Modules\Academic\AcademicPeriod\Application\Contracts\ActiveAcademicPeriodReader;
use App\Modules\HumanResource\HumanResource\Application\Contracts\ActiveEmployeeReader;
use App\Modules\HumanResource\HumanResource\Application\DTO\ActiveEmployeeOptionData;
use App\Modules\Pesantrian\Santri\Application\Contracts\ActiveStudentReader;
use App\Modules\Pesantrian\Santri\Application\DTO\ActiveStudentOptionData;
use App\Modules\Pesantrian\Tahfidz\Application\Actions\CreateTahfidzProgram;
use App\Modules\Pesantrian\Tahfidz\Application\Actions\CreateTahfidzSubmission;
use App\Modules\Pesantrian\Tahfidz\Application\Actions\CreateTahfidzTarget;
use App\Modules\Pesantrian\Tahfidz\Application\Actions\ReviewTahfidzSubmission;
use App\Modules\Pesantrian\Tahfidz\Application\Actions\UpdateTahfidzProgram;
use App\Modules\Pesantrian\Tahfidz\Application\Actions\UpdateTahfidzSubmission;
use App\Modules\Pesantrian\Tahfidz\Application\Actions\UpdateTahfidzTarget;
use App\Modules\Pesantrian\Tahfidz\Application\Actions\VoidTahfidzSubmission;
use App\Modules\Pesantrian\Tahfidz\Application\Contracts\TahfidzReadRepository;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\PaginatedTahfidzSubmissionData;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\TahfidzSubmissionData;
use App\Modules\Pesantrian\Tahfidz\Application\Exceptions\TahfidzMutationException;
use App\Modules\Pesantrian\Tahfidz\Application\Queries\ListTahfidzSubmissions;
use App\Modules\Pesantrian\Tahfidz\Application\Queries\ShowTahfidzSubmission;
use App\Modules\Pesantrian\Tahfidz\Presentation\Requests\ListTahfidzSubmissionsApiRequest;
use App\Modules\Pesantrian\Tahfidz\Presentation\Requests\StoreTahfidzProgramApiRequest;
use App\Modules\Pesantrian\Tahfidz\Presentation\Requests\StoreTahfidzSubmissionApiRequest;
use App\Modules\Pesantrian\Tahfidz\Presentation\Requests\StoreTahfidzTargetApiRequest;
use App\Modules\Pesantrian\Tahfidz\Presentation\Requests\TahfidzReasonApiRequest;
use App\Modules\Pesantrian\Tahfidz\Presentation\Requests\TahfidzReviewApiRequest;
use App\Modules\Pesantrian\Tahfidz\Presentation\Requests\UpdateTahfidzProgramApiRequest;
use App\Modules\Pesantrian\Tahfidz\Presentation\Requests\UpdateTahfidzSubmissionApiRequest;
use App\Modules\Pesantrian\Tahfidz\Presentation\Requests\UpdateTahfidzTargetApiRequest;
use App\Modules\Pesantrian\Tahfidz\Presentation\Resources\TahfidzSubmissionResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

final readonly class TahfidzController implements HasMiddleware
{
    public function __construct(
        private ListTahfidzSubmissions $listTahfidzSubmissions,
        private ShowTahfidzSubmission $showTahfidzSubmission,
        private CreateTahfidzProgram $createTahfidzProgram,
        private UpdateTahfidzProgram $updateTahfidzProgram,
        private CreateTahfidzTarget $createTahfidzTarget,
        private UpdateTahfidzTarget $updateTahfidzTarget,
        private CreateTahfidzSubmission $createTahfidzSubmission,
        private UpdateTahfidzSubmission $updateTahfidzSubmission,
        private ReviewTahfidzSubmission $reviewTahfidzSubmission,
        private VoidTahfidzSubmission $voidTahfidzSubmission,
        private TahfidzReadRepository $reader,
        private ActiveStudentReader $students,
        private ActiveEmployeeReader $employees,
        private ActiveAcademicPeriodReader $academicPeriods,
        private ApiResponseFactory $responses,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:tahfidz.view', only: ['index', 'show']),
            new Middleware('can:tahfidz.manage', only: ['storeProgram', 'updateProgram', 'storeTarget', 'updateTarget']),
            new Middleware('can:tahfidz.record', only: ['storeSubmission', 'updateSubmission']),
            new Middleware('can:tahfidz.review', only: ['reviewSubmission']),
            new Middleware('can:tahfidz.archive', only: ['voidSubmission']),
        ];
    }

    public function index(ListTahfidzSubmissionsApiRequest $request): Response
    {
        $result = $this->listTahfidzSubmissions->execute($request->toFilter());

        return Inertia::render('Pesantrian/Tahfidz/pages/Index', [
            'submissions' => [
                'data' => array_map(
                    static fn (TahfidzSubmissionData $submission): array => (new TahfidzSubmissionResource($submission, includeRevisions: false))->toArray($request),
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
            'canManage' => $request->user()?->can('tahfidz.manage') === true,
            'canRecord' => $request->user()?->can('tahfidz.record') === true,
            'canReview' => $request->user()?->can('tahfidz.review') === true,
            'canArchive' => $request->user()?->can('tahfidz.archive') === true,
        ]);
    }

    public function storeProgram(StoreTahfidzProgramApiRequest $request): RedirectResponse
    {
        $this->createTahfidzProgram->execute($request->user(), $request->toData(), $this->responses->correlationId($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Program tahfidz berhasil dibuat.']);

        return to_route('pesantrian.tahfidz.index');
    }

    public function updateProgram(UpdateTahfidzProgramApiRequest $request, string $program): RedirectResponse
    {
        $updated = $this->updateTahfidzProgram->execute($request->user(), $program, $request->changes(), $this->responses->correlationId($request));

        abort_if($updated === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Program tahfidz berhasil diperbarui.']);

        return to_route('pesantrian.tahfidz.index');
    }

    public function storeTarget(StoreTahfidzTargetApiRequest $request): RedirectResponse
    {
        try {
            $this->createTahfidzTarget->execute($request->user(), $request->toData(), $this->responses->correlationId($request));
        } catch (TahfidzMutationException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Target hafalan berhasil dibuat.']);

        return to_route('pesantrian.tahfidz.index');
    }

    public function updateTarget(UpdateTahfidzTargetApiRequest $request, string $target): RedirectResponse
    {
        try {
            $updated = $this->updateTahfidzTarget->execute($request->user(), $target, $request->changes(), $this->responses->correlationId($request));
        } catch (TahfidzMutationException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        abort_if($updated === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Target hafalan berhasil diperbarui.']);

        return to_route('pesantrian.tahfidz.index');
    }

    public function storeSubmission(StoreTahfidzSubmissionApiRequest $request): RedirectResponse
    {
        try {
            $submission = $this->createTahfidzSubmission->execute($request->user(), $request->toData(), $this->responses->correlationId($request));
        } catch (TahfidzMutationException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Setoran tahfidz berhasil dibuat.']);

        return to_route('pesantrian.tahfidz.show', $submission->id);
    }

    public function updateSubmission(UpdateTahfidzSubmissionApiRequest $request, string $submission): RedirectResponse
    {
        try {
            $updated = $this->updateTahfidzSubmission->execute($request->user(), $submission, $request->changes(), $this->responses->correlationId($request));
        } catch (TahfidzMutationException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        abort_if($updated === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Setoran tahfidz berhasil diperbarui.']);

        return to_route('pesantrian.tahfidz.show', $submission);
    }

    public function reviewSubmission(TahfidzReviewApiRequest $request, string $submission): RedirectResponse
    {
        try {
            $updated = $this->reviewTahfidzSubmission->execute(
                $request->user(),
                $submission,
                (string) $request->validated('status'),
                (string) $request->validated('reason'),
                $this->responses->correlationId($request),
            );
        } catch (TahfidzMutationException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        abort_if($updated === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Setoran tahfidz berhasil direview.']);

        return to_route('pesantrian.tahfidz.show', $submission);
    }

    public function voidSubmission(TahfidzReasonApiRequest $request, string $submission): RedirectResponse
    {
        try {
            $updated = $this->voidTahfidzSubmission->execute(
                $request->user(),
                $submission,
                (string) $request->validated('reason'),
                $this->responses->correlationId($request),
            );
        } catch (TahfidzMutationException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        abort_if($updated === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Setoran tahfidz berhasil dibatalkan.']);

        return to_route('pesantrian.tahfidz.index');
    }

    public function show(Request $request, string $submission): Response
    {
        $data = $this->showTahfidzSubmission->execute($submission);

        abort_if($data === null, 404);

        return Inertia::render('Pesantrian/Tahfidz/pages/Show', [
            'submission' => (new TahfidzSubmissionResource($data))->toArray($request),
            'options' => $this->options(),
            'canManage' => $request->user()?->can('tahfidz.manage') === true,
            'canRecord' => $request->user()?->can('tahfidz.record') === true,
            'canReview' => $request->user()?->can('tahfidz.review') === true,
            'canArchive' => $request->user()?->can('tahfidz.archive') === true,
        ]);
    }

    /** @return array{currentPage: int, perPage: int, total: int, lastPage: int} */
    private function paginationMeta(PaginatedTahfidzSubmissionData $result): array
    {
        return [
            'currentPage' => $result->currentPage,
            'perPage' => $result->perPage,
            'total' => $result->total,
            'lastPage' => $result->lastPage,
        ];
    }

    /** @return array<string, mixed> */
    private function options(): array
    {
        $currentPeriod = $this->academicPeriods->current();

        return [
            'programs' => $this->reader->programOptions(),
            'targets' => $this->reader->targetOptions(),
            'students' => array_map(
                static fn (ActiveStudentOptionData $student): array => [
                    'id' => $student->id,
                    'code' => $student->studentNo,
                    'name' => $student->fullName,
                ],
                $this->students->options(limit: 200),
            ),
            'employees' => array_map(
                static fn (ActiveEmployeeOptionData $employee): array => [
                    'id' => $employee->id,
                    'code' => $employee->employeeNo,
                    'name' => $employee->name,
                    'position' => $employee->position,
                ],
                $this->employees->options(limit: 200),
            ),
            'academicPeriods' => $currentPeriod === null ? [] : [[
                'id' => $currentPeriod->termId,
                'label' => trim($currentPeriod->termName.' '.$currentPeriod->academicYearName),
            ]],
            'types' => [
                ['value' => 'new_memorization', 'label' => 'Hafalan baru'],
                ['value' => 'murojaah', 'label' => 'Murojaah'],
            ],
            'statuses' => [
                ['value' => 'draft', 'label' => 'Draft'],
                ['value' => 'submitted', 'label' => 'Menunggu review'],
                ['value' => 'accepted', 'label' => 'Diterima'],
                ['value' => 'needs_revision', 'label' => 'Perlu koreksi'],
                ['value' => 'void', 'label' => 'Dibatalkan'],
            ],
        ];
    }
}
