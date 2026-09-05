<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Presentation\Resources;

use App\Modules\Pesantrian\Asrama\Application\DTO\StudentRoomPlacementData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin StudentRoomPlacementData */
final class StudentRoomPlacementResource extends JsonResource
{
    /** @return array<string, string|null> */
    public function toArray(Request $request): array
    {
        /** @var StudentRoomPlacementData $placement */
        $placement = $this->resource;

        return [
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
        ];
    }
}
