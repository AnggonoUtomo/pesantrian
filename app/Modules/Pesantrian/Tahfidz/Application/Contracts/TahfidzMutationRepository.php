<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Application\Contracts;

use App\Modules\Pesantrian\Tahfidz\Application\DTO\TahfidzProgramData;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\TahfidzTargetData;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\UpsertTahfidzProgramData;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\UpsertTahfidzTargetData;

interface TahfidzMutationRepository
{
    public function createProgram(UpsertTahfidzProgramData $data, ?string $actorId): TahfidzProgramData;

    /** @param array<string, string|null> $changes */
    public function updateProgram(string $id, array $changes): ?TahfidzProgramData;

    public function createTarget(UpsertTahfidzTargetData $data, ?string $actorId): TahfidzTargetData;

    /** @param array<string, int|string|null> $changes */
    public function updateTarget(string $id, array $changes): ?TahfidzTargetData;
}
