<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>|string> */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[A-Za-z0-9][A-Za-z0-9 _-]*$/',
                Rule::unique('roles', 'name')->where('guard_name', 'web'),
            ],
        ];
    }
}
