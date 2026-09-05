<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Application\Contracts;

use App\Modules\Pesantrian\Asrama\Application\DTO\DormitoryData;
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
}
