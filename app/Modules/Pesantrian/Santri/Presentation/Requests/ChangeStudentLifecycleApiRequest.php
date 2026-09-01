<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Santri\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ChangeStudentLifecycleApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(['active', 'inactive', 'transferred', 'graduated'])],
            'reason' => [
                Rule::requiredIf(fn (): bool => in_array($this->input('status'), ['inactive', 'transferred', 'graduated'], true)),
                'nullable',
                'string',
                'min:3',
                'max:500',
            ],
        ];
    }

    public function status(): string
    {
        return (string) $this->validated('status');
    }

    public function reason(): ?string
    {
        $reason = $this->validated('reason');

        return is_string($reason) && trim($reason) !== '' ? trim($reason) : null;
    }
}
