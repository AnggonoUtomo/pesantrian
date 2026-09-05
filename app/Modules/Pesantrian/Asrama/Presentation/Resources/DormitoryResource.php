<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Presentation\Resources;

use App\Modules\Pesantrian\Asrama\Application\DTO\DormitoryData;
use App\Modules\Pesantrian\Asrama\Application\DTO\DormitoryRoomData;
use App\Modules\Pesantrian\Asrama\Application\DTO\DormitorySupervisorAssignmentData;
use App\Modules\Pesantrian\Asrama\Application\DTO\ReferenceData;
use App\Modules\Pesantrian\Asrama\Application\DTO\StudentRoomPlacementData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin DormitoryData */
final class DormitoryResource extends JsonResource
{
    public function __construct(mixed $resource, private readonly bool $includeDetails = false)
    {
        parent::__construct($resource);
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var DormitoryData $dormitory */
        $dormitory = $this->resource;

        $data = [
            'id' => $dormitory->id,
            'unit' => $this->reference($dormitory->unit),
            'code' => $dormitory->code,
            'name' => $dormitory->name,
            'gender_policy' => $dormitory->genderPolicy,
            'description' => $dormitory->description,
            'room_count' => $dormitory->roomCount,
            'capacity' => $dormitory->capacity,
            'occupied_count' => $dormitory->occupiedCount,
            'available_capacity' => $dormitory->availableCapacity,
            'status' => $dormitory->status,
            'archived_at' => $dormitory->archivedAt,
            'created_at' => $dormitory->createdAt,
            'updated_at' => $dormitory->updatedAt,
        ];

        if ($this->includeDetails) {
            $data['rooms'] = array_map(
                static fn (DormitoryRoomData $room): array => [
                    'id' => $room->id,
                    'code' => $room->code,
                    'name' => $room->name,
                    'capacity' => $room->capacity,
                    'occupied_count' => $room->occupiedCount,
                    'available_capacity' => $room->availableCapacity,
                    'status' => $room->status,
                    'archived_at' => $room->archivedAt,
                    'created_at' => $room->createdAt,
                    'updated_at' => $room->updatedAt,
                ],
                $dormitory->rooms,
            );
            $data['placements'] = array_map(
                static fn (StudentRoomPlacementData $placement): array => [
                    'id' => $placement->id,
                    'student_id' => $placement->studentId,
                    'student_no' => $placement->studentNo,
                    'student_name' => $placement->studentName,
                    'room_id' => $placement->dormitoryRoomId,
                    'room_code' => $placement->roomCode,
                    'started_at' => $placement->startedAt,
                    'ended_at' => $placement->endedAt,
                    'status' => $placement->status,
                    'reason' => $placement->reason,
                ],
                $dormitory->placements,
            );
            $data['supervisors'] = array_map(
                static fn (DormitorySupervisorAssignmentData $supervisor): array => [
                    'id' => $supervisor->id,
                    'employee_id' => $supervisor->employeeId,
                    'employee_name' => $supervisor->employeeName,
                    'role' => $supervisor->role,
                    'dormitory_id' => $supervisor->dormitoryId,
                    'dormitory_room_id' => $supervisor->dormitoryRoomId,
                    'room_code' => $supervisor->roomCode,
                    'started_at' => $supervisor->startedAt,
                    'ended_at' => $supervisor->endedAt,
                    'status' => $supervisor->status,
                    'reason' => $supervisor->reason,
                ],
                $dormitory->supervisors,
            );
        }

        return $data;
    }

    /** @return array{id: string, code: string, name: string} */
    private function reference(ReferenceData $reference): array
    {
        return [
            'id' => $reference->id,
            'code' => $reference->code,
            'name' => $reference->name,
        ];
    }
}
