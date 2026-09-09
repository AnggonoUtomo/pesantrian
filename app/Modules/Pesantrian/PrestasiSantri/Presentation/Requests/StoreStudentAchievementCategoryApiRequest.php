<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Presentation\Requests;

use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\UpsertStudentAchievementCategoryData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreStudentAchievementCategoryApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:40', 'regex:/^[A-Z0-9_\\-]+$/', Rule::unique('student_achievement_categories', 'code')],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function toData(): UpsertStudentAchievementCategoryData
    {
        $data = $this->validated();

        return new UpsertStudentAchievementCategoryData(
            code: strtoupper(trim((string) $data['code'])),
            name: trim((string) $data['name']),
            description: isset($data['description']) ? trim((string) $data['description']) : null,
        );
    }
}
