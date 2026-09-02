<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Presentation\Resources;

use App\Modules\Academic\KelasRombel\Application\DTO\ClassLevelData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ClassLevelData */
final class ClassLevelResource extends JsonResource
{
    /** @return array<string, string|int|null> */
    public function toArray(Request $request): array
    {
        /** @var ClassLevelData $level */
        $level = $this->resource;

        return [
            'id' => $level->id,
            'unit_id' => $level->unitId,
            'code' => $level->code,
            'name' => $level->name,
            'sequence' => $level->sequence,
            'status' => $level->status,
            'created_at' => $level->createdAt,
            'updated_at' => $level->updatedAt,
        ];
    }
}
