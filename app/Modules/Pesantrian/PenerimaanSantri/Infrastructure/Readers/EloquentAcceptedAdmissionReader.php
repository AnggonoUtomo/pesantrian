<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PenerimaanSantri\Infrastructure\Readers;

use App\Modules\Pesantrian\PenerimaanSantri\Application\Contracts\AcceptedAdmissionReader;
use App\Modules\Pesantrian\PenerimaanSantri\Application\DTO\AcceptedAdmissionData;
use App\Modules\Pesantrian\PenerimaanSantri\Infrastructure\Models\StudentAdmissionRecord;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class EloquentAcceptedAdmissionReader implements AcceptedAdmissionReader
{
    public function findAcceptedForConversion(string $admissionId): ?AcceptedAdmissionData
    {
        if (! Str::isUlid($admissionId)) {
            throw new InvalidArgumentException('admissionId wajib berupa ULID.');
        }

        $record = StudentAdmissionRecord::query()
            ->whereKey($admissionId)
            ->where('status', 'accepted')
            ->whereNotNull('decided_at')
            ->whereNotNull('decided_by')
            ->where('candidate_name', '<>', '')
            ->where('guardian_name', '<>', '')
            ->first();

        if (! $record instanceof StudentAdmissionRecord) {
            return null;
        }

        if ($record->registration_fee_required && $record->registration_fee_status !== 'verified') {
            return null;
        }

        return new AcceptedAdmissionData(
            admissionId: $record->id,
            registrationNo: $record->registration_no,
            candidateName: $record->candidate_name,
            candidateGender: $record->candidate_gender,
            candidateBirthPlace: $record->candidate_birth_place,
            candidateBirthDate: $record->candidate_birth_date?->toDateString(),
            previousSchool: $record->previous_school,
            targetUnitId: $record->target_unit_id,
            guardianName: $record->guardian_name,
            guardianPhone: $record->guardian_phone,
            guardianRelation: $record->guardian_relation,
            acceptedAt: $record->decided_at?->toJSON(),
            acceptedBy: $record->decided_by,
        );
    }
}
