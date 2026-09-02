<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod\Database\Seeders;

use App\Modules\Academic\AcademicPeriod\Infrastructure\Models\AcademicTermRecord;
use App\Modules\Academic\AcademicPeriod\Infrastructure\Models\AcademicYearRecord;
use Illuminate\Database\Seeder;

final class AcademicPeriodDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (config('app.env') === 'production') {
            return;
        }

        $closedYear = $this->upsertYear('2025-2026', 'Tahun Ajaran 2025/2026', '2025-07-01', '2026-06-30', 'closed');
        $activeYear = $this->upsertYear('2026-2027', 'Tahun Ajaran 2026/2027', '2026-07-01', '2027-06-30', 'active');
        $draftYear = $this->upsertYear('2027-2028', 'Tahun Ajaran 2027/2028', '2027-07-01', '2028-06-30', 'draft');

        $this->upsertTerm($closedYear, '2025-2026-GENAP', 'Semester Genap 2025/2026', 2, '2026-01-01', '2026-06-30', 'closed', false);
        $this->upsertTerm($activeYear, '2026-2027-GANJIL', 'Semester Ganjil 2026/2027', 1, '2026-07-01', '2026-12-31', 'active', true);
        $this->upsertTerm($activeYear, '2026-2027-GENAP', 'Semester Genap 2026/2027', 2, '2027-01-01', '2027-06-30', 'draft', false);
        $this->upsertTerm($draftYear, '2027-2028-GANJIL', 'Semester Ganjil 2027/2028', 1, '2027-07-01', '2027-12-31', 'draft', false);
    }

    private function upsertYear(string $code, string $name, string $startsOn, string $endsOn, string $status): AcademicYearRecord
    {
        $year = AcademicYearRecord::firstOrNew(['code' => $code]);
        $year->fill([
            'name' => $name,
            'starts_on' => $startsOn,
            'ends_on' => $endsOn,
            'status' => $status,
        ]);
        $year->save();

        return $year;
    }

    private function upsertTerm(
        AcademicYearRecord $year,
        string $code,
        string $name,
        int $sequence,
        string $startsOn,
        string $endsOn,
        string $status,
        bool $isActive,
    ): void {
        $term = AcademicTermRecord::firstOrNew([
            'academic_year_id' => $year->id,
            'code' => $code,
        ]);
        $term->fill([
            'name' => $name,
            'sequence' => $sequence,
            'starts_on' => $startsOn,
            'ends_on' => $endsOn,
            'status' => $status,
            'is_active' => $isActive,
        ]);
        $term->save();
    }
}
