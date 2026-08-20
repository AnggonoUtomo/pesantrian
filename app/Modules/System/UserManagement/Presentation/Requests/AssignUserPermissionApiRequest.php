<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class AssignUserPermissionApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'permission' => [
                'required',
                'string',
                'min:3',
                'max:150',
                'regex:/^[a-z0-9][a-z0-9._-]*$/',
            ],
        ];
    }

    public function permission(): string
    {
        return (string) $this->validated('permission');
    }
}
