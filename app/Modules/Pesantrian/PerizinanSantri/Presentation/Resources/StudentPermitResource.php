<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PerizinanSantri\Presentation\Resources;

use App\Modules\Pesantrian\PerizinanSantri\Application\DTO\StudentPermitData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin StudentPermitData */
final class StudentPermitResource extends JsonResource
{
    public function __construct(StudentPermitData $resource, private readonly bool $includeRevisions = true)
    {
        parent::__construct($resource);
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return $this->resource->toArray($this->includeRevisions);
    }
}
