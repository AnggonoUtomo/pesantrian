<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Santri\Infrastructure\Repositories;

use App\Modules\Pesantrian\Santri\Application\Contracts\StudentRepository;
use App\Modules\Pesantrian\Santri\Application\DTO\PaginatedStudentData;
use App\Modules\Pesantrian\Santri\Application\DTO\StudentData;
use App\Modules\Pesantrian\Santri\Application\DTO\StudentGuardianData;
use App\Modules\Pesantrian\Santri\Application\DTO\StudentListFilter;
use App\Modules\Pesantrian\Santri\Infrastructure\Models\StudentGuardianRecord;
use App\Modules\Pesantrian\Santri\Infrastructure\Models\StudentRecord;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class EloquentStudentRepository implements StudentRepository
{
    public function paginate(StudentListFilter $filter): PaginatedStudentData
    {
        $query = StudentRecord::query()
            ->with(['guardians' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('created_at')])
            ->when($filter->search !== null, function ($query) use ($filter): void {
                $query->where(function ($query) use ($filter): void {
                    $query->where('student_no', 'like', '%'.$filter->search.'%')
                        ->orWhere('full_name', 'like', '%'.$filter->search.'%')
                        ->orWhere('preferred_name', 'like', '%'.$filter->search.'%');
                });
            })
            ->when($filter->status !== null, fn ($query) => $query->where('status', $filter->status))
            ->when($filter->primaryUnitId !== null, fn ($query) => $query->where('primary_unit_id', $filter->primaryUnitId))
            ->whereNull('archived_at')
            ->orderBy($filter->sortField, $filter->sortDirection === 'desc' ? 'desc' : 'asc');

        /** @var LengthAwarePaginator<int, StudentRecord> $page */
        $page = $query->paginate($filter->perPage, ['*'], 'page', $filter->page);

        return new PaginatedStudentData(
            data: array_map(
                fn (StudentRecord $record): StudentData => $this->map($record),
                array_values($page->items()),
            ),
            currentPage: $page->currentPage(),
            perPage: $page->perPage(),
            total: $page->total(),
            lastPage: $page->lastPage(),
        );
    }

    public function find(string $id): ?StudentData
    {
        $record = StudentRecord::query()
            ->with(['guardians' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('created_at')])
            ->whereNull('archived_at')
            ->find($id);

        return $record instanceof StudentRecord ? $this->map($record) : null;
    }

    private function map(StudentRecord $record): StudentData
    {
        $guardians = $record->guardians
            ->map(fn (StudentGuardianRecord $guardian): StudentGuardianData => $this->mapGuardian($guardian))
            ->values()
            ->all();

        $primaryGuardian = $record->guardians
            ->first(fn (StudentGuardianRecord $guardian): bool => (bool) $guardian->is_primary);

        return new StudentData(
            id: (string) $record->getKey(),
            studentNo: (string) $record->student_no,
            admissionId: $record->admission_id === null ? null : (string) $record->admission_id,
            registrationNo: $record->registration_no === null ? null : (string) $record->registration_no,
            fullName: (string) $record->full_name,
            preferredName: $record->preferred_name === null ? null : (string) $record->preferred_name,
            gender: $record->gender === null ? null : (string) $record->gender,
            birthPlace: $record->birth_place === null ? null : (string) $record->birth_place,
            birthDate: $record->birth_date?->toDateString(),
            previousSchool: $record->previous_school === null ? null : (string) $record->previous_school,
            primaryUnitId: $record->primary_unit_id === null ? null : (string) $record->primary_unit_id,
            entryDate: $record->entry_date?->toDateString(),
            status: (string) $record->status,
            primaryGuardian: $primaryGuardian instanceof StudentGuardianRecord ? $this->mapGuardian($primaryGuardian) : null,
            guardians: $guardians,
            createdAt: $record->created_at->toJSON(),
            updatedAt: $record->updated_at->toJSON(),
        );
    }

    private function mapGuardian(StudentGuardianRecord $record): StudentGuardianData
    {
        return new StudentGuardianData(
            id: (string) $record->getKey(),
            studentId: (string) $record->student_id,
            guardianName: (string) $record->guardian_name,
            guardianPhone: $record->guardian_phone === null ? null : (string) $record->guardian_phone,
            guardianRelation: $record->guardian_relation === null ? null : (string) $record->guardian_relation,
            isPrimary: (bool) $record->is_primary,
            isEmergencyContact: (bool) $record->is_emergency_contact,
        );
    }
}
