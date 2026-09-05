<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Application\DTO;

final readonly class UpsertDormitoryRoomData
{
    public function __construct(
        public string $dormitoryId,
        public string $code,
        public string $name,
        public int $capacity,
        public string $status,
    ) {}

    /** @return array<string, string|int> */
    public function toArray(): array
    {
        return [
            'dormitory_id' => $this->dormitoryId,
            'code' => $this->code,
            'name' => $this->name,
            'capacity' => $this->capacity,
            'status' => $this->status,
        ];
    }
}
