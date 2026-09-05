<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Presentation\Controllers;

use App\Http\ApiResponseFactory;
use App\Modules\HumanResource\HumanResource\Application\Contracts\ActiveEmployeeReader;
use App\Modules\HumanResource\HumanResource\Application\DTO\ActiveEmployeeOptionData;
use App\Modules\Organization\Organization\Application\Contracts\DormitoryUnitReader;
use App\Modules\Organization\Organization\Application\DTO\DormitoryUnitOptionData;
use App\Modules\Pesantrian\Asrama\Application\Actions\ArchiveDormitory;
use App\Modules\Pesantrian\Asrama\Application\Actions\ArchiveDormitoryRoom;
use App\Modules\Pesantrian\Asrama\Application\Actions\AssignDormitorySupervisor;
use App\Modules\Pesantrian\Asrama\Application\Actions\CreateDormitory;
use App\Modules\Pesantrian\Asrama\Application\Actions\CreateDormitoryRoom;
use App\Modules\Pesantrian\Asrama\Application\Actions\EndDormitorySupervisor;
use App\Modules\Pesantrian\Asrama\Application\Actions\PlaceStudentInRoom;
use App\Modules\Pesantrian\Asrama\Application\Actions\RemoveStudentRoomPlacement;
use App\Modules\Pesantrian\Asrama\Application\Actions\RestoreDormitory;
use App\Modules\Pesantrian\Asrama\Application\Actions\RestoreDormitoryRoom;
use App\Modules\Pesantrian\Asrama\Application\Actions\TransferStudentRoom;
use App\Modules\Pesantrian\Asrama\Application\Actions\UpdateDormitory;
use App\Modules\Pesantrian\Asrama\Application\Actions\UpdateDormitoryRoom;
use App\Modules\Pesantrian\Asrama\Application\DTO\DormitoryData;
use App\Modules\Pesantrian\Asrama\Application\DTO\PaginatedDormitoryData;
use App\Modules\Pesantrian\Asrama\Application\Exceptions\AsramaPlacementException;
use App\Modules\Pesantrian\Asrama\Application\Exceptions\AsramaSupervisorException;
use App\Modules\Pesantrian\Asrama\Application\Queries\ListDormitories;
use App\Modules\Pesantrian\Asrama\Application\Queries\ShowDormitory;
use App\Modules\Pesantrian\Asrama\Presentation\Requests\ArchiveDormitoryApiRequest;
use App\Modules\Pesantrian\Asrama\Presentation\Requests\AssignDormitorySupervisorApiRequest;
use App\Modules\Pesantrian\Asrama\Presentation\Requests\EndDormitorySupervisorApiRequest;
use App\Modules\Pesantrian\Asrama\Presentation\Requests\ListDormitoriesApiRequest;
use App\Modules\Pesantrian\Asrama\Presentation\Requests\PlaceStudentRoomApiRequest;
use App\Modules\Pesantrian\Asrama\Presentation\Requests\RemoveStudentRoomPlacementApiRequest;
use App\Modules\Pesantrian\Asrama\Presentation\Requests\StoreDormitoryApiRequest;
use App\Modules\Pesantrian\Asrama\Presentation\Requests\StoreDormitoryRoomApiRequest;
use App\Modules\Pesantrian\Asrama\Presentation\Requests\TransferStudentRoomApiRequest;
use App\Modules\Pesantrian\Asrama\Presentation\Requests\UpdateDormitoryApiRequest;
use App\Modules\Pesantrian\Asrama\Presentation\Requests\UpdateDormitoryRoomApiRequest;
use App\Modules\Pesantrian\Asrama\Presentation\Resources\DormitoryResource;
use App\Modules\Pesantrian\Santri\Application\Contracts\ActiveStudentReader;
use App\Modules\Pesantrian\Santri\Application\DTO\ActiveStudentOptionData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

final readonly class DormitoryController implements HasMiddleware
{
    public function __construct(
        private ListDormitories $listDormitories,
        private ShowDormitory $showDormitory,
        private CreateDormitory $createDormitory,
        private UpdateDormitory $updateDormitory,
        private CreateDormitoryRoom $createDormitoryRoom,
        private UpdateDormitoryRoom $updateDormitoryRoom,
        private PlaceStudentInRoom $placeStudent,
        private TransferStudentRoom $transferStudentRoom,
        private RemoveStudentRoomPlacement $removeStudentRoomPlacement,
        private AssignDormitorySupervisor $assignDormitorySupervisor,
        private EndDormitorySupervisor $endDormitorySupervisor,
        private ArchiveDormitory $archiveDormitory,
        private RestoreDormitory $restoreDormitory,
        private ArchiveDormitoryRoom $archiveDormitoryRoom,
        private RestoreDormitoryRoom $restoreDormitoryRoom,
        private DormitoryUnitReader $dormitoryUnits,
        private ActiveStudentReader $students,
        private ActiveEmployeeReader $employees,
        private ApiResponseFactory $responses,
    ) {}

    public static function middleware(): array
    {
        return [
            new Middleware('can:asrama.view', only: ['index', 'show']),
            new Middleware('can:asrama.manage', only: ['store', 'update', 'storeRoom', 'updateRoom']),
            new Middleware('can:asrama.placement', only: ['storePlacement', 'transferPlacement', 'removePlacement']),
            new Middleware('can:asrama.supervisor', only: ['storeSupervisor', 'endSupervisor']),
            new Middleware('can:asrama.archive', only: ['archive', 'restore', 'archiveRoom', 'restoreRoom']),
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
            'options' => [
                'students' => $this->studentOptions(),
                'employees' => $this->employeeOptions(),
            ],
            'canManage' => $request->user()?->can('asrama.manage') === true,
            'canPlacement' => $request->user()?->can('asrama.placement') === true,
            'canSupervisor' => $request->user()?->can('asrama.supervisor') === true,
            'canArchive' => $request->user()?->can('asrama.archive') === true,
        ]);
    }

    public function store(StoreDormitoryApiRequest $request): RedirectResponse
    {
        $dormitory = $this->createDormitory->execute($request->user(), $request->toData(), $this->responses->correlationId($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Asrama berhasil dibuat.']);

        return to_route('pesantrian.asrama.show', $dormitory->id);
    }

    public function update(UpdateDormitoryApiRequest $request, string $dormitory): RedirectResponse
    {
        $updated = $this->updateDormitory->execute($request->user(), $dormitory, $request->changes(), $this->responses->correlationId($request));

        abort_if($updated === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Asrama berhasil diperbarui.']);

        return to_route('pesantrian.asrama.show', $dormitory);
    }

    public function storeRoom(StoreDormitoryRoomApiRequest $request, string $dormitory): RedirectResponse
    {
        $this->createDormitoryRoom->execute($request->user(), $request->toData(), $this->responses->correlationId($request));

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Kamar berhasil dibuat.']);

        return to_route('pesantrian.asrama.show', $dormitory);
    }

    public function updateRoom(UpdateDormitoryRoomApiRequest $request, string $dormitory, string $room): RedirectResponse
    {
        $updated = $this->updateDormitoryRoom->execute($request->user(), $dormitory, $room, $request->changes(), $this->responses->correlationId($request));

        abort_if($updated === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Kamar berhasil diperbarui.']);

        return to_route('pesantrian.asrama.show', $dormitory);
    }

    public function storePlacement(PlaceStudentRoomApiRequest $request, string $dormitory): RedirectResponse
    {
        try {
            $placement = $this->placeStudent->execute(
                $request->user(),
                $dormitory,
                (string) $request->validated('student_id'),
                (string) $request->validated('dormitory_room_id'),
                (string) $request->validated('started_at'),
                $this->responses->correlationId($request),
            );
        } catch (AsramaPlacementException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        abort_if($placement === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Santri berhasil ditempatkan ke kamar.']);

        return to_route('pesantrian.asrama.show', $dormitory);
    }

    public function transferPlacement(TransferStudentRoomApiRequest $request, string $dormitory, string $placement): RedirectResponse
    {
        try {
            $transfer = $this->transferStudentRoom->execute(
                $request->user(),
                $dormitory,
                $placement,
                (string) $request->validated('target_room_id'),
                (string) $request->validated('started_at'),
                (string) $request->validated('reason'),
                $this->responses->correlationId($request),
            );
        } catch (AsramaPlacementException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        abort_if($transfer === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Santri berhasil dipindahkan kamar.']);

        return to_route('pesantrian.asrama.show', $dormitory);
    }

    public function removePlacement(RemoveStudentRoomPlacementApiRequest $request, string $dormitory, string $placement): RedirectResponse
    {
        try {
            $removed = $this->removeStudentRoomPlacement->execute(
                $request->user(),
                $dormitory,
                $placement,
                (string) $request->validated('ended_at'),
                (string) $request->validated('reason'),
                $this->responses->correlationId($request),
            );
        } catch (AsramaPlacementException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        abort_if($removed === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Santri berhasil dikeluarkan dari kamar.']);

        return to_route('pesantrian.asrama.show', $dormitory);
    }

    public function storeSupervisor(AssignDormitorySupervisorApiRequest $request, string $dormitory): RedirectResponse
    {
        try {
            $assignment = $this->assignDormitorySupervisor->execute(
                $request->user(),
                $dormitory,
                (string) $request->validated('employee_id'),
                $request->validated('dormitory_room_id') === null ? null : (string) $request->validated('dormitory_room_id'),
                (string) $request->validated('role'),
                (string) $request->validated('started_at'),
                $this->responses->correlationId($request),
            );
        } catch (AsramaSupervisorException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        abort_if($assignment === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Musyrif berhasil ditugaskan.']);

        return to_route('pesantrian.asrama.show', $dormitory);
    }

    public function endSupervisor(EndDormitorySupervisorApiRequest $request, string $dormitory, string $assignment): RedirectResponse
    {
        try {
            $ended = $this->endDormitorySupervisor->execute(
                $request->user(),
                $dormitory,
                $assignment,
                (string) $request->validated('ended_at'),
                (string) $request->validated('reason'),
                $this->responses->correlationId($request),
            );
        } catch (AsramaSupervisorException $exception) {
            throw ValidationException::withMessages($exception->errors());
        }

        abort_if($ended === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Tugas musyrif berhasil diakhiri.']);

        return to_route('pesantrian.asrama.show', $dormitory);
    }

    public function archive(ArchiveDormitoryApiRequest $request, string $dormitory): RedirectResponse
    {
        $archived = $this->archiveDormitory->execute($request->user(), $dormitory, (string) $request->validated('reason'), $this->responses->correlationId($request));

        abort_if($archived === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Asrama berhasil diarsipkan.']);

        return to_route('pesantrian.asrama.index');
    }

    public function restore(Request $request, string $dormitory): RedirectResponse
    {
        $restored = $this->restoreDormitory->execute($request->user(), $dormitory, $this->responses->correlationId($request));

        abort_if($restored === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Asrama berhasil dipulihkan.']);

        return to_route('pesantrian.asrama.show', $dormitory);
    }

    public function archiveRoom(ArchiveDormitoryApiRequest $request, string $dormitory, string $room): RedirectResponse
    {
        $updated = $this->archiveDormitoryRoom->execute($request->user(), $dormitory, $room, (string) $request->validated('reason'), $this->responses->correlationId($request));

        abort_if($updated === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Kamar berhasil diarsipkan.']);

        return to_route('pesantrian.asrama.show', $dormitory);
    }

    public function restoreRoom(Request $request, string $dormitory, string $room): RedirectResponse
    {
        $updated = $this->restoreDormitoryRoom->execute($request->user(), $dormitory, $room, $this->responses->correlationId($request));

        abort_if($updated === null, 404);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Kamar berhasil dipulihkan.']);

        return to_route('pesantrian.asrama.show', $dormitory);
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
        $options = [];

        foreach ($this->dormitoryUnits->options(limit: 200) as $record) {
            /** @var DormitoryUnitOptionData $record */
            $options[] = [
                'id' => $record->id,
                'code' => $record->code,
                'name' => $record->name,
            ];
        }

        return $options;
    }

    /**
     * @return list<array{id: string, code: string, name: string}>
     */
    private function studentOptions(): array
    {
        $options = [];

        foreach ($this->students->options(limit: 200) as $record) {
            /** @var ActiveStudentOptionData $record */
            $options[] = [
                'id' => $record->id,
                'code' => $record->studentNo,
                'name' => $record->fullName,
            ];
        }

        return $options;
    }

    /**
     * @return list<array{id: string, code: string, name: string}>
     */
    private function employeeOptions(): array
    {
        $options = [];

        foreach ($this->employees->options(employmentType: 'teacher', limit: 200) as $record) {
            /** @var ActiveEmployeeOptionData $record */
            $options[] = [
                'id' => $record->id,
                'code' => $record->employeeNo,
                'name' => $record->name,
            ];
        }

        return $options;
    }
}
