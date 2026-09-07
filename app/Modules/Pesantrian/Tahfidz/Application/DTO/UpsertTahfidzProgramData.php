<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Application\DTO;

final readonly class UpsertTahfidzProgramData
{
    public function __construct(
        public string $code,
        public string $name,
        public ?string $description,
        public string $status,
    ) {}

    /** @return array{code: string, name: string, description: string|null, status: string} */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
        ];
    }
}
