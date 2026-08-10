<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Presentation\Requests;

use App\Modules\System\SystemSetting\Application\DTO\UpdateSystemSettingCategoryData;
use App\Modules\System\SystemSetting\Application\Services\SettingDefinitionRegistry;
use App\Modules\System\SystemSetting\Domain\Exceptions\UnknownSettingDefinition;
use App\Modules\System\SystemSetting\Domain\ValueObjects\SettingCategory;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;
use InvalidArgumentException;

final class UpdateSystemSettingCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'updates' => ['required', 'array', 'min:1'],
            'updates.*.key' => ['required', 'string', 'regex:/^[a-z][a-z0-9_]*(\.[a-z][a-z0-9_]*)+$/'],
            'updates.*.value' => ['present'],
            'reason' => [
                'required',
                'string',
                'max:500',
                static function (string $attribute, mixed $value, Closure $fail): void {
                    $sanitized = preg_replace('/[\x00-\x1F\x7F]/u', '', strip_tags((string) $value)) ?? '';

                    if (trim($sanitized) === '') {
                        $fail('Reason wajib berisi penjelasan yang valid.');
                    }
                },
            ],
            'correlation_id' => ['nullable', 'ulid'],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $category = SettingCategory::tryFrom((string) $this->route('category'));

                if ($category === null) {
                    $validator->errors()->add('category', 'Kategori SystemSetting tidak valid.');

                    return;
                }

                $keys = [];

                foreach ($this->input('updates', []) as $index => $update) {
                    $key = is_array($update) ? ($update['key'] ?? null) : null;

                    if (! is_string($key)) {
                        continue;
                    }

                    if (! $category->owns($key)) {
                        $validator->errors()->add('updates', 'Semua key wajib berada pada kategori yang dipilih.');

                        continue;
                    }

                    if (in_array($key, $keys, true)) {
                        $validator->errors()->add('updates', 'Key SystemSetting tidak boleh duplikat.');

                        continue;
                    }

                    $keys[] = $key;

                    try {
                        app(SettingDefinitionRegistry::class)
                            ->definition($key)
                            ->normalize($update['value'] ?? null);
                    } catch (UnknownSettingDefinition) {
                        // Controller mengubah unknown key menjadi response 404.
                    } catch (InvalidArgumentException $exception) {
                        $validator->errors()->add("updates.{$index}.value", $exception->getMessage());
                    }
                }
            },
        ];
    }

    public function toData(string $category): UpdateSystemSettingCategoryData
    {
        /** @var list<array{key: string, value: mixed}> $updates */
        $updates = $this->validated('updates');

        return new UpdateSystemSettingCategoryData(
            category: $category,
            updates: array_column($updates, 'value', 'key'),
            reason: (string) $this->validated('reason'),
            correlationId: (string) ($this->validated('correlation_id') ?? Str::ulid()),
        );
    }
}
