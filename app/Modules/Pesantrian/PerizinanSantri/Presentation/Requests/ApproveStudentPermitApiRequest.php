<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PerizinanSantri\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ApproveStudentPermitApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'review_note' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function reviewNote(): ?string
    {
        $data = $this->validated();

        return isset($data['review_note']) ? (string) $data['review_note'] : null;
    }
}
