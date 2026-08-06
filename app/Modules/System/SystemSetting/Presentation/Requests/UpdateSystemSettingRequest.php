<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Presentation\Requests;

use App\Modules\System\SystemSetting\Application\DTO\UpdateSystemSettingData;
use App\Modules\System\SystemSetting\Application\Services\SettingDefinitionRegistry;
use App\Modules\System\SystemSetting\Domain\Exceptions\UnknownSettingDefinition;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;
use InvalidArgumentException;

final class UpdateSystemSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'value' => ['present'],
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
                try {
                    app(SettingDefinitionRegistry::class)
                        ->definition((string) $this->route('key'))
                        ->normalize($this->input('value'));
                } catch (UnknownSettingDefinition) {
                    // Controller mengubah unknown key menjadi response 404.
                } catch (InvalidArgumentException $exception) {
                    $validator->errors()->add('value', $exception->getMessage());
                }
            },
        ];
    }

    public function toData(string $key): UpdateSystemSettingData
    {
        return new UpdateSystemSettingData(
            key: $key,
            value: $this->validated('value'),
            reason: (string) $this->validated('reason'),
            correlationId: (string) ($this->validated('correlation_id') ?? Str::ulid()),
        );
    }
}
