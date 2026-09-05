<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AssignDormitorySupervisorApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'ulid', Rule::exists('employees', 'id')],
            'dormitory_room_id' => ['nullable', 'ulid', Rule::exists('dormitory_rooms', 'id')],
            'role' => ['required', 'string', Rule::in(['musyrif', 'pembina'])],
            'started_at' => ['required', 'date'],
        ];
    }
}
