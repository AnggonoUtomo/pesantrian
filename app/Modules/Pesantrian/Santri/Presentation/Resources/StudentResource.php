<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Santri\Presentation\Resources;

use App\Modules\Pesantrian\Santri\Application\DTO\StudentData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin StudentData */
final class StudentResource extends JsonResource
{
    public function __construct(StudentData $resource, private readonly bool $includeGuardians = true)
    {
        parent::__construct($resource);
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return $this->resource->toArray($this->includeGuardians);
    }
}
