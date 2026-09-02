<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PenerimaanSantri\Database\Seeders;

use App\Models\User;
use App\Modules\Organization\Organization\Infrastructure\Models\OrganizationUnitRecord;
use App\Modules\Pesantrian\PenerimaanSantri\Infrastructure\Models\StudentAdmissionRecord;
use Illuminate\Database\Seeder;

final class PenerimaanSantriDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (config('app.env') === 'production') {
            return;
        }

        $actor = User::where('email', 'super-system@example.test')->first();
        $mts = OrganizationUnitRecord::where('code', 'DEMO-MTS')->first();
        $ma = OrganizationUnitRecord::where('code', 'DEMO-MA')->first();

        if (! $mts || ! $ma) {
            return;
        }

        $rows = [
            ['PPDB-DEMO-DRAFT', 'Alya Draft Demo', 'female', $mts->id, 'draft', 'not_required', false, null, null],
            ['PPDB-DEMO-SUBMITTED', 'Bima Submitted Demo', 'male', $mts->id, 'submitted', 'unpaid', true, 150000, null],
            ['PPDB-DEMO-VERIFIED', 'Citra Verified Demo', 'female', $ma->id, 'verified', 'paid', true, 150000, null],
            ['PPDB-DEMO-ACCEPTED', 'Dani Accepted Demo', 'male', $ma->id, 'accepted', 'paid', true, 150000, $actor?->id],
            ['PPDB-DEMO-REJECTED', 'Eka Rejected Demo', 'female', $mts->id, 'rejected', 'paid', true, 150000, $actor?->id],
            ['PPDB-DEMO-CANCELLED', 'Fajar Cancelled Demo', 'male', $ma->id, 'cancelled', 'unpaid', true, 150000, $actor?->id],
        ];

        foreach ($rows as [$registrationNo, $candidateName, $gender, $unitId, $status, $feeStatus, $feeRequired, $feeAmount, $decidedBy]) {
            $admission = StudentAdmissionRecord::firstOrNew(['registration_no' => $registrationNo]);
            $admission->fill([
                'registration_period' => 'PPDB 2026/2027',
                'candidate_name' => $candidateName,
                'candidate_gender' => $gender,
                'candidate_birth_place' => 'Bandung',
                'candidate_birth_date' => '2013-05-10',
                'previous_school' => 'SD/MI Demo',
                'target_unit_id' => $unitId,
                'guardian_name' => 'Wali '.$candidateName,
                'guardian_phone' => '0800000000'.substr($registrationNo, -1),
                'guardian_relation' => $gender === 'male' ? 'ayah' : 'ibu',
                'registration_fee_required' => $feeRequired,
                'registration_fee_amount' => $feeAmount,
                'registration_fee_status' => $feeStatus,
                'document_checklist' => [
                    ['name' => 'Kartu Keluarga', 'status' => in_array($status, ['verified', 'accepted', 'rejected'], true) ? 'received' : 'pending'],
                    ['name' => 'Akta Kelahiran', 'status' => in_array($status, ['accepted', 'rejected'], true) ? 'received' : 'pending'],
                ],
                'status' => $status,
                'registered_at' => $status === 'draft' ? null : now()->subDays(14),
                'decided_at' => $decidedBy === null ? null : now()->subDays(3),
                'decided_by' => $decidedBy,
                'notes' => 'Data demo lifecycle PPDB.',
            ]);
            $admission->save();
        }
    }
}
