<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class VoidStudentAchievementApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'void_reason' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function voidReason(): string
    {
        return trim((string) $this->validated('void_reason'));
    }
}
