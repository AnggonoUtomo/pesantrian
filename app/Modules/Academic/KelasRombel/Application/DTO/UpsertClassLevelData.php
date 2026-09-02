<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Application\DTO;

final readonly class UpsertClassLevelData
{
    public function __construct(
        public string $unitId,
        public string $code,
        public string $name,
        public int $sequence,
        public string $status,
    ) {}

    /** @return array<string, string|int> */
    public function toArray(): array
    {
        return [
            'unit_id' => $this->unitId,
            'code' => $this->code,
            'name' => $this->name,
            'sequence' => $this->sequence,
            'status' => $this->status,
        ];
    }
}
