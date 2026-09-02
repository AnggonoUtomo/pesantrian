<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Application\Actions;

use App\Modules\Academic\KelasRombel\Application\Contracts\KelasRombelActivityPublisher;
use App\Modules\Academic\KelasRombel\Application\Contracts\KelasRombelMutationRepository;
use App\Modules\Academic\KelasRombel\Application\DTO\CurriculumData;
use App\Modules\Academic\KelasRombel\Application\DTO\UpsertCurriculumData;
use Illuminate\Contracts\Auth\Authenticatable;

final readonly class CreateCurriculum
{
    public function __construct(
        private KelasRombelActivityPublisher $activities,
        private KelasRombelMutationRepository $repository,
    ) {}

    public function execute(?Authenticatable $actor, UpsertCurriculumData $data, ?string $correlationId = null): CurriculumData
    {
        return $this->activities->publish(
            actorId: $actor ? (string) $actor->getAuthIdentifier() : null,
            action: 'kelas_rombel.curriculum.created',
            subjectType: 'academic_curriculum',
            mutation: fn (): CurriculumData => $this->repository->createCurriculum($data),
            subjectId: static fn (CurriculumData $curriculum): string => $curriculum->id,
            metadata: static fn (CurriculumData $curriculum): array => [
                'changed_fields' => ['code', 'name', 'description', 'status'],
                'result' => self::auditResult($curriculum),
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
