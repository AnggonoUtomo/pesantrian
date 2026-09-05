<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Application\DTO;

final readonly class StudentRoomTransferData
{
    public function __construct(
        public StudentRoomPlacementData $previous,
        public StudentRoomPlacementData $current,
    ) {}
}
