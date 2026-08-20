<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Presentation\Requests;

use App\Modules\System\AccessControl\Application\Contracts\RoleCatalogCapability;
use App\Modules\System\UserManagement\Application\DTO\CreateUserData;
use App\Modules\System\UserManagement\Domain\ValueObjects\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;

final class StoreUserApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $roleNames = array_map(
            static fn ($role): string => $role->name,
            app(RoleCatalogCapability::class)->listRoles(),
        );

        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', Password::min(8)],
            'status' => ['nullable', new Enum(UserStatus::class)],
            'role' => ['nullable', 'string', 'max:100', Rule::in($roleNames)],
        ];
    }

    public function toData(): CreateUserData
    {
        $data = $this->validated();

        return new CreateUserData(
            name: (string) $data['name'],
            email: (string) $data['email'],
            password: (string) $data['password'],
            status: UserStatus::from((string) ($data['status'] ?? 'active')),
            role: isset($data['role']) ? (string) $data['role'] : null,
        );
    }
}
