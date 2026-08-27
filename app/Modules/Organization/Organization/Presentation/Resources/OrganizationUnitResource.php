<?php

declare(strict_types=1);

namespace App\Modules\Organization\Organization\Presentation\Resources;

use App\Modules\Organization\Organization\Application\DTO\OrganizationUnitData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin OrganizationUnitData */
final class OrganizationUnitResource extends JsonResource
{
    /** @return array<string, string|null> */
    public function toArray(Request $request): array
    {
        /** @var OrganizationUnitData $unit */
        $unit = $this->resource;

        return $unit->toArray();
    }
}
