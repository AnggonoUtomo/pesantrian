<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class TransferStudentApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'target_class_group_id' => ['required', 'ulid', Rule::exists('class_groups', 'id')],
            'joined_on' => ['required', 'date'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ];
    }
}
