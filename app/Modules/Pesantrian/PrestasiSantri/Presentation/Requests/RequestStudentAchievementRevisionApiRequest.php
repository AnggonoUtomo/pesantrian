<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class RequestStudentAchievementRevisionApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'verification_note' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function verificationNote(): string
    {
        return trim((string) $this->validated('verification_note'));
    }
}
