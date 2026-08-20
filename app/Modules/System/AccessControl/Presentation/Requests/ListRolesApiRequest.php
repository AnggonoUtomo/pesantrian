<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Presentation\Requests;

use App\Modules\System\AccessControl\Application\DTO\RoleListFilter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ListRolesApiRequest extends FormRequest
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
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'sort' => ['nullable', 'string', Rule::in(['name', '-name'])],
        ];
    }

    public function toFilter(): RoleListFilter
    {
        $data = $this->validated();

        return RoleListFilter::from(
            isset($data['search']) ? (string) $data['search'] : null,
            isset($data['page']) ? (int) $data['page'] : null,
            isset($data['per_page']) ? (int) $data['per_page'] : null,
            isset($data['sort']) ? (string) $data['sort'] : null,
        );
    }
}
