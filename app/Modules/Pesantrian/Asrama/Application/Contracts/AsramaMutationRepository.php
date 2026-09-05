<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Application\Contracts;

use App\Modules\Pesantrian\Asrama\Application\DTO\DormitoryData;
use App\Modules\Pesantrian\Asrama\Application\DTO\DormitoryRoomPlacementContextData;
use App\Modules\Pesantrian\Asrama\Application\DTO\PlaceStudentRoomData;
use App\Modules\Pesantrian\Asrama\Application\DTO\StudentRoomPlacementData;
use App\Modules\Pesantrian\Asrama\Application\DTO\StudentRoomTransferData;
use App\Modules\Pesantrian\Asrama\Application\DTO\UpsertDormitoryData;
use App\Modules\Pesantrian\Asrama\Application\DTO\UpsertDormitoryRoomData;

interface AsramaMutationRepository
{
    public function createDormitory(UpsertDormitoryData $data): DormitoryData;

    /** @param array<string, string|null> $changes */
    public function updateDormitory(string $id, array $changes): ?DormitoryData;

    public function createRoom(UpsertDormitoryRoomData $data): DormitoryData;

    /** @param array<string, string|int> $changes */
    public function updateRoom(string $dormitoryId, string $roomId, array $changes): ?DormitoryData;

    public function findRoomForPlacement(string $roomId): ?DormitoryRoomPlacementContextData;

    public function findPlacement(string $id): ?StudentRoomPlacementData;

    public function findActivePlacementForStudent(string $studentId): ?StudentRoomPlacementData;

    public function placeStudent(PlaceStudentRoomData $data): StudentRoomPlacementData;

    public function transferStudent(string $placementId, PlaceStudentRoomData $target, string $reason): ?StudentRoomTransferData;

    public function removeStudent(string $placementId, string $endedAt, string $reason, ?string $actorId): ?StudentRoomPlacementData;
}
