<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Presentation\Requests;

use App\Modules\Pesantrian\Asrama\Application\DTO\UpsertDormitoryRoomData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreDormitoryRoomApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $dormitoryId = (string) $this->route('dormitory');

        return [
            'code' => [
                'required',
                'string',
                'max:40',
                'regex:/^[A-Z0-9][A-Z0-9_-]*$/',
                Rule::unique('dormitory_rooms', 'code')->where('dormitory_id', $dormitoryId),
            ],
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'capacity' => ['required', 'integer', 'min:1', 'max:200'],
            'status' => ['required', 'string', Rule::in($this->statuses())],
        ];
    }

    public function toData(): UpsertDormitoryRoomData
    {
        $data = $this->validated();

        return new UpsertDormitoryRoomData(
            dormitoryId: (string) $this->route('dormitory'),
            code: (string) $data['code'],
            name: (string) $data['name'],
            capacity: (int) $data['capacity'],
            status: (string) $data['status'],
        );
    }

    /** @return list<string> */
    private function statuses(): array
    {
        return ['active', 'inactive'];
    }
}
