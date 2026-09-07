<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PerizinanSantri\Presentation\Requests;

use App\Modules\Pesantrian\PerizinanSantri\Application\DTO\StudentPermitMutationData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateStudentPermitApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'student_id' => ['sometimes', 'ulid'],
            'permit_type' => ['sometimes', 'string', Rule::in(['leave', 'home_visit', 'sick', 'activity'])],
            'starts_at' => ['sometimes', 'date_format:Y-m-d H:i:s'],
            'ends_at' => ['sometimes', 'date_format:Y-m-d H:i:s'],
            'destination' => ['nullable', 'string', 'max:255'],
            'reason' => ['sometimes', 'string', 'min:3', 'max:1000'],
            'revision_reason' => ['required', 'string', 'min:3', 'max:500'],
        ];
    }

    public function toData(): StudentPermitMutationData
    {
        $data = $this->validated();

        return new StudentPermitMutationData(
            studentId: isset($data['student_id']) ? (string) $data['student_id'] : null,
            permitType: isset($data['permit_type']) ? (string) $data['permit_type'] : null,
            startsAt: isset($data['starts_at']) ? (string) $data['starts_at'] : null,
            endsAt: isset($data['ends_at']) ? (string) $data['ends_at'] : null,
            destination: isset($data['destination']) ? (string) $data['destination'] : null,
            reason: isset($data['reason']) ? (string) $data['reason'] : null,
        );
    }

    public function revisionReason(): string
    {
        return (string) $this->validated('revision_reason');
    }
}
