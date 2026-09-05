<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Application\Actions;

use App\Modules\Pesantrian\Asrama\Application\Contracts\AsramaActivityPublisher;
use App\Modules\Pesantrian\Asrama\Application\Contracts\AsramaMutationRepository;
use App\Modules\Pesantrian\Asrama\Application\DTO\DormitoryData;
use App\Modules\Pesantrian\Asrama\Application\DTO\UpsertDormitoryData;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class CreateDormitory
{
    public function __construct(
        private AsramaActivityPublisher $activities,
        private AsramaMutationRepository $repository,
    ) {}

    public function execute(?Authenticatable $actor, UpsertDormitoryData $data, ?string $correlationId = null): DormitoryData
    {
        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'asrama.dormitory.created',
            subjectType: 'dormitory',
            mutation: fn (): DormitoryData => $this->repository->createDormitory($data),
            subjectId: static fn (DormitoryData $dormitory): string => $dormitory->id,
            metadata: static fn (DormitoryData $dormitory): array => [
                'changed_fields' => ['unit_id', 'code', 'name', 'gender_policy', 'description', 'status'],
                'result' => self::auditResult($dormitory),
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
