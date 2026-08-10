<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class InviteUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'], 'role' => ['nullable', 'string', 'max:100']];
    }
}
