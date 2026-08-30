<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PenerimaanSantri\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class TransitionStudentAdmissionApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [];
    }
}
