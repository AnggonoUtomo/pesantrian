<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Infrastructure\Repositories;

use App\Modules\Pesantrian\Asrama\Application\Contracts\AsramaMutationRepository;
use App\Modules\Pesantrian\Asrama\Application\Contracts\AsramaReadRepository;
use App\Modules\Pesantrian\Asrama\Application\DTO\AssignDormitorySupervisorData;
use App\Modules\Pesantrian\Asrama\Application\DTO\DormitoryData;
use App\Modules\Pesantrian\Asrama\Application\DTO\DormitoryListFilter;
use App\Modules\Pesantrian\Asrama\Application\DTO\DormitoryRoomData;
use App\Modules\Pesantrian\Asrama\Application\DTO\DormitoryRoomPlacementContextData;
use App\Modules\Pesantrian\Asrama\Application\DTO\DormitorySupervisorAssignmentData;
use App\Modules\Pesantrian\Asrama\Application\DTO\PaginatedDormitoryData;
use App\Modules\Pesantrian\Asrama\Application\DTO\PlaceStudentRoomData;
use App\Modules\Pesantrian\Asrama\Application\DTO\ReferenceData;
use App\Modules\Pesantrian\Asrama\Application\DTO\StudentRoomPlacementData;
use App\Modules\Pesantrian\Asrama\Application\DTO\StudentRoomTransferData;
use App\Modules\Pesantrian\Asrama\Application\DTO\UpsertDormitoryData;
use App\Modules\Pesantrian\Asrama\Application\DTO\UpsertDormitoryRoomData;
use App\Modules\Pesantrian\Asrama\Infrastructure\Models\DormitoryRecord;
use App\Modules\Pesantrian\Asrama\Infrastructure\Models\DormitoryRoomRecord;
use App\Modules\Pesantrian\Asrama\Infrastructure\Models\DormitorySupervisorAssignmentRecord;
use App\Modules\Pesantrian\Asrama\Infrastructure\Models\StudentRoomPlacementRecord;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class EloquentAsramaReadRepository implements AsramaMutationRepository, AsramaReadRepository
{
    public function paginateDormitories(DormitoryListFilter $filter): PaginatedDormitoryData
    {
        $query = $this->baseQuery()
            ->when($filter->search !== null, function (Builder $query) use ($filter): void {
                $query->where(function (Builder $query) use ($filter): void {
                    $query->where('dormitories.name', 'like', '%'.$filter->search.'%')
                        ->orWhere('dormitories.code', 'like', '%'.$filter->search.'%')
                        ->orWhere('organization_units.name', 'like', '%'.$filter->search.'%');
                });
            })
            ->when($filter->unitId !== null, fn (Builder $query) => $query->where('dormitories.unit_id', $filter->unitId))
            ->when($filter->genderPolicy !== null, fn (Builder $query) => $query->where('dormitories.gender_policy', $filter->genderPolicy))
            ->when($filter->status !== null, fn (Builder $query) => $query->where('dormitories.status', $filter->status))
            ->when(
                $filter->archived === 'archived',
                fn (Builder $query) => $query->whereNotNull('dormitories.archived_at'),
                fn (Builder $query) => $query->whereNull('dormitories.archived_at'),
            )
            ->orderBy($this->qualifiedSortField($filter->sortField), $filter->sortDirection === 'desc' ? 'desc' : 'asc');

        /** @var LengthAwarePaginator<int, DormitoryRecord> $page */
        $page = $query->paginate($filter->perPage, ['dormitories.*'], 'page', $filter->page);

        return new PaginatedDormitoryData(
            data: array_map(
                fn (DormitoryRecord $record): DormitoryData => $this->map($record),
                array_values($page->items()),
            ),
            currentPage: $page->currentPage(),
            perPage: $page->perPage(),
            total: $page->total(),
            lastPage: $page->lastPage(),
        );
    }

    public function findDormitory(string $id): ?DormitoryData
    {
        $record = $this->baseQuery()->find($id, ['dormitories.*']);

        if (! $record instanceof DormitoryRecord) {
            return null;
        }

        return $this->map($record, includeDetails: true);
    }

    public function createDormitory(UpsertDormitoryData $data): DormitoryData
    {
        /** @var DormitoryRecord $record */
        $record = DormitoryRecord::query()->create($data->toArray());

        return $this->freshDormitoryData($record);
    }

    public function updateDormitory(string $id, array $changes): ?DormitoryData
    {
        $record = DormitoryRecord::query()->find($id);

        if (! $record instanceof DormitoryRecord) {
            return null;
        }

        $record->fill($changes);
        $record->save();

        return $this->freshDormitoryData($record);
    }

    public function createRoom(UpsertDormitoryRoomData $data): DormitoryData
    {
        /** @var DormitoryRoomRecord $room */
        $room = DormitoryRoomRecord::query()->create($data->toArray());

        return $this->freshDormitoryDataById($room->dormitory_id);
    }

    public function updateRoom(string $dormitoryId, string $roomId, array $changes): ?DormitoryData
    {
        $room = DormitoryRoomRecord::query()
            ->where('dormitory_id', $dormitoryId)
            ->find($roomId);

        if (! $room instanceof DormitoryRoomRecord) {
            return null;
        }

        $room->fill($changes);
        $room->save();

        return $this->freshDormitoryDataById($dormitoryId);
    }

    public function findRoomForPlacement(string $roomId): ?DormitoryRoomPlacementContextData
    {
        $room = DormitoryRoomRecord::query()
            ->join('dormitories', 'dormitories.id', '=', 'dormitory_rooms.dormitory_id')
            ->where('dormitory_rooms.id', $roomId)
            ->select('dormitory_rooms.*')
            ->addSelect([
                DB::raw('dormitories.id as placement_dormitory_id'),
                DB::raw('dormitories.unit_id as placement_dormitory_unit_id'),
                DB::raw('dormitories.status as placement_dormitory_status'),
                DB::raw('dormitories.gender_policy as placement_gender_policy'),
                DB::raw('dormitories.archived_at as placement_dormitory_archived_at'),
                DB::raw("(select count(*) from student_room_placements where student_room_placements.dormitory_room_id = dormitory_rooms.id and student_room_placements.status = 'active' and student_room_placements.active_student_key is not null) as occupied_count"),
            ])
            ->first();

        if (! $room instanceof DormitoryRoomRecord) {
            return null;
        }

        $dormitoryArchivedAt = $room->getAttribute('placement_dormitory_archived_at');

        return new DormitoryRoomPlacementContextData(
            dormitoryId: (string) $room->getAttribute('placement_dormitory_id'),
            dormitoryUnitId: (string) $room->getAttribute('placement_dormitory_unit_id'),
            dormitoryStatus: (string) $room->getAttribute('placement_dormitory_status'),
            genderPolicy: (string) $room->getAttribute('placement_gender_policy'),
            dormitoryArchivedAt: $dormitoryArchivedAt === null ? null : Carbon::parse($dormitoryArchivedAt)->toJSON(),
            roomId: (string) $room->getKey(),
            roomCode: (string) $room->code,
            capacity: (int) $room->capacity,
            occupiedCount: (int) $room->getAttribute('occupied_count'),
            roomStatus: (string) $room->status,
            roomArchivedAt: $room->archived_at?->toJSON(),
        );
    }

    public function findPlacement(string $id): ?StudentRoomPlacementData
    {
        $record = $this->placementQuery()
            ->where('student_room_placements.id', $id)
            ->first();

        return $record instanceof StudentRoomPlacementRecord ? $this->mapPlacement($record) : null;
    }

    public function findActivePlacementForStudent(string $studentId): ?StudentRoomPlacementData
    {
        $record = $this->placementQuery()
            ->where('student_room_placements.student_id', $studentId)
            ->where('student_room_placements.status', 'active')
            ->whereNotNull('student_room_placements.active_student_key')
            ->first();

        return $record instanceof StudentRoomPlacementRecord ? $this->mapPlacement($record) : null;
    }

    public function placeStudent(PlaceStudentRoomData $data): StudentRoomPlacementData
    {
        /** @var StudentRoomPlacementRecord $record */
        $record = StudentRoomPlacementRecord::query()->create($data->toArray());

        return $this->freshPlacementData((string) $record->getKey());
    }

    public function transferStudent(string $placementId, PlaceStudentRoomData $target, string $reason): ?StudentRoomTransferData
    {
        $previous = StudentRoomPlacementRecord::query()->find($placementId);

        if (! $previous instanceof StudentRoomPlacementRecord || $previous->status !== 'active') {
            return null;
        }

        $previous->forceFill([
            'ended_at' => $target->startedAt,
            'status' => 'moved',
            'reason' => $reason,
            'active_student_key' => null,
        ])->save();

        /** @var StudentRoomPlacementRecord $current */
        $current = StudentRoomPlacementRecord::query()->create($target->toArray());

        return new StudentRoomTransferData(
            previous: $this->freshPlacementData((string) $previous->getKey()),
            current: $this->freshPlacementData((string) $current->getKey()),
        );
    }

    public function removeStudent(string $placementId, string $endedAt, string $reason, ?string $actorId): ?StudentRoomPlacementData
    {
        $record = StudentRoomPlacementRecord::query()->find($placementId);

        if (! $record instanceof StudentRoomPlacementRecord || $record->status !== 'active') {
            return null;
        }

        $record->forceFill([
            'ended_at' => $endedAt,
            'status' => 'inactive',
            'reason' => $reason,
            'active_student_key' => null,
            'ended_by' => $actorId,
        ])->save();

        return $this->freshPlacementData((string) $record->getKey());
    }

    public function findSupervisorAssignment(string $id): ?DormitorySupervisorAssignmentData
    {
        $record = $this->supervisorQuery()
            ->where('dormitory_supervisor_assignments.id', $id)
            ->first();

        return $record instanceof DormitorySupervisorAssignmentRecord ? $this->mapSupervisor($record) : null;
    }

    public function findActiveSupervisorForScope(string $employeeId, string $dormitoryId, ?string $roomId): ?DormitorySupervisorAssignmentData
    {
        $record = $this->supervisorQuery()
            ->where('dormitory_supervisor_assignments.employee_id', $employeeId)
            ->where('dormitory_supervisor_assignments.dormitory_id', $dormitoryId)
            ->where('dormitory_supervisor_assignments.status', 'active')
            ->whereNull('dormitory_supervisor_assignments.ended_at')
            ->when(
                $roomId === null,
                static fn (Builder $query) => $query->whereNull('dormitory_supervisor_assignments.dormitory_room_id'),
                static fn (Builder $query) => $query->where('dormitory_supervisor_assignments.dormitory_room_id', $roomId),
            )
            ->first();

        return $record instanceof DormitorySupervisorAssignmentRecord ? $this->mapSupervisor($record) : null;
    }

    public function assignSupervisor(AssignDormitorySupervisorData $data): DormitorySupervisorAssignmentData
    {
        /** @var DormitorySupervisorAssignmentRecord $record */
        $record = DormitorySupervisorAssignmentRecord::query()->create($data->toArray());

        return $this->freshSupervisorData((string) $record->getKey());
    }

    public function endSupervisor(string $assignmentId, string $endedAt, string $reason): ?DormitorySupervisorAssignmentData
    {
        $record = DormitorySupervisorAssignmentRecord::query()->find($assignmentId);

        if (! $record instanceof DormitorySupervisorAssignmentRecord || $record->status !== 'active') {
            return null;
        }

        $record->forceFill([
            'ended_at' => $endedAt,
            'status' => 'ended',
            'reason' => $reason,
        ])->save();

        return $this->freshSupervisorData((string) $record->getKey());
    }

    public function archiveDormitory(string $id, ?string $actorId): ?DormitoryData
    {
        $record = DormitoryRecord::query()
            ->whereKey($id)
            ->whereNull('archived_at')
            ->first();

        if (! $record instanceof DormitoryRecord) {
            return null;
        }

        $record->forceFill([
            'status' => 'inactive',
            'archived_at' => now(),
            'archived_by' => $actorId,
        ])->save();

        return $this->freshDormitoryData($record);
    }

    public function restoreDormitory(string $id): ?DormitoryData
    {
        $record = DormitoryRecord::query()
            ->whereKey($id)
            ->whereNotNull('archived_at')
            ->first();

        if (! $record instanceof DormitoryRecord) {
            return null;
        }

        $record->forceFill([
            'status' => 'active',
            'archived_at' => null,
            'archived_by' => null,
        ])->save();

        return $this->freshDormitoryData($record);
    }

    public function archiveRoom(string $dormitoryId, string $roomId, ?string $actorId): ?DormitoryData
    {
        $room = DormitoryRoomRecord::query()
            ->where('dormitory_id', $dormitoryId)
            ->whereKey($roomId)
            ->whereNull('archived_at')
            ->first();

        if (! $room instanceof DormitoryRoomRecord) {
            return null;
        }

        $room->forceFill([
            'status' => 'inactive',
            'archived_at' => now(),
            'archived_by' => $actorId,
        ])->save();

        return $this->freshDormitoryDataById($dormitoryId);
    }

    public function restoreRoom(string $dormitoryId, string $roomId): ?DormitoryData
    {
        $room = DormitoryRoomRecord::query()
            ->where('dormitory_id', $dormitoryId)
            ->whereKey($roomId)
            ->whereNotNull('archived_at')
            ->first();

        if (! $room instanceof DormitoryRoomRecord) {
            return null;
        }

        $room->forceFill([
            'status' => 'active',
            'archived_at' => null,
            'archived_by' => null,
        ])->save();

        return $this->freshDormitoryDataById($dormitoryId);
    }

    /** @return Builder<DormitoryRecord> */
    private function baseQuery(): Builder
    {
        return DormitoryRecord::query()
            ->leftJoin('organization_units', 'organization_units.id', '=', 'dormitories.unit_id')
            ->select('dormitories.*')
            ->addSelect([
                DB::raw('organization_units.code as unit_code'),
                DB::raw('organization_units.name as unit_name'),
                DB::raw('(select count(*) from dormitory_rooms where dormitory_rooms.dormitory_id = dormitories.id) as room_count'),
                DB::raw('(select coalesce(sum(dormitory_rooms.capacity), 0) from dormitory_rooms where dormitory_rooms.dormitory_id = dormitories.id) as total_capacity'),
                DB::raw("(select count(*) from student_room_placements inner join dormitory_rooms as occupied_rooms on occupied_rooms.id = student_room_placements.dormitory_room_id where occupied_rooms.dormitory_id = dormitories.id and student_room_placements.status = 'active' and student_room_placements.active_student_key is not null) as occupied_count"),
            ]);
    }

    private function map(DormitoryRecord $record, bool $includeDetails = false): DormitoryData
    {
        $capacity = (int) $record->getAttribute('total_capacity');
        $occupiedCount = (int) $record->getAttribute('occupied_count');

        return new DormitoryData(
            id: (string) $record->getKey(),
            unit: new ReferenceData(
                id: (string) $record->unit_id,
                code: (string) $record->getAttribute('unit_code'),
                name: (string) $record->getAttribute('unit_name'),
            ),
            code: (string) $record->code,
            name: (string) $record->name,
            genderPolicy: (string) $record->gender_policy,
            description: $record->description === null ? null : (string) $record->description,
            roomCount: (int) $record->getAttribute('room_count'),
            capacity: $capacity,
            occupiedCount: $occupiedCount,
            availableCapacity: max(0, $capacity - $occupiedCount),
            status: (string) $record->status,
            archivedAt: $record->archived_at?->toJSON(),
            createdAt: $record->created_at->toJSON(),
            updatedAt: $record->updated_at->toJSON(),
            rooms: $includeDetails ? $this->roomsFor($record) : [],
            placements: $includeDetails ? $this->placementsFor($record) : [],
            supervisors: $includeDetails ? $this->supervisorsFor($record) : [],
        );
    }

    /** @return list<DormitoryRoomData> */
    private function roomsFor(DormitoryRecord $record): array
    {
        return array_values(DormitoryRoomRecord::query()
            ->where('dormitory_id', $record->id)
            ->select('dormitory_rooms.*')
            ->addSelect([
                DB::raw("(select count(*) from student_room_placements where student_room_placements.dormitory_room_id = dormitory_rooms.id and student_room_placements.status = 'active' and student_room_placements.active_student_key is not null) as occupied_count"),
            ])
            ->orderBy('code')
            ->get()
            ->map(static function (DormitoryRoomRecord $room): DormitoryRoomData {
                $capacity = (int) $room->capacity;
                $occupiedCount = (int) $room->getAttribute('occupied_count');

                return new DormitoryRoomData(
                    id: (string) $room->getKey(),
                    code: (string) $room->code,
                    name: (string) $room->name,
                    capacity: $capacity,
                    occupiedCount: $occupiedCount,
                    availableCapacity: max(0, $capacity - $occupiedCount),
                    status: (string) $room->status,
                    archivedAt: $room->archived_at?->toJSON(),
                    createdAt: $room->created_at->toJSON(),
                    updatedAt: $room->updated_at->toJSON(),
                );
            })
            ->values()
            ->all());
    }

    /** @return list<StudentRoomPlacementData> */
    private function placementsFor(DormitoryRecord $record): array
    {
        return array_values(StudentRoomPlacementRecord::query()
            ->leftJoin('students', 'students.id', '=', 'student_room_placements.student_id')
            ->leftJoin('dormitory_rooms', 'dormitory_rooms.id', '=', 'student_room_placements.dormitory_room_id')
            ->where('dormitory_rooms.dormitory_id', $record->id)
            ->where('student_room_placements.status', 'active')
            ->whereNotNull('student_room_placements.active_student_key')
            ->orderBy('dormitory_rooms.code')
            ->orderBy('student_room_placements.started_at')
            ->get([
                'student_room_placements.*',
                'students.full_name as student_name',
                'dormitory_rooms.code as room_code',
            ])
            ->map(fn (StudentRoomPlacementRecord $placement): StudentRoomPlacementData => $this->mapPlacement($placement))
            ->values()
            ->all());
    }

    /** @return Builder<StudentRoomPlacementRecord> */
    private function placementQuery(): Builder
    {
        return StudentRoomPlacementRecord::query()
            ->leftJoin('students', 'students.id', '=', 'student_room_placements.student_id')
            ->leftJoin('dormitory_rooms', 'dormitory_rooms.id', '=', 'student_room_placements.dormitory_room_id')
            ->select([
                'student_room_placements.*',
                'students.full_name as student_name',
                'dormitory_rooms.code as room_code',
            ]);
    }

    private function mapPlacement(StudentRoomPlacementRecord $placement): StudentRoomPlacementData
    {
        return new StudentRoomPlacementData(
            id: (string) $placement->getKey(),
            studentId: (string) $placement->student_id,
            dormitoryRoomId: (string) $placement->dormitory_room_id,
            studentNo: (string) $placement->student_no,
            studentName: $placement->getAttribute('student_name') === null ? null : (string) $placement->getAttribute('student_name'),
            roomCode: $placement->getAttribute('room_code') === null ? null : (string) $placement->getAttribute('room_code'),
            startedAt: $placement->started_at->toJSON(),
            endedAt: $placement->ended_at?->toJSON(),
            status: (string) $placement->status,
            reason: $placement->reason === null ? null : (string) $placement->reason,
        );
    }

    /** @return list<DormitorySupervisorAssignmentData> */
    private function supervisorsFor(DormitoryRecord $record): array
    {
        return array_values(DormitorySupervisorAssignmentRecord::query()
            ->leftJoin('dormitory_rooms', 'dormitory_rooms.id', '=', 'dormitory_supervisor_assignments.dormitory_room_id')
            ->where(function (Builder $query) use ($record): void {
                $query->where('dormitory_supervisor_assignments.dormitory_id', $record->id)
                    ->orWhere('dormitory_rooms.dormitory_id', $record->id);
            })
            ->where('dormitory_supervisor_assignments.status', 'active')
            ->orderBy('dormitory_supervisor_assignments.started_at')
            ->get([
                'dormitory_supervisor_assignments.*',
                'dormitory_rooms.code as room_code',
            ])
            ->map(fn (DormitorySupervisorAssignmentRecord $supervisor): DormitorySupervisorAssignmentData => $this->mapSupervisor($supervisor))
            ->values()
            ->all());
    }

    /** @return Builder<DormitorySupervisorAssignmentRecord> */
    private function supervisorQuery(): Builder
    {
        return DormitorySupervisorAssignmentRecord::query()
            ->leftJoin('dormitory_rooms', 'dormitory_rooms.id', '=', 'dormitory_supervisor_assignments.dormitory_room_id')
            ->select([
                'dormitory_supervisor_assignments.*',
                'dormitory_rooms.code as room_code',
            ]);
    }

    private function mapSupervisor(DormitorySupervisorAssignmentRecord $supervisor): DormitorySupervisorAssignmentData
    {
        return new DormitorySupervisorAssignmentData(
            id: (string) $supervisor->getKey(),
            employeeId: (string) $supervisor->employee_id,
            employeeName: (string) $supervisor->employee_name,
            role: (string) $supervisor->role,
            dormitoryId: $supervisor->dormitory_id === null ? null : (string) $supervisor->dormitory_id,
            dormitoryRoomId: $supervisor->dormitory_room_id === null ? null : (string) $supervisor->dormitory_room_id,
            roomCode: $supervisor->getAttribute('room_code') === null ? null : (string) $supervisor->getAttribute('room_code'),
            startedAt: $supervisor->started_at->toJSON(),
            endedAt: $supervisor->ended_at?->toJSON(),
            status: (string) $supervisor->status,
            reason: $supervisor->reason === null ? null : (string) $supervisor->reason,
        );
    }

    private function qualifiedSortField(string $field): string
    {
        return match ($field) {
            'code' => 'dormitories.code',
            'name' => 'dormitories.name',
            'gender_policy' => 'dormitories.gender_policy',
            'capacity' => 'total_capacity',
            'occupied_count' => 'occupied_count',
            'status' => 'dormitories.status',
            default => 'dormitories.created_at',
        };
    }

    private function freshDormitoryData(DormitoryRecord $record): DormitoryData
    {
        return $this->freshDormitoryDataById((string) $record->getKey());
    }

    private function freshDormitoryDataById(string $dormitoryId): DormitoryData
    {
        $fresh = $this->findDormitory($dormitoryId);

        if (! $fresh instanceof DormitoryData) {
            throw new \RuntimeException('Asrama gagal dibaca ulang setelah mutation.');
        }

        return $fresh;
    }

    private function freshPlacementData(string $placementId): StudentRoomPlacementData
    {
        $fresh = $this->findPlacement($placementId);

        if (! $fresh instanceof StudentRoomPlacementData) {
            throw new \RuntimeException('Penempatan kamar gagal dibaca ulang setelah mutation.');
        }

        return $fresh;
    }

    private function freshSupervisorData(string $assignmentId): DormitorySupervisorAssignmentData
    {
        $fresh = $this->findSupervisorAssignment($assignmentId);

        if (! $fresh instanceof DormitorySupervisorAssignmentData) {
            throw new \RuntimeException('Penugasan musyrif gagal dibaca ulang setelah mutation.');
        }

        return $fresh;
    }
}
