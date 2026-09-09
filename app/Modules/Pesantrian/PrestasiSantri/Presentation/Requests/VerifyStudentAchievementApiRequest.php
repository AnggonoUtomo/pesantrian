<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class VerifyStudentAchievementApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'verification_note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function verificationNote(): ?string
    {
        $note = $this->validated('verification_note');

        return $note === null ? null : trim((string) $note);
    }
}
