<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Santri\Infrastructure\Readers;

use App\Modules\Pesantrian\Santri\Application\Contracts\PrimaryStudentGuardianReader;
use App\Modules\Pesantrian\Santri\Application\DTO\PrimaryStudentGuardianSnapshotData;
use App\Modules\Pesantrian\Santri\Infrastructure\Models\StudentGuardianRecord;

final class EloquentPrimaryStudentGuardianReader implements PrimaryStudentGuardianReader
{
    public function primaryForActiveStudent(string $studentId): ?PrimaryStudentGuardianSnapshotData
    {
        $record = StudentGuardianRecord::query()
            ->where('student_id', $studentId)
            ->whereHas('student', function ($query): void {
                $query->where('status', 'active')
                    ->whereNull('archived_at');
            })
            ->orderByDesc('is_primary')
            ->orderByDesc('is_emergency_contact')
            ->orderBy('created_at')
            ->first(['student_id', 'guardian_name', 'guardian_phone', 'guardian_relation']);

        return $record instanceof StudentGuardianRecord ? $this->map($record) : null;
    }

    private function map(StudentGuardianRecord $record): PrimaryStudentGuardianSnapshotData
    {
        return new PrimaryStudentGuardianSnapshotData(
            studentId: (string) $record->student_id,
            guardianName: (string) $record->guardian_name,
            guardianPhone: $record->guardian_phone === null ? null : (string) $record->guardian_phone,
            guardianRelation: $record->guardian_relation === null ? null : (string) $record->guardian_relation,
        );
    }
}
