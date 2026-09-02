<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Application\Actions;

use App\Modules\Academic\KelasRombel\Application\Contracts\KelasRombelActivityPublisher;
use App\Modules\Academic\KelasRombel\Application\Contracts\KelasRombelMutationRepository;
use App\Modules\Academic\KelasRombel\Application\DTO\CurriculumData;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class UpdateCurriculum
{
    public function __construct(
        private KelasRombelActivityPublisher $activities,
        private KelasRombelMutationRepository $repository,
    ) {}

    /** @param array<string, string|null> $changes */
    public function execute(?Authenticatable $actor, string $id, array $changes, ?string $correlationId = null): ?CurriculumData
    {
        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'kelas_rombel.curriculum.updated',
            subjectType: 'academic_curriculum',
            mutation: fn (): ?CurriculumData => $this->repository->updateCurriculum($id, $changes),
            subjectId: static fn (?CurriculumData $curriculum): ?string => $curriculum?->id,
            metadata: static fn (?CurriculumData $curriculum): array => [
                'changed_fields' => array_keys($changes),
                'result' => $curriculum instanceof CurriculumData ? self::auditResult($curriculum) : null,
            ],
            correlationId: $correlationId,
        );
    }

    /** @return array<string, string|null> */
    private static function auditResult(CurriculumData $curriculum): array
    {
        return [
            'code' => $curriculum->code,
            'name' => $curriculum->name,
            'description' => $curriculum->description,
            'status' => $curriculum->status,
        ];
    }
}
