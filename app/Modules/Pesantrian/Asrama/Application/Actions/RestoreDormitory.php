<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Application\Actions;

use App\Modules\Pesantrian\Asrama\Application\Contracts\AsramaActivityPublisher;
use App\Modules\Pesantrian\Asrama\Application\Contracts\AsramaMutationRepository;
use App\Modules\Pesantrian\Asrama\Application\DTO\DormitoryData;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class RestoreDormitory
{
    public function __construct(
        private AsramaActivityPublisher $activities,
        private AsramaMutationRepository $repository,
    ) {}

    public function execute(?Authenticatable $actor, string $dormitoryId, ?string $correlationId = null): ?DormitoryData
    {
        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'asrama.dormitory.restored',
            subjectType: 'dormitory',
            mutation: fn (): ?DormitoryData => $this->repository->restoreDormitory($dormitoryId),
            subjectId: static fn (?DormitoryData $dormitory): ?string => $dormitory?->id,
            metadata: static fn (?DormitoryData $dormitory): array => [
                'changed_fields' => ['status', 'archived_at', 'archived_by'],
                'result' => $dormitory instanceof DormitoryData ? self::auditResult($dormitory) : null,
            ],
            correlationId: $correlationId,
        );
    }

    /** @return array<string, mixed> */
    private static function auditResult(DormitoryData $dormitory): array
    {
        return [
            'unit_id' => $dormitory->unit->id,
            'code' => $dormitory->code,
            'name' => $dormitory->name,
            'status' => $dormitory->status,
            'archived_at' => $dormitory->archivedAt,
        ];
    }
}
