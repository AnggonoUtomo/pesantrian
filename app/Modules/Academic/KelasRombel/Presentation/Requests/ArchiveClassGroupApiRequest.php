<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ArchiveClassGroupApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ];
    }
}
