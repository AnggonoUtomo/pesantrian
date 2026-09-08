<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class VoidStudentDisciplineCaseApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'void_reason' => ['required', 'string', 'min:5', 'max:2000'],
        ];
    }

    public function voidReason(): string
    {
        return trim((string) $this->validated('void_reason'));
    }
}
