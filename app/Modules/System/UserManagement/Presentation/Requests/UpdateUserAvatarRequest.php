<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateUserAvatarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['avatar' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048']];
    }
}
