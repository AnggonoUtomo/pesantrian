<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Presentation\Resources;

use App\Modules\Pesantrian\Asrama\Application\DTO\DormitorySupervisorAssignmentData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin DormitorySupervisorAssignmentData */
final class DormitorySupervisorAssignmentResource extends JsonResource
{
    /** @return array<string, string|null> */
    public function toArray(Request $request): array
    {
        /** @var DormitorySupervisorAssignmentData $assignment */
        $assignment = $this->resource;

        return [
            'id' => $assignment->id,
            'employee_id' => $assignment->employeeId,
            'employee_name' => $assignment->employeeName,
            'role' => $assignment->role,
            'dormitory_id' => $assignment->dormitoryId,
            'dormitory_room_id' => $assignment->dormitoryRoomId,
            'room_code' => $assignment->roomCode,
            'started_at' => $assignment->startedAt,
            'ended_at' => $assignment->endedAt,
            'status' => $assignment->status,
            'reason' => $assignment->reason,
        ];
    }
}
