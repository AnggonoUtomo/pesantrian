<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Application\Actions;

use App\Modules\Pesantrian\Asrama\Application\Contracts\AsramaActivityPublisher;
use App\Modules\Pesantrian\Asrama\Application\Contracts\AsramaMutationRepository;
use App\Modules\Pesantrian\Asrama\Application\DTO\DormitoryData;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class UpdateDormitory
{
    public function __construct(
        private AsramaActivityPublisher $activities,
        private AsramaMutationRepository $repository,
    ) {}

    /** @param array<string, string|null> $changes */
    public function execute(?Authenticatable $actor, string $id, array $changes, ?string $correlationId = null): ?DormitoryData
    {
        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'asrama.dormitory.updated',
            subjectType: 'dormitory',
            mutation: fn (): ?DormitoryData => $this->repository->updateDormitory($id, $changes),
            subjectId: static fn (?DormitoryData $dormitory): ?string => $dormitory?->id,
            metadata: static fn (?DormitoryData $dormitory): array => [
                'changed_fields' => array_keys($changes),
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
            'gender_policy' => $dormitory->genderPolicy,
            'status' => $dormitory->status,
        ];
    }
}
