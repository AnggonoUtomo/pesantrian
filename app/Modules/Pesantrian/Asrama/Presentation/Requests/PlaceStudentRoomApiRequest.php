<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class PlaceStudentRoomApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'ulid', Rule::exists('students', 'id')],
            'dormitory_room_id' => ['required', 'ulid', Rule::exists('dormitory_rooms', 'id')],
            'started_at' => ['required', 'date'],
        ];
    }
}
