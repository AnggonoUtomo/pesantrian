<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Presentation\Resources;

use App\Modules\Pesantrian\Tahfidz\Application\DTO\TahfidzProgramData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TahfidzProgramData */
final class TahfidzProgramResource extends JsonResource
{
    public function __construct(TahfidzProgramData $resource)
    {
        parent::__construct($resource);
    }

    /** @return array{id: string, code: string, name: string, description: string|null, status: string} */
    public function toArray(Request $request): array
    {
        /** @var TahfidzProgramData $program */
        $program = $this->resource;

        return [
            'id' => $program->id,
            'code' => $program->code,
            'name' => $program->name,
            'description' => $program->description,
            'status' => $program->status,
        ];
    }
}
