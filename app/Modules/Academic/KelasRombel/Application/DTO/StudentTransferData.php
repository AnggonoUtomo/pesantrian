<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Application\DTO;

final readonly class StudentTransferData
{
    public function __construct(
        public StudentPlacementData $previous,
        public StudentPlacementData $current,
    ) {}
}
