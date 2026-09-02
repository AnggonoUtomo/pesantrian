<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod\Application\DTO;

final readonly class ActiveAcademicPeriodData
{
    public function __construct(
        public string $termId,
        public string $academicYearId,
        public string $termCode,
        public string $termName,
        public string $academicYearCode,
        public string $academicYearName,
        public string $startsOn,
        public string $endsOn,
    ) {}
}
