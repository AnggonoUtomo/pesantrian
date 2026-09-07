<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Application\Actions;

use App\Modules\Pesantrian\Tahfidz\Application\Contracts\TahfidzActivityPublisher;
use App\Modules\Pesantrian\Tahfidz\Application\Contracts\TahfidzMutationRepository;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\TahfidzProgramData;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class UpdateTahfidzProgram
{
    public function __construct(
        private TahfidzActivityPublisher $activities,
        private TahfidzMutationRepository $repository,
    ) {}

    /** @param array<string, string|null> $changes */
    public function execute(?Authenticatable $actor, string $id, array $changes, ?string $correlationId = null): ?TahfidzProgramData
    {
        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'tahfidz.program.updated',
            subjectType: 'tahfidz_program',
            mutation: fn (): ?TahfidzProgramData => $this->repository->updateProgram($id, $changes),
            subjectId: static fn (?TahfidzProgramData $program): ?string => $program?->id,
            metadata: static fn (?TahfidzProgramData $program): array => [
                'changed_fields' => array_keys($changes),
                'result' => $program instanceof TahfidzProgramData ? self::auditProgram($program) : null,
            ],
            correlationId: $correlationId,
        );
    }

    /** @return array<string, mixed> */
    private static function auditProgram(TahfidzProgramData $program): array
    {
        return [
            'code' => $program->code,
            'name' => $program->name,
            'status' => $program->status,
        ];
    }
}
