<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Presentation\Resources;

use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCategoryData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin StudentDisciplineCategoryData */
final class StudentDisciplineCategoryResource extends JsonResource
{
    public function __construct(StudentDisciplineCategoryData $resource)
    {
        parent::__construct($resource);
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return $this->resource->toArray();
    }
}
