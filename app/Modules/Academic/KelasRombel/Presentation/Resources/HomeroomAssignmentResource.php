<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Presentation\Resources;

use App\Modules\Academic\KelasRombel\Application\DTO\HomeroomAssignmentData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin HomeroomAssignmentData */
final class HomeroomAssignmentResource extends JsonResource
{
    /** @return array<string, string|null> */
    public function toArray(Request $request): array
    {
        /** @var HomeroomAssignmentData $homeroom */
        $homeroom = $this->resource;

        return [
            'id' => $homeroom->id,
            'class_group_id' => $homeroom->classGroupId,
            'employee_id' => $homeroom->employeeId,
            'employee_name' => $homeroom->employeeName,
            'assigned_on' => $homeroom->assignedOn,
            'ended_on' => $homeroom->endedOn,
            'status' => $homeroom->status,
            'reason' => $homeroom->reason,
            'created_at' => $homeroom->createdAt,
            'updated_at' => $homeroom->updatedAt,
        ];
    }
}
