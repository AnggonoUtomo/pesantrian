<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Presentation\Controllers;

use App\Http\ApiResponseFactory;
use App\Modules\Academic\AcademicPeriod\Application\Contracts\ActiveAcademicPeriodReader;
use App\Modules\HumanResource\HumanResource\Application\Contracts\ActiveEmployeeReader;
use App\Modules\HumanResource\HumanResource\Application\DTO\ActiveEmployeeOptionData;
use App\Modules\Pesantrian\PrestasiSantri\Application\Actions\ArchiveStudentAchievementCategory;
use App\Modules\Pesantrian\PrestasiSantri\Application\Actions\CreateStudentAchievementCategory;
use App\Modules\Pesantrian\PrestasiSantri\Application\Actions\CreateStudentAchievementDraft;
use App\Modules\Pesantrian\PrestasiSantri\Application\Actions\RequestStudentAchievementRevision;
use App\Modules\Pesantrian\PrestasiSantri\Application\Actions\SubmitStudentAchievementDraft;
use App\Modules\Pesantrian\PrestasiSantri\Application\Actions\UpdateStudentAchievementCategory;
use App\Modules\Pesantrian\PrestasiSantri\Application\Actions\UpdateStudentAchievementDraft;
use App\Modules\Pesantrian\PrestasiSantri\Application\Actions\VerifyStudentAchievement;
use App\Modules\Pesantrian\PrestasiSantri\Application\Actions\VoidStudentAchievement;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\PaginatedStudentAchievementData;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementCategoryData;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementCategoryListFilter;
use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementData;
use App\Modules\Pesantrian\PrestasiSantri\Application\Exceptions\StudentAchievementMutationException;
use App\Modules\Pesantrian\PrestasiSantri\Application\Queries\ListStudentAchievementCategories;
use App\Modules\Pesantrian\PrestasiSantri\Application\Queries\ListStudentAchievements;
use App\Modules\Pesantrian\PrestasiSantri\Application\Queries\ShowStudentAchievement;
use App\Modules\Pesantrian\PrestasiSantri\Presentation\Requests\ArchiveStudentAchievementCategoryApiRequest;
use App\Modules\Pesantrian\PrestasiSantri\Presentation\Requests\ListStudentAchievementsApiRequest;
use App\Modules\Pesantrian\PrestasiSantri\Presentation\Requests\RequestStudentAchievementRevisionApiRequest;
use App\Modules\Pesantrian\PrestasiSantri\Presentation\Requests\StoreStudentAchievementApiRequest;
use App\Modules\Pesantrian\PrestasiSantri\Presentation\Requests\StoreStudentAchievementCategoryApiRequest;
use App\Modules\Pesantrian\PrestasiSantri\Presentation\Requests\UpdateStudentAchievementApiRequest;
use App\Modules\Pesantrian\PrestasiSantri\Presentation\Requests\UpdateStudentAchievementCategoryApiRequest;
use App\Modules\Pesantrian\PrestasiSantri\Presentation\Requests\VerifyStudentAchievementApiRequest;
use App\Modules\Pesantrian\PrestasiSantri\Presentation\Requests\VoidStudentAchievementApiRequest;
use App\Modules\Pesantrian\PrestasiSantri\Presentation\Resources\StudentAchievementResource;
use App\Modules\Pesantrian\Santri\Application\Contracts\ActiveStudentReader;
use App\Modules\Pesantrian\Santri\Application\DTO\ActiveStudentOptionData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

final readonly class StudentAchievementController implements HasMiddleware
{
    public function __construct(
        private ListStudentAchievements $listAchievements,
        private ShowStudentAchievement $showAchievement,
        private ListStudentAchievementCategories $listCategories,
        private CreateStudentAchievementCategory $createCategory,
        private UpdateStudentAchievementCategory $updateCategory,
        private ArchiveStudentAchievementCategory $archiveCategory,
        private CreateStudentAchievementDraft $createDraft,
        private UpdateStudentAchievementDraft $updateDraft,
        private SubmitStudentAchievementDraft $submitDraft,
        private VerifyStudentAchievement $verifyAchievement,
        private RequestStudentAchievementRevision $requestRevision,
        private VoidStudentAchievement $voidAchievement,
        private ActiveStudentReader $students,
        private ActiveEmployeeReader $employees,
        private ActiveAcademicPeriodReader $academicPeriods,
        private ApiResponseFactory $responses,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:prestasi_santri.view', only: ['index', 'show']),
            new Middleware('can:prestasi_santri.manage', only: ['storeCategory', 'updateCategory', 'archiveCategory']),
            new Middleware('can:prestasi_santri.record', only: ['store', 'update', 'submit']),
            new Middleware('can:prestasi_santri.verify', only: ['verify', 'revise']),
            new Middleware('can:prestasi_santri.archive', only: ['void']),
        ];
    }

    public function index(ListStudentAchievementsApiRequest $request): Response
    {
        $result = $this->listAchievements->execute($request->toFilter());

        return Inertia::render('Pesantrian/PrestasiSantri/pages/Index', [
            'achievements' => [
                'data' => array_map(
                    static fn (StudentAchievementData $achievement): array => (new StudentAchievementResource($achievement, includeRevisions: false))->toArray($request),
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
            'canManage' => $request->user()?->can('prestasi_santri.manage') === true,
            'canRecord' => $request->user()?->can('prestasi_santri.record') === true,
            'canVerify' => $request->user()?->can('prestasi_santri.verify') === true,
            'canArchive' => $request->user()?->can('prestasi_santri.archive') === true,
        ]);
    }

    public function show(Request $request, string $achievement): Response
    {
        $data = $this->showAchievement->execute($achievement);

        abort_if($data === null, 404);

        return Inertia::render('Pesantrian/PrestasiSantri/pages/Show', [
            'achievement' => (new StudentAchievementResource($data))->toArray($request),
            'options' => $this->options(),
            'canManage' => $request->user()?->can('prestasi_santri.manage') === true,
            'canRecord' => $request->user()?->can('prestasi_santri.record') === true,
            'canVerify' => $request->user()?->can('prestasi_santri.verify') === true,
            'canArchive' => $request->user()?->can('prestasi_santri.archive') === true,
        ]);
    }

    public function storeCategory(StoreStudentAchievementCategoryApiRequest $request): RedirectResponse
    {
        $this->createCategory->execute(
            $request->user(),
            $request->toData(),
            $this->responses->correlationId($request),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Kategori prestasi santri berhasil dibuat.']);

        return back();
    }

    public function updateCategory(UpdateStudentAchievementCategoryApiRequest $request, string $category): RedirectResponse
    {
        $updated = $this->updateCategory->execute(
            $request->user(),
            $category,
            $request->changes(),
            $this->responses->correlationId($request),
        );

        abort_if($updated === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Kategori prestasi santri berhasil diperbarui.']);

        return back();
    }

    public function archiveCategory(ArchiveStudentAchievementCategoryApiRequest $request, string $category): RedirectResponse
    {
        $archived = $this->archiveCategory->execute(
            $request->user(),
            $category,
            $request->reason(),
            $this->responses->correlationId($request),
        );

        abort_if($archived === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Kategori prestasi santri berhasil diarsipkan.']);

        return back();
    }

    public function store(StoreStudentAchievementApiRequest $request): RedirectResponse
    {
        try {
            $data = $this->createDraft->execute(
                $request->user(),
                $request->toData(),
                $this->responses->correlationId($request),
            );
        } catch (StudentAchievementMutationException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Draft prestasi santri berhasil dibuat.']);

        return to_route('pesantrian.prestasi-santri.show', $data->id);
    }

    public function update(UpdateStudentAchievementApiRequest $request, string $achievement): RedirectResponse
    {
        try {
            $data = $this->updateDraft->execute(
                $request->user(),
                $achievement,
                $request->toData(),
                $request->revisionReason(),
                $this->responses->correlationId($request),
            );
        } catch (StudentAchievementMutationException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        abort_if($data === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Draft prestasi santri berhasil diperbarui.']);

        return to_route('pesantrian.prestasi-santri.show', $data->id);
    }

    public function submit(Request $request, string $achievement): RedirectResponse
    {
        try {
            $data = $this->submitDraft->execute(
                $request->user(),
                $achievement,
                $this->responses->correlationId($request),
            );
        } catch (StudentAchievementMutationException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        abort_if($data === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Draft prestasi santri berhasil disubmit.']);

        return to_route('pesantrian.prestasi-santri.show', $data->id);
    }

    public function verify(VerifyStudentAchievementApiRequest $request, string $achievement): RedirectResponse
    {
        try {
            $data = $this->verifyAchievement->execute(
                $request->user(),
                $achievement,
                $request->verificationNote(),
                $this->responses->correlationId($request),
            );
        } catch (StudentAchievementMutationException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        abort_if($data === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Prestasi santri berhasil diverifikasi.']);

        return to_route('pesantrian.prestasi-santri.show', $data->id);
    }

    public function revise(RequestStudentAchievementRevisionApiRequest $request, string $achievement): RedirectResponse
    {
        try {
            $data = $this->requestRevision->execute(
                $request->user(),
                $achievement,
                $request->verificationNote(),
                $this->responses->correlationId($request),
            );
        } catch (StudentAchievementMutationException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        abort_if($data === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Prestasi santri berhasil diminta revisi.']);

        return to_route('pesantrian.prestasi-santri.show', $data->id);
    }

    public function void(VoidStudentAchievementApiRequest $request, string $achievement): RedirectResponse
    {
        try {
            $data = $this->voidAchievement->execute(
                $request->user(),
                $achievement,
                $request->voidReason(),
                $this->responses->correlationId($request),
            );
        } catch (StudentAchievementMutationException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        abort_if($data === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Prestasi santri berhasil dibatalkan.']);

        return to_route('pesantrian.prestasi-santri.show', $data->id);
    }

    /** @return array{currentPage: int, perPage: int, total: int, lastPage: int} */
    private function paginationMeta(PaginatedStudentAchievementData $result): array
    {
        return [
            'currentPage' => $result->currentPage,
            'perPage' => $result->perPage,
            'total' => $result->total,
            'lastPage' => $result->lastPage,
        ];
    }

    /** @return array{categories: list<array{value: string, label: string, id: string, code: string, name: string, description: string|null, status: string}>, students: list<array{value: string, label: string}>, officers: list<array{value: string, label: string}>, academicPeriods: list<array{value: string, label: string}>, levels: list<array{value: string, label: string}>, statuses: list<array{value: string, label: string}>, types: list<array{value: string, label: string}>} */
    private function options(): array
    {
        $currentPeriod = $this->academicPeriods->current();

        return [
            'categories' => array_map(
                static fn (StudentAchievementCategoryData $category): array => [
                    'value' => $category->id,
                    'label' => $category->name,
                    'id' => $category->id,
                    'code' => $category->code,
                    'name' => $category->name,
                    'description' => $category->description,
                    'status' => $category->status,
                ],
                $this->listCategories->execute(new StudentAchievementCategoryListFilter(null, 'active')),
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
            'academicPeriods' => $currentPeriod === null ? [] : [[
                'value' => $currentPeriod->termId,
                'label' => $currentPeriod->termName.' - '.$currentPeriod->academicYearName,
            ]],
            'levels' => [
                ['value' => 'internal', 'label' => 'Internal'],
                ['value' => 'district', 'label' => 'Kecamatan'],
                ['value' => 'city', 'label' => 'Kabupaten/Kota'],
                ['value' => 'province', 'label' => 'Provinsi'],
                ['value' => 'national', 'label' => 'Nasional'],
                ['value' => 'international', 'label' => 'Internasional'],
            ],
            'statuses' => [
                ['value' => 'draft', 'label' => 'Draft'],
                ['value' => 'submitted', 'label' => 'Menunggu verifikasi'],
                ['value' => 'needs_revision', 'label' => 'Perlu revisi'],
                ['value' => 'verified', 'label' => 'Terverifikasi'],
                ['value' => 'void', 'label' => 'Dibatalkan'],
            ],
            'types' => [
                ['value' => 'competition', 'label' => 'Lomba/Kompetisi'],
                ['value' => 'award', 'label' => 'Penghargaan'],
                ['value' => 'publication', 'label' => 'Publikasi'],
                ['value' => 'delegation', 'label' => 'Delegasi'],
                ['value' => 'other', 'label' => 'Lainnya'],
            ],
        ];
    }
}
