<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StartImpersonationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:3', 'max:500'],
        ];
    }
}
