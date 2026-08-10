<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

final class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>|string> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::min(8)],
            'status' => ['nullable', 'string', 'in:active,inactive,suspended'],
            'role' => ['nullable', 'string', 'max:100'],
        ];
    }
}
