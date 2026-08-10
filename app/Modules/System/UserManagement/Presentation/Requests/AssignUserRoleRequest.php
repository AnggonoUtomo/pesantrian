<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class AssignUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'roles' => ['required', 'array', 'min:1', 'max:20'],
            'roles.*' => ['required', 'string', 'distinct', 'max:100'],
        ];
    }
}
