<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class UpdateDormitoryRoomApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $dormitoryId = (string) $this->route('dormitory');
        $roomId = (string) $this->route('room');

        return [
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:40',
                'regex:/^[A-Z0-9][A-Z0-9_-]*$/',
                Rule::unique('dormitory_rooms', 'code')
                    ->where('dormitory_id', $dormitoryId)
                    ->ignore($roomId),
            ],
            'name' => ['sometimes', 'required', 'string', 'min:2', 'max:120'],
            'capacity' => ['sometimes', 'required', 'integer', 'min:1', 'max:200'],
            'status' => ['sometimes', 'required', 'string', Rule::in($this->statuses())],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->hasAny(['code', 'name', 'capacity', 'status'])) {
                $validator->errors()->add('payload', 'Minimal satu field perubahan wajib diisi.');
            }
        });
    }

    /** @return array<string, string|int> */
    public function changes(): array
    {
        $validated = $this->validated();
        $changes = [];

        foreach (['code', 'name', 'status'] as $field) {
            if (array_key_exists($field, $validated)) {
                $changes[$field] = (string) $validated[$field];
            }
        }

        if (array_key_exists('capacity', $validated)) {
            $changes['capacity'] = (int) $validated['capacity'];
        }

        return $changes;
    }

    /** @return list<string> */
    private function statuses(): array
    {
        return ['active', 'inactive'];
    }
}
