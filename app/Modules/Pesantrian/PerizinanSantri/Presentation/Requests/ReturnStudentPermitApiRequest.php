<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PerizinanSantri\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ReturnStudentPermitApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'returned_at' => ['required', 'date_format:Y-m-d H:i:s'],
            'return_note' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function returnedAt(): string
    {
        return (string) $this->validated('returned_at');
    }

    public function returnNote(): ?string
    {
        $data = $this->validated();

        return isset($data['return_note']) ? (string) $data['return_note'] : null;
    }
}
