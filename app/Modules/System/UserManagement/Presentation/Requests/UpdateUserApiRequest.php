<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Presentation\Requests;

use App\Modules\System\UserManagement\Application\DTO\UpdateUserData;
use App\Modules\System\UserManagement\Application\DTO\UserData;
use App\Modules\System\UserManagement\Domain\ValueObjects\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;

final class UpdateUserApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'min:2', 'max:100'],
            'email' => ['sometimes', 'required', 'email', 'max:255'],
            'status' => ['sometimes', 'required', new Enum(UserStatus::class)],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->hasAny(['name', 'email', 'status'])) {
                $validator->errors()->add('payload', 'Minimal satu field perubahan wajib diisi.');
            }
        });
    }

    public function hasProfileChanges(): bool
    {
        return $this->hasAny(['name', 'email']);
    }

    public function profileData(UserData $current): UpdateUserData
    {
        $data = $this->validated();

        return new UpdateUserData(
            name: isset($data['name']) ? (string) $data['name'] : $current->name,
            email: isset($data['email']) ? (string) $data['email'] : $current->email,
        );
    }

    public function status(): ?UserStatus
    {
        $status = $this->validated('status');

        return is_string($status) ? UserStatus::from($status) : null;
    }
}
