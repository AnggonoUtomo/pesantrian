<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod\Application\Contracts;

use App\Modules\Academic\AcademicPeriod\Application\DTO\AcademicTermData;
use App\Modules\Academic\AcademicPeriod\Application\DTO\AcademicTermListFilter;
use App\Modules\Academic\AcademicPeriod\Application\DTO\AcademicYearData;
use App\Modules\Academic\AcademicPeriod\Application\DTO\AcademicYearListFilter;
use App\Modules\Academic\AcademicPeriod\Application\DTO\PaginatedAcademicTermData;
use App\Modules\Academic\AcademicPeriod\Application\DTO\PaginatedAcademicYearData;
use App\Modules\Academic\AcademicPeriod\Application\DTO\UpsertAcademicTermData;
use App\Modules\Academic\AcademicPeriod\Application\DTO\UpsertAcademicYearData;

interface AcademicPeriodRepository
{
    public function paginateYears(AcademicYearListFilter $filter): PaginatedAcademicYearData;

    public function findYear(string $id): ?AcademicYearData;

    public function createYear(UpsertAcademicYearData $data): AcademicYearData;

    /** @param array<string, string> $changes */
    public function updateYear(string $id, array $changes): ?AcademicYearData;

    public function paginateTerms(AcademicTermListFilter $filter): PaginatedAcademicTermData;

    public function findTerm(string $id): ?AcademicTermData;

    public function currentActiveTerm(): ?AcademicTermData;

    public function createTerm(UpsertAcademicTermData $data): AcademicTermData;

    /** @param array<string, string|int|bool> $changes */
    public function updateTerm(string $id, array $changes): ?AcademicTermData;

    public function activateTerm(string $id): ?AcademicTermData;

    public function closeTerm(string $id): ?AcademicTermData;
}
