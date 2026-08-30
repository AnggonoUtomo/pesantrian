<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PenerimaanSantri\Presentation\Requests;

use App\Modules\Pesantrian\PenerimaanSantri\Application\DTO\StudentAdmissionListFilter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ListStudentAdmissionsApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'filter' => ['nullable', 'array:status,target_unit_id,registration_fee_status'],
            'filter.status' => ['nullable', 'string', Rule::in($this->statuses())],
            'filter.target_unit_id' => ['nullable', 'ulid', Rule::exists('organization_units', 'id')],
            'filter.registration_fee_status' => ['nullable', 'string', Rule::in($this->registrationFeeStatuses())],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', Rule::in([10, 25, 50, 100])],
            'sort' => ['nullable', 'string', Rule::in(['created_at', '-created_at', 'registration_no', '-registration_no', 'candidate_name', '-candidate_name', 'registered_at', '-registered_at'])],
        ];
    }

    public function toFilter(): StudentAdmissionListFilter
    {
        $data = $this->validated();
        $filters = is_array($data['filter'] ?? null) ? $data['filter'] : [];
        $sort = (string) ($data['sort'] ?? '-created_at');

        return new StudentAdmissionListFilter(
            search: isset($data['search']) ? (string) $data['search'] : null,
            status: isset($filters['status']) ? (string) $filters['status'] : null,
            targetUnitId: isset($filters['target_unit_id']) ? (string) $filters['target_unit_id'] : null,
            registrationFeeStatus: isset($filters['registration_fee_status']) ? (string) $filters['registration_fee_status'] : null,
            page: isset($data['page']) ? (int) $data['page'] : 1,
            perPage: isset($data['per_page']) ? (int) $data['per_page'] : 25,
            sortField: ltrim($sort, '-'),
            sortDirection: str_starts_with($sort, '-') ? 'desc' : 'asc',
        );
    }

    /** @return list<string> */
    private function statuses(): array
    {
        return ['draft', 'submitted', 'verified', 'accepted', 'rejected', 'cancelled'];
    }

    /** @return list<string> */
    private function registrationFeeStatuses(): array
    {
        return ['not_required', 'pending', 'verified', 'rejected'];
    }
}
