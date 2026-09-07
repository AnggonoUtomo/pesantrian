<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PerizinanSantri\Presentation\Requests;

use App\Modules\Pesantrian\PerizinanSantri\Application\DTO\StudentPermitMutationData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreStudentPermitApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'ulid'],
            'permit_type' => ['required', 'string', Rule::in(['leave', 'home_visit', 'sick', 'activity'])],
            'starts_at' => ['required', 'date_format:Y-m-d H:i:s'],
            'ends_at' => ['required', 'date_format:Y-m-d H:i:s', 'after:starts_at'],
            'destination' => ['nullable', 'string', 'max:255'],
            'reason' => ['required', 'string', 'min:3', 'max:1000'],
        ];
    }

    public function toData(): StudentPermitMutationData
    {
        $data = $this->validated();

        return new StudentPermitMutationData(
            studentId: (string) $data['student_id'],
            permitType: (string) $data['permit_type'],
            startsAt: (string) $data['starts_at'],
            endsAt: (string) $data['ends_at'],
            destination: isset($data['destination']) ? (string) $data['destination'] : null,
            reason: (string) $data['reason'],
        );
    }
}
