<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Santri\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ArchiveStudentApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'reason' => ['sometimes', 'nullable', 'string', 'min:3', 'max:500'],
        ];
    }

    public function reason(): ?string
    {
        $reason = $this->validated('reason');

        return is_string($reason) && trim($reason) !== '' ? trim($reason) : null;
    }
}
