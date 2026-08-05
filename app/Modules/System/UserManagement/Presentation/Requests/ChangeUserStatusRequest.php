<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Presentation\Requests;

use App\Modules\System\UserManagement\Domain\ValueObjects\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

final class ChangeUserStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return ['status' => ['required', new Enum(UserStatus::class)]];
    }
}
