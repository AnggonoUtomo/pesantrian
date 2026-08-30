<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PenerimaanSantri\Infrastructure\Repositories;

use App\Modules\Pesantrian\PenerimaanSantri\Application\Contracts\StudentAdmissionRepository;
use App\Modules\Pesantrian\PenerimaanSantri\Application\DTO\PaginatedStudentAdmissionData;
use App\Modules\Pesantrian\PenerimaanSantri\Application\DTO\StudentAdmissionData;
use App\Modules\Pesantrian\PenerimaanSantri\Application\DTO\StudentAdmissionListFilter;
use App\Modules\Pesantrian\PenerimaanSantri\Application\DTO\UpsertStudentAdmissionData;
use App\Modules\Pesantrian\PenerimaanSantri\Infrastructure\Models\StudentAdmissionRecord;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class EloquentStudentAdmissionRepository implements StudentAdmissionRepository
{
    public function paginate(StudentAdmissionListFilter $filter): PaginatedStudentAdmissionData
    {
        $query = StudentAdmissionRecord::query()
            ->when($filter->search !== null, function ($query) use ($filter): void {
                $query->where(function ($query) use ($filter): void {
                    $query->where('candidate_name', 'like', '%'.$filter->search.'%')
                        ->orWhere('registration_no', 'like', '%'.$filter->search.'%')
                        ->orWhere('guardian_name', 'like', '%'.$filter->search.'%');
                });
            })
            ->when($filter->status !== null, fn ($query) => $query->where('status', $filter->status))
            ->when($filter->targetUnitId !== null, fn ($query) => $query->where('target_unit_id', $filter->targetUnitId))
            ->when($filter->registrationFeeStatus !== null, fn ($query) => $query->where('registration_fee_status', $filter->registrationFeeStatus))
            ->orderBy($filter->sortField, $filter->sortDirection === 'desc' ? 'desc' : 'asc');

        /** @var LengthAwarePaginator<int, StudentAdmissionRecord> $page */
        $page = $query->paginate($filter->perPage, ['*'], 'page', $filter->page);

        return new PaginatedStudentAdmissionData(
            data: array_map(
                fn (StudentAdmissionRecord $record): StudentAdmissionData => $this->map($record),
                array_values($page->items()),
            ),
            currentPage: $page->currentPage(),
            perPage: $page->perPage(),
            total: $page->total(),
            lastPage: $page->lastPage(),
        );
    }

    public function find(string $id): ?StudentAdmissionData
    {
        $record = StudentAdmissionRecord::query()->find($id);

        return $record instanceof StudentAdmissionRecord ? $this->map($record) : null;
    }

    public function create(UpsertStudentAdmissionData $data): StudentAdmissionData
    {
        /** @var StudentAdmissionRecord $record */
        $record = StudentAdmissionRecord::query()->create([
            'registration_no' => $this->nextRegistrationNo(),
            'registered_at' => now(),
            ...$data->toArray(),
        ]);

        return $this->map($record->refresh());
    }

    public function update(string $id, array $changes): ?StudentAdmissionData
    {
        $record = StudentAdmissionRecord::query()->find($id);

        if (! $record instanceof StudentAdmissionRecord) {
            return null;
        }

        $record->fill($changes);
        $record->save();

        return $this->map($record->refresh());
    }

    private function nextRegistrationNo(): string
    {
        $latest = StudentAdmissionRecord::query()
            ->where('registration_no', 'like', 'SNTR-%')
            ->orderByDesc('registration_no')
            ->value('registration_no');

        $next = 1;

        if (is_string($latest) && preg_match('/^SNTR-(\d+)$/', $latest, $matches) === 1) {
            $next = ((int) $matches[1]) + 1;
        }

        return 'SNTR-'.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    private function map(StudentAdmissionRecord $record): StudentAdmissionData
    {
        return new StudentAdmissionData(
            id: (string) $record->getKey(),
            registrationNo: (string) $record->registration_no,
            registrationPeriod: $record->registration_period === null ? null : (string) $record->registration_period,
            candidateName: (string) $record->candidate_name,
            candidateGender: $record->candidate_gender === null ? null : (string) $record->candidate_gender,
            candidateBirthPlace: $record->candidate_birth_place === null ? null : (string) $record->candidate_birth_place,
            candidateBirthDate: $record->candidate_birth_date?->toDateString(),
            previousSchool: $record->previous_school === null ? null : (string) $record->previous_school,
            targetUnitId: $record->target_unit_id === null ? null : (string) $record->target_unit_id,
            guardianName: (string) $record->guardian_name,
            guardianPhone: $record->guardian_phone === null ? null : (string) $record->guardian_phone,
            guardianRelation: $record->guardian_relation === null ? null : (string) $record->guardian_relation,
            registrationFeeRequired: (bool) $record->registration_fee_required,
            registrationFeeAmount: $record->registration_fee_amount === null ? null : (string) $record->registration_fee_amount,
            registrationFeeStatus: (string) $record->registration_fee_status,
            documentChecklist: $this->documentChecklist($record),
            status: (string) $record->status,
            registeredAt: $record->registered_at?->toJSON(),
            decidedAt: $record->decided_at?->toJSON(),
            decidedBy: $record->decided_by === null ? null : (string) $record->decided_by,
            notes: $record->notes === null ? null : (string) $record->notes,
            createdAt: $record->created_at->toJSON(),
            updatedAt: $record->updated_at->toJSON(),
        );
    }

    /** @return array<int, array<string, string>>|null */
    private function documentChecklist(StudentAdmissionRecord $record): ?array
    {
        if ($record->document_checklist === null) {
            return null;
        }

        return array_map(
            static fn (array $item): array => [
                'type' => (string) ($item['type'] ?? ''),
                'status' => (string) ($item['status'] ?? ''),
                'notes' => (string) ($item['notes'] ?? ''),
            ],
            $record->document_checklist,
        );
    }
}
