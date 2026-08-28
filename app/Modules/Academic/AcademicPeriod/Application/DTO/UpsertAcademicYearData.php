<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod\Application\DTO;

final readonly class UpsertAcademicYearData
{
    public function __construct(
        public string $code,
        public string $name,
        public string $startsOn,
        public string $endsOn,
        public string $status,
    ) {}

    /** @return array<string, string> */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'starts_on' => $this->startsOn,
            'ends_on' => $this->endsOn,
            'status' => $this->status,
        ];
    }
}
