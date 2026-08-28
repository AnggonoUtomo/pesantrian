<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod\Presentation\Resources;

use App\Modules\Academic\AcademicPeriod\Application\DTO\AcademicTermData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AcademicTermData */
final class AcademicTermResource extends JsonResource
{
    /** @return array<string, string|int|bool|null> */
    public function toArray(Request $request): array
    {
        /** @var AcademicTermData $term */
        $term = $this->resource;

        return $term->toArray();
    }
}
