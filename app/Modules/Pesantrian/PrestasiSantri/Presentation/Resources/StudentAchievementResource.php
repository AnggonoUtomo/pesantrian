<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Presentation\Resources;

use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin StudentAchievementData */
final class StudentAchievementResource extends JsonResource
{
    public function __construct(StudentAchievementData $resource, private readonly bool $includeRevisions = true)
    {
        parent::__construct($resource);
    }

    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return $this->resource->toArray($this->includeRevisions);
    }
}
