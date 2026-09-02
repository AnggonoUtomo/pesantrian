<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Santri\Database\Seeders;

use App\Models\User;
use App\Modules\Pesantrian\Santri\Infrastructure\Models\StudentGuardianRecord;
use App\Modules\Pesantrian\Santri\Infrastructure\Models\StudentRecord;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class SantriDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (config('app.env') === 'production') {
            return;
        }

        $actor = User::where('email', 'super-system@example.test')->first();
        $mts = DB::table('organization_units')->where('code', 'DEMO-MTS')->first();
        $ma = DB::table('organization_units')->where('code', 'DEMO-MA')->first();
        $acceptedAdmission = DB::table('student_admissions')
            ->where('registration_no', 'PPDB-DEMO-ACCEPTED')
            ->first();

        if (! $mts || ! $ma) {
            return;
        }

        $rows = [
            ['NIS-DEMO-AKTIF', null, null, 'Ahmad Santri Aktif', 'Ahmad', 'male', $mts->id, 'active', null, null, null],
            ['NIS-DEMO-NONAKTIF', null, null, 'Fatimah Santri Nonaktif', 'Fatimah', 'female', $ma->id, 'inactive', 'Cuti sementara dari kegiatan pesantren.', now()->subDays(10), null],
            ['NIS-DEMO-PINDAH', null, null, 'Hasan Santri Pindah', 'Hasan', 'male', $mts->id, 'transferred', 'Pindah mengikuti domisili wali.', now()->subDays(20), null],
            ['NIS-DEMO-LULUS', null, null, 'Nadia Santri Lulus', 'Nadia', 'female', $ma->id, 'graduated', 'Lulus akhir tahun ajaran 2025/2026.', now()->subMonths(2), null],
            ['NIS-DEMO-ARSIP', null, null, 'Rafi Santri Arsip', 'Rafi', 'male', $mts->id, 'inactive', 'Data lama yang diarsipkan.', now()->subMonths(3), now()->subMonth()],
            ['NIS-DEMO-PPDB', $acceptedAdmission?->id, $acceptedAdmission?->registration_no, 'Dani Accepted Demo', 'Dani', 'male', $ma->id, 'active', null, null, null],
        ];

        foreach ($rows as [$studentNo, $admissionId, $registrationNo, $fullName, $preferredName, $gender, $unitId, $status, $reason, $statusChangedAt, $archivedAt]) {
            $student = StudentRecord::firstOrNew(['student_no' => $studentNo]);
            $student->fill([
                'admission_id' => $admissionId,
                'registration_no' => $registrationNo,
                'full_name' => $fullName,
                'preferred_name' => $preferredName,
                'gender' => $gender,
                'birth_place' => 'Bandung',
                'birth_date' => '2013-05-10',
                'previous_school' => 'SD/MI Demo',
                'primary_unit_id' => $unitId,
                'entry_date' => '2026-07-15',
                'status' => $status,
                'status_reason' => $reason,
                'status_changed_at' => $statusChangedAt,
                'status_changed_by' => $statusChangedAt === null ? null : $actor?->id,
                'archived_at' => $archivedAt,
                'archived_by' => $archivedAt === null ? null : $actor?->id,
            ]);
            $student->save();

            StudentGuardianRecord::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'guardian_name' => 'Wali Demo '.$preferredName,
                ],
                [
                    'guardian_phone' => '08120000'.substr($studentNo, -2),
                    'guardian_relation' => $gender === 'male' ? 'ayah' : 'ibu',
                    'is_primary' => true,
                    'is_emergency_contact' => true,
                    'notes' => 'Data demo wali snapshot.',
                ],
            );
        }
    }
}
