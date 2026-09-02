<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Application\Contracts;

use App\Modules\Academic\KelasRombel\Application\DTO\AssignHomeroomData;
use App\Modules\Academic\KelasRombel\Application\DTO\ClassGroupData;
use App\Modules\Academic\KelasRombel\Application\DTO\ClassLevelData;
use App\Modules\Academic\KelasRombel\Application\DTO\CurriculumData;
use App\Modules\Academic\KelasRombel\Application\DTO\HomeroomAssignmentData;
use App\Modules\Academic\KelasRombel\Application\DTO\PlaceStudentData;
use App\Modules\Academic\KelasRombel\Application\DTO\StudentPlacementData;
use App\Modules\Academic\KelasRombel\Application\DTO\StudentTransferData;
use App\Modules\Academic\KelasRombel\Application\DTO\UpsertClassGroupData;
use App\Modules\Academic\KelasRombel\Application\DTO\UpsertClassLevelData;
use App\Modules\Academic\KelasRombel\Application\DTO\UpsertCurriculumData;

interface KelasRombelMutationRepository
{
    public function createCurriculum(UpsertCurriculumData $data): CurriculumData;

    /** @param array<string, string|null> $changes */
    public function updateCurriculum(string $id, array $changes): ?CurriculumData;

    public function createClassLevel(UpsertClassLevelData $data): ClassLevelData;

    /** @param array<string, string|int> $changes */
    public function updateClassLevel(string $id, array $changes): ?ClassLevelData;

    public function createClassGroup(UpsertClassGroupData $data): ClassGroupData;

    /** @param array<string, string|int|null> $changes */
    public function updateClassGroup(string $id, array $changes): ?ClassGroupData;

    public function findPlacement(string $id): ?StudentPlacementData;

    public function findActivePlacementForStudentInTerm(string $studentId, string $academicTermId): ?StudentPlacementData;

    public function placeStudent(PlaceStudentData $data): StudentPlacementData;

    public function transferStudent(string $placementId, PlaceStudentData $target, string $reason): ?StudentTransferData;

    public function removeStudent(string $placementId, string $leftOn, string $reason): ?StudentPlacementData;

    public function findHomeroom(string $id): ?HomeroomAssignmentData;

    public function findActiveHomeroomForClassGroup(string $classGroupId): ?HomeroomAssignmentData;

    public function assignHomeroom(AssignHomeroomData $data): HomeroomAssignmentData;

    public function endHomeroom(string $homeroomId, string $endedOn, string $reason): ?HomeroomAssignmentData;

    public function archiveClassGroup(string $id, ?string $actorId): ?ClassGroupData;

    public function restoreClassGroup(string $id): ?ClassGroupData;
}
