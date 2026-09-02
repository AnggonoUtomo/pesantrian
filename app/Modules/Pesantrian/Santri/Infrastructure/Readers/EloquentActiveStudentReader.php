<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Santri\Infrastructure\Readers;

use App\Modules\Pesantrian\Santri\Application\Contracts\ActiveStudentReader;
use App\Modules\Pesantrian\Santri\Application\DTO\ActiveStudentOptionData;
use App\Modules\Pesantrian\Santri\Infrastructure\Models\StudentRecord;

final class EloquentActiveStudentReader implements ActiveStudentReader
{
    public function findActive(string $studentId, ?string $primaryUnitId = null): ?ActiveStudentOptionData
    {
        $record = StudentRecord::query()
            ->whereKey($studentId)
            ->where('status', 'active')
            ->whereNull('archived_at')
            ->when($primaryUnitId !== null, fn ($query) => $query->where('primary_unit_id', $primaryUnitId))
            ->first(['id', 'student_no', 'full_name', 'primary_unit_id']);

        return $record instanceof StudentRecord ? $this->map($record) : null;
    }

    public function options(?string $primaryUnitId = null, ?string $search = null, int $limit = 50): array
    {
        $safeLimit = max(1, min($limit, 100));

        return StudentRecord::query()
            ->where('status', 'active')
            ->whereNull('archived_at')
            ->when($primaryUnitId !== null, fn ($query) => $query->where('primary_unit_id', $primaryUnitId))
            ->when($search !== null && trim($search) !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('full_name', 'like', '%'.$search.'%')
                        ->orWhere('student_no', 'like', '%'.$search.'%')
                        ->orWhere('preferred_name', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('full_name')
            ->limit($safeLimit)
            ->get(['id', 'student_no', 'full_name', 'primary_unit_id'])
            ->map(fn (StudentRecord $record): ActiveStudentOptionData => $this->map($record))
            ->values()
            ->all();
    }

    private function map(StudentRecord $record): ActiveStudentOptionData
    {
        return new ActiveStudentOptionData(
            id: (string) $record->getKey(),
            studentNo: (string) $record->student_no,
            fullName: (string) $record->full_name,
            primaryUnitId: $record->primary_unit_id === null ? null : (string) $record->primary_unit_id,
        );
    }
}
