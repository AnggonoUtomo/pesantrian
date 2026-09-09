<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PerizinanSantri\Infrastructure\Readers;

use App\Modules\Pesantrian\PerizinanSantri\Application\Contracts\LateStudentPermitReader;
use App\Modules\Pesantrian\PerizinanSantri\Application\DTO\StudentPermitDisciplineSignalData;
use App\Modules\Pesantrian\PerizinanSantri\Infrastructure\Models\StudentPermitRecord;
use Illuminate\Database\Eloquent\Builder;

final class EloquentLateStudentPermitReader implements LateStudentPermitReader
{
    public function lateReturns(?string $dateFrom = null, ?string $dateTo = null, ?string $studentId = null, int $limit = 50): array
    {
        $safeLimit = max(1, min($limit, 100));

        $lateConstraint = static function (Builder $query): void {
            $query->whereColumn('returned_at', '>', 'ends_at')
                ->orWhere(function (Builder $query): void {
                    $query->where('status', 'checked_out')
                        ->whereNull('returned_at')
                        ->where('ends_at', '<', now());
                });
        };

        /** @var list<StudentPermitRecord> $records */
        $records = StudentPermitRecord::query()
            ->where($lateConstraint)
            ->when($studentId !== null, fn (Builder $query) => $query->where('student_id', $studentId))
            ->when($dateFrom !== null, function (Builder $query) use ($dateFrom): void {
                $query->where(function (Builder $query) use ($dateFrom): void {
                    $query->whereDate('returned_at', '>=', $dateFrom)
                        ->orWhere(function (Builder $query) use ($dateFrom): void {
                            $query->whereNull('returned_at')
                                ->whereDate('ends_at', '>=', $dateFrom);
                        });
                });
            })
            ->when($dateTo !== null, function (Builder $query) use ($dateTo): void {
                $query->where(function (Builder $query) use ($dateTo): void {
                    $query->whereDate('returned_at', '<=', $dateTo)
                        ->orWhere(function (Builder $query) use ($dateTo): void {
                            $query->whereNull('returned_at')
                                ->whereDate('ends_at', '<=', $dateTo);
                        });
                });
            })
            ->orderByRaw('COALESCE(returned_at, ends_at) desc')
            ->orderBy('student_name')
            ->limit($safeLimit)
            ->get()
            ->all();

        return array_map(
            static fn (StudentPermitRecord $record): StudentPermitDisciplineSignalData => new StudentPermitDisciplineSignalData(
                permitId: (string) $record->getKey(),
                permitNo: (string) $record->permit_no,
                studentId: (string) $record->student_id,
                studentNo: (string) $record->student_no,
                studentName: (string) $record->student_name,
                permitType: (string) $record->permit_type,
                status: (string) $record->status,
                startsAt: $record->starts_at->toJSON(),
                endsAt: $record->ends_at->toJSON(),
                returnedAt: $record->returned_at?->toJSON(),
                occurredAt: ($record->returned_at ?? $record->ends_at)->toJSON(),
                destination: $record->destination === null ? null : (string) $record->destination,
                returnNote: $record->return_note === null ? null : (string) $record->return_note,
            ),
            $records,
        );
    }
}
