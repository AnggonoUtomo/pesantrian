<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Application\Contracts;

use App\Modules\Academic\KelasRombel\Application\DTO\ActiveClassGroupStudentData;

interface ActiveClassGroupRosterReader
{
    /** @return list<ActiveClassGroupStudentData> */
    public function studentsForClassGroup(string $classGroupId, ?string $academicTermId = null): array;
}
