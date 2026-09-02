<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Presentation\Resources;

use App\Modules\Academic\KelasRombel\Application\DTO\StudentPlacementData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin StudentPlacementData */
final class StudentPlacementResource extends JsonResource
{
    /** @return array<string, string|null> */
    public function toArray(Request $request): array
    {
        /** @var StudentPlacementData $placement */
        $placement = $this->resource;

        return [
            'id' => $placement->id,
            'class_group_id' => $placement->classGroupId,
            'academic_term_id' => $placement->academicTermId,
            'student_id' => $placement->studentId,
            'student_no' => $placement->studentNo,
            'joined_on' => $placement->joinedOn,
            'left_on' => $placement->leftOn,
            'status' => $placement->status,
            'reason' => $placement->reason,
            'created_at' => $placement->createdAt,
            'updated_at' => $placement->updatedAt,
        ];
    }
}
