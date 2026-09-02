<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class EndHomeroomApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'ended_on' => ['required', 'date'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ];
    }
}
