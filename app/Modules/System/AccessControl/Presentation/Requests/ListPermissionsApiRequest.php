<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Presentation\Requests;

use App\Modules\System\AccessControl\Application\DTO\PermissionListFilter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ListPermissionsApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'filter' => ['nullable', 'array:module'],
            'filter.module' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'sort' => ['nullable', 'string', Rule::in(['name', '-name'])],
        ];
    }

    public function toFilter(): PermissionListFilter
    {
        $data = $this->validated();
        $filters = is_array($data['filter'] ?? null) ? $data['filter'] : [];

        return PermissionListFilter::from(
            isset($data['search']) ? (string) $data['search'] : null,
            isset($filters['module']) ? (string) $filters['module'] : null,
            isset($data['page']) ? (int) $data['page'] : null,
            isset($data['per_page']) ? (int) $data['per_page'] : null,
            isset($data['sort']) ? (string) $data['sort'] : null,
        );
    }
}
