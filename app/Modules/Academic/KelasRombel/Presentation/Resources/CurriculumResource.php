<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Presentation\Resources;

use App\Modules\Academic\KelasRombel\Application\DTO\CurriculumData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CurriculumData */
final class CurriculumResource extends JsonResource
{
    /** @return array<string, string|null> */
    public function toArray(Request $request): array
    {
        /** @var CurriculumData $curriculum */
        $curriculum = $this->resource;

        return [
            'id' => $curriculum->id,
            'code' => $curriculum->code,
            'name' => $curriculum->name,
            'description' => $curriculum->description,
            'status' => $curriculum->status,
            'created_at' => $curriculum->createdAt,
            'updated_at' => $curriculum->updatedAt,
        ];
    }
}
