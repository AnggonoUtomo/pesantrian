<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateStudentAchievementCategoryApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'code' => ['sometimes', 'string', 'max:40', 'regex:/^[A-Z0-9_\\-]+$/', Rule::unique('student_achievement_categories', 'code')->ignore($this->route('category'))],
            'name' => ['sometimes', 'string', 'max:120'],
            'description' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }

    /** @return array<string, string|null> */
    public function changes(): array
    {
        $data = $this->validated();
        $changes = [];

        if (array_key_exists('code', $data)) {
            $changes['code'] = strtoupper(trim((string) $data['code']));
        }

        if (array_key_exists('name', $data)) {
            $changes['name'] = trim((string) $data['name']);
        }

        if (array_key_exists('description', $data)) {
            $changes['description'] = $data['description'] === null ? null : trim((string) $data['description']);
        }

        return $changes;
    }
}
