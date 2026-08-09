<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Presentation\Requests;

use App\Modules\System\AccessControl\Application\Contracts\RoleCatalogCapability;
use App\Modules\System\UserManagement\Domain\ValueObjects\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

final class ListUsersRequest extends FormRequest
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
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', new Enum(UserStatus::class)],
            'role' => ['nullable', 'string', 'max:100', Rule::in($roleNames)],
            'archive' => ['nullable', 'string', Rule::in(['all', 'active', 'archived'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', Rule::in([5, 10, 25, 50])],
        ];
    }
}
