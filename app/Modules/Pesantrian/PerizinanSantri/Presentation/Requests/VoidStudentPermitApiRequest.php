<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PerizinanSantri\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class VoidStudentPermitApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:3', 'max:500'],
        ];
    }

    public function reason(): string
    {
        return (string) $this->validated('reason');
    }
}
