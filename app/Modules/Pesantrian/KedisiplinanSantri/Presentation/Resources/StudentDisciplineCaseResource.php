<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Presentation\Resources;

use App\Modules\Pesantrian\KedisiplinanSantri\Application\DTO\StudentDisciplineCaseData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin StudentDisciplineCaseData */
final class StudentDisciplineCaseResource extends JsonResource
{
    public function __construct(StudentDisciplineCaseData $resource, private readonly bool $includeRevisions = true)
    {
        parent::__construct($resource);
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return $this->resource->toArray($this->includeRevisions);
    }
}
