<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Application\Actions;

use App\Modules\Pesantrian\Tahfidz\Application\Contracts\TahfidzActivityPublisher;
use App\Modules\Pesantrian\Tahfidz\Application\Contracts\TahfidzMutationRepository;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\TahfidzProgramData;
use App\Modules\Pesantrian\Tahfidz\Application\DTO\UpsertTahfidzProgramData;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class CreateTahfidzProgram
{
    public function __construct(
        private TahfidzActivityPublisher $activities,
        private TahfidzMutationRepository $repository,
    ) {}

    public function execute(?Authenticatable $actor, UpsertTahfidzProgramData $data, ?string $correlationId = null): TahfidzProgramData
    {
        $actorId = $actor ? (string) $actor->getAuthIdentifier() : null;

        return $this->activities->publish(
            actorId: $actorId,
            action: 'tahfidz.program.created',
            subjectType: 'tahfidz_program',
            mutation: fn (): TahfidzProgramData => $this->repository->createProgram($data, $actorId),
            subjectId: static fn (TahfidzProgramData $program): string => $program->id,
            metadata: static fn (TahfidzProgramData $program): array => [
                'changed_fields' => ['code', 'name', 'description', 'status'],
                'result' => self::auditProgram($program),
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
