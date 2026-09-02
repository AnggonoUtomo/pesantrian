<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Application\Actions;

use App\Modules\Academic\KelasRombel\Application\Contracts\KelasRombelActivityPublisher;
use App\Modules\Academic\KelasRombel\Application\Contracts\KelasRombelMutationRepository;
use App\Modules\Academic\KelasRombel\Application\DTO\ClassLevelData;
use App\Modules\Academic\KelasRombel\Application\DTO\UpsertClassLevelData;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class CreateClassLevel
{
    public function __construct(
        private KelasRombelActivityPublisher $activities,
        private KelasRombelMutationRepository $repository,
    ) {}

    public function execute(?Authenticatable $actor, UpsertClassLevelData $data, ?string $correlationId = null): ClassLevelData
    {
        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'kelas_rombel.class_level.created',
            subjectType: 'class_level',
            mutation: fn (): ClassLevelData => $this->repository->createClassLevel($data),
            subjectId: static fn (ClassLevelData $level): string => $level->id,
            metadata: static fn (ClassLevelData $level): array => [
                'changed_fields' => ['unit_id', 'code', 'name', 'sequence', 'status'],
                'result' => self::auditResult($level),
            ],
            correlationId: $correlationId,
        );
    }

    /** @return array<string, string|int> */
    private static function auditResult(ClassLevelData $level): array
    {
        return [
            'unit_id' => $level->unitId,
            'code' => $level->code,
            'name' => $level->name,
            'sequence' => $level->sequence,
            'status' => $level->status,
        ];
    }
}
