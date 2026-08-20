<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class UpdateRoleApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[A-Za-z0-9][A-Za-z0-9 _-]*$/',
            ],
            'permissions' => ['sometimes', 'required', 'array'],
            'permissions.*' => ['string', 'distinct', 'exists:permissions,name'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->hasAny(['name', 'permissions'])) {
                $validator->errors()->add('payload', 'Minimal satu field perubahan wajib diisi.');
            }
        });
    }

    public function name(): ?string
    {
        $name = $this->validated('name');

        return is_string($name) ? $name : null;
    }

    /** @return list<string>|null */
    public function permissions(): ?array
    {
        if (! $this->has('permissions')) {
            return null;
        }

        $permissions = $this->validated('permissions');

        return is_array($permissions) ? array_values($permissions) : null;
    }
}
