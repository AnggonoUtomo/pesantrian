<?php

declare(strict_types=1);

namespace App\Modules\System\UserManagement\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class BulkUserLifecycleRequest extends FormRequest
{
    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'user_ids' => ['required', 'array', 'min:1', 'max:50'],
            'user_ids.*' => ['required', 'string', 'ulid', 'distinct'],
        ];
    }
}
