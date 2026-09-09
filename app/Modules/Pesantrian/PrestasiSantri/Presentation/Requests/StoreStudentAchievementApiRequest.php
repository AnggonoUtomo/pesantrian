<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Presentation\Requests;

use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementMutationData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class StoreStudentAchievementApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'ulid'],
            'category_id' => ['required', 'ulid'],
            'academic_period_id' => ['nullable', 'ulid'],
            'mentor_employee_id' => ['nullable', 'ulid'],
            'title' => ['required', 'string', 'min:3', 'max:180'],
            'achievement_type' => ['required', 'string', Rule::in(['competition', 'award', 'delegation', 'publication', 'other'])],
            'level' => ['required', 'string', Rule::in(['internal', 'district', 'city', 'province', 'national', 'international'])],
            'result' => ['required', 'string', 'max:120'],
            'organizer' => ['nullable', 'string', 'max:180'],
            'event_name' => ['nullable', 'string', 'max:180'],
            'event_location' => ['nullable', 'string', 'max:180'],
            'achieved_on' => ['nullable', 'date_format:Y-m-d'],
            'period_started_on' => ['nullable', 'date_format:Y-m-d'],
            'period_ended_on' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:period_started_on'],
            'description' => ['nullable', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if (! $this->filled('achieved_on') && ! $this->filled('period_started_on')) {
                $validator->errors()->add('achieved_on', 'Isi tanggal prestasi atau awal periode prestasi.');
            }
        });
    }

    public function toData(): StudentAchievementMutationData
    {
        $data = $this->validated();

        return new StudentAchievementMutationData(
            categoryId: (string) $data['category_id'],
            categoryName: null,
            studentId: (string) $data['student_id'],
            studentNo: null,
            studentName: null,
            academicPeriodId: isset($data['academic_period_id']) ? (string) $data['academic_period_id'] : null,
            academicPeriodLabel: null,
            mentorEmployeeId: isset($data['mentor_employee_id']) ? (string) $data['mentor_employee_id'] : null,
            mentorName: null,
            title: trim((string) $data['title']),
            achievementType: (string) $data['achievement_type'],
            level: (string) $data['level'],
            result: trim((string) $data['result']),
            organizer: isset($data['organizer']) ? trim((string) $data['organizer']) : null,
            eventName: isset($data['event_name']) ? trim((string) $data['event_name']) : null,
            eventLocation: isset($data['event_location']) ? trim((string) $data['event_location']) : null,
            achievedOn: isset($data['achieved_on']) ? (string) $data['achieved_on'] : null,
            periodStartedOn: isset($data['period_started_on']) ? (string) $data['period_started_on'] : null,
            periodEndedOn: isset($data['period_ended_on']) ? (string) $data['period_ended_on'] : null,
            description: isset($data['description']) ? trim((string) $data['description']) : null,
            notes: isset($data['notes']) ? trim((string) $data['notes']) : null,
        );
    }
}
