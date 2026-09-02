<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Application\DTO;

final readonly class ClassGroupHomeroomData
{
    public function __construct(
        public string $id,
        public string $employeeId,
        public string $employeeName,
        public string $assignedOn,
        public ?string $endedOn,
        public string $status,
        public ?string $reason,
    ) {}
}
