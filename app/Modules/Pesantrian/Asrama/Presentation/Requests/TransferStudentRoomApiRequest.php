<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class TransferStudentRoomApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'target_room_id' => ['required', 'ulid', Rule::exists('dormitory_rooms', 'id')],
            'started_at' => ['required', 'date'],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ];
    }
}
