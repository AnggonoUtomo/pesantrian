<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod\Presentation\Resources;

use App\Modules\Academic\AcademicPeriod\Application\DTO\AcademicYearData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AcademicYearData */
final class AcademicYearResource extends JsonResource
{
    /** @return array<string, string|null> */
    public function toArray(Request $request): array
    {
        /** @var AcademicYearData $year */
        $year = $this->resource;

        return $year->toArray();
    }
}
