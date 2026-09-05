<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PresensiSantri\Presentation\Resources;

use App\Modules\Pesantrian\PresensiSantri\Application\DTO\StudentAttendanceData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin StudentAttendanceData */
final class StudentAttendanceResource extends JsonResource
{
    public function __construct(StudentAttendanceData $resource, private readonly bool $includeEntries = true)
    {
        parent::__construct($resource);
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return $this->resource->toArray($this->includeEntries);
    }
}
