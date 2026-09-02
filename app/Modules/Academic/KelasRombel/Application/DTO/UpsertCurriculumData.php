<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Application\DTO;

final readonly class UpsertCurriculumData
{
    public function __construct(
        public string $code,
        public string $name,
        public ?string $description,
        public string $status,
    ) {}

    /** @return array<string, string|null> */
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
