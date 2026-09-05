<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PresensiSantri\Application\DTO;

final readonly class StudentAttendanceSummaryData
{
    public function __construct(
        public int $total,
        public int $present,
        public int $late,
        public int $excused,
        public int $sick,
        public int $absent,
    ) {}

    /** @return array<string, int> */
    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'present' => $this->present,
            'late' => $this->late,
            'excused' => $this->excused,
            'sick' => $this->sick,
            'absent' => $this->absent,
        ];
    }
}
