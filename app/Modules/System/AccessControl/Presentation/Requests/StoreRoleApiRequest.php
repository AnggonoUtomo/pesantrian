<?php

declare(strict_types=1);

namespace App\Modules\System\AccessControl\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreRoleApiRequest extends FormRequest
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
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[A-Za-z0-9][A-Za-z0-9 _-]*$/',
            ],
        ];
    }

    public function name(): string
    {
        return (string) $this->validated('name');
    }
}
