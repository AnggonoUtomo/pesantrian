<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Application\Contracts;

use App\Modules\Pesantrian\Asrama\Application\DTO\AssignDormitorySupervisorData;
use App\Modules\Pesantrian\Asrama\Application\DTO\DormitoryData;
use App\Modules\Pesantrian\Asrama\Application\DTO\DormitoryRoomPlacementContextData;
use App\Modules\Pesantrian\Asrama\Application\DTO\DormitorySupervisorAssignmentData;
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

    public function findSupervisorAssignment(string $id): ?DormitorySupervisorAssignmentData;

    public function findActiveSupervisorForScope(string $employeeId, string $dormitoryId, ?string $roomId): ?DormitorySupervisorAssignmentData;

    public function assignSupervisor(AssignDormitorySupervisorData $data): DormitorySupervisorAssignmentData;

    public function endSupervisor(string $assignmentId, string $endedAt, string $reason): ?DormitorySupervisorAssignmentData;

    public function archiveDormitory(string $id, ?string $actorId): ?DormitoryData;

    public function restoreDormitory(string $id): ?DormitoryData;

    public function archiveRoom(string $dormitoryId, string $roomId, ?string $actorId): ?DormitoryData;

    public function restoreRoom(string $dormitoryId, string $roomId): ?DormitoryData;
}
