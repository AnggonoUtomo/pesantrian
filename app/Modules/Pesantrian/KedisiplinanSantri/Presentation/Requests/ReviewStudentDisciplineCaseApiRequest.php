<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ReviewStudentDisciplineCaseApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'review_note' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function reviewNote(): string
    {
        return trim((string) $this->validated('review_note'));
    }
}
