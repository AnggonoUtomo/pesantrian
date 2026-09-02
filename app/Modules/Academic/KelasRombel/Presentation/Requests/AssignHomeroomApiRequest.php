<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AssignHomeroomApiRequest extends FormRequest
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
            'assigned_on' => ['required', 'date'],
        ];
    }
}
