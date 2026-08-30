<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PenerimaanSantri\Presentation\Resources;

use App\Modules\Pesantrian\PenerimaanSantri\Application\DTO\StudentAdmissionData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin StudentAdmissionData */
final class StudentAdmissionResource extends JsonResource
{
    public function __construct(StudentAdmissionData $resource)
    {
        parent::__construct($resource);
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return $this->resource->toArray();
    }
}
