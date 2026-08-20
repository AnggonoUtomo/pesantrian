<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Presentation\Requests;

use App\Modules\System\AccessControl\Application\Contracts\RoleCatalogCapability;
use App\Modules\System\UserManagement\Application\Contracts\UserRuntimeSettings;
use App\Modules\System\UserManagement\Application\DTO\UserListFilter;
use App\Modules\System\UserManagement\Domain\ValueObjects\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

final class ListUsersApiRequest extends FormRequest
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
        $perPageOptions = array_values(array_filter(
            app(UserRuntimeSettings::class)->pagination()->perPageOptions,
            static fn (int $value): bool => $value <= 100,
        ));

        return [
            'search' => ['nullable', 'string', 'max:100'],
            'filter' => ['nullable', 'array:status,role,archive'],
            'filter.status' => ['nullable', new Enum(UserStatus::class)],
            'filter.role' => ['nullable', 'string', 'max:100', Rule::in($roleNames)],
            'filter.archive' => ['nullable', 'string', Rule::in(['all', 'active', 'archived'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', Rule::in($perPageOptions)],
            'sort' => ['nullable', 'string', Rule::in(['created_at', '-created_at', 'name', '-name'])],
        ];
    }

    public function toFilter(UserRuntimeSettings $runtimeSettings): UserListFilter
    {
        $data = $this->validated();
        $filters = is_array($data['filter'] ?? null) ? $data['filter'] : [];
        $sort = (string) ($data['sort'] ?? '-created_at');
        $pagination = $runtimeSettings->pagination();

        return UserListFilter::from(
            isset($data['search']) ? (string) $data['search'] : null,
            isset($filters['status']) ? (string) $filters['status'] : null,
            isset($filters['role']) ? (string) $filters['role'] : null,
            isset($filters['archive']) ? (string) $filters['archive'] : null,
            isset($data['page']) ? (int) $data['page'] : null,
            isset($data['per_page']) ? (int) $data['per_page'] : null,
            $pagination->defaultPerPage,
            array_values(array_filter(
                $pagination->perPageOptions,
                static fn (int $value): bool => $value <= 100,
            )),
            str_starts_with($sort, '-') ? 'desc' : 'asc',
            ltrim($sort, '-'),
        );
    }
}
