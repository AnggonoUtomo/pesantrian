<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod\Presentation\Requests;

use App\Modules\Academic\AcademicPeriod\Application\DTO\UpsertAcademicYearData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreAcademicYearApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:40', 'regex:/^[0-9]{4}-[0-9]{4}$/', Rule::unique('academic_years', 'code')],
            'name' => ['required', 'string', 'min:2', 'max:180'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after:starts_on'],
            'status' => ['required', 'string', Rule::in($this->statuses())],
        ];
    }

    public function toData(): UpsertAcademicYearData
    {
        $data = $this->validated();

        return new UpsertAcademicYearData(
            code: (string) $data['code'],
            name: (string) $data['name'],
            startsOn: (string) $data['starts_on'],
            endsOn: (string) $data['ends_on'],
            status: (string) $data['status'],
        );
    }

    /** @return list<string> */
    private function statuses(): array
    {
        return ['draft', 'active', 'closed'];
    }
}
