<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod\Infrastructure\Readers;

use App\Modules\Academic\AcademicPeriod\Application\Contracts\ActiveAcademicPeriodReader;
use App\Modules\Academic\AcademicPeriod\Application\DTO\ActiveAcademicPeriodData;
use App\Modules\Academic\AcademicPeriod\Infrastructure\Models\AcademicTermRecord;
use App\Modules\Academic\AcademicPeriod\Infrastructure\Models\AcademicYearRecord;

final class EloquentActiveAcademicPeriodReader implements ActiveAcademicPeriodReader
{
    public function current(): ?ActiveAcademicPeriodData
    {
        $record = AcademicTermRecord::query()
            ->with('academicYear')
            ->where('is_active', true)
            ->where('status', 'active')
            ->orderBy('starts_on')
            ->first();

        if (! $record instanceof AcademicTermRecord || ! $record->academicYear instanceof AcademicYearRecord) {
            return null;
        }

        return new ActiveAcademicPeriodData(
            termId: (string) $record->getKey(),
            academicYearId: (string) $record->academic_year_id,
            termCode: (string) $record->code,
            termName: (string) $record->name,
            academicYearCode: (string) $record->academicYear->code,
            academicYearName: (string) $record->academicYear->name,
            startsOn: $record->starts_on->toDateString(),
            endsOn: $record->ends_on->toDateString(),
        );
    }
}
