<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Application\DTO;

final readonly class DormitoryData
{
    /**
     * @param  list<DormitoryRoomData>  $rooms
     * @param  list<StudentRoomPlacementData>  $placements
     * @param  list<DormitorySupervisorAssignmentData>  $supervisors
     */
    public function __construct(
        public string $id,
        public ReferenceData $unit,
        public string $code,
        public string $name,
        public string $genderPolicy,
        public ?string $description,
        public int $roomCount,
        public int $capacity,
        public int $occupiedCount,
        public int $availableCapacity,
        public string $status,
        public ?string $archivedAt,
        public ?string $createdAt,
        public ?string $updatedAt,
        public array $rooms = [],
        public array $placements = [],
        public array $supervisors = [],
    ) {}
}
