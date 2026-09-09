<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Presentation\Requests;

use App\Modules\Pesantrian\PrestasiSantri\Application\DTO\StudentAchievementMutationData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateStudentAchievementApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'student_id' => ['sometimes', 'ulid'],
            'category_id' => ['sometimes', 'ulid'],
            'academic_period_id' => ['sometimes', 'nullable', 'ulid'],
            'mentor_employee_id' => ['sometimes', 'nullable', 'ulid'],
            'title' => ['sometimes', 'string', 'min:3', 'max:180'],
            'achievement_type' => ['sometimes', 'string', Rule::in(['competition', 'award', 'delegation', 'publication', 'other'])],
            'level' => ['sometimes', 'string', Rule::in(['internal', 'district', 'city', 'province', 'national', 'international'])],
            'result' => ['sometimes', 'string', 'max:120'],
            'organizer' => ['sometimes', 'nullable', 'string', 'max:180'],
            'event_name' => ['sometimes', 'nullable', 'string', 'max:180'],
            'event_location' => ['sometimes', 'nullable', 'string', 'max:180'],
            'achieved_on' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
            'period_started_on' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
            'period_ended_on' => ['sometimes', 'nullable', 'date_format:Y-m-d', 'after_or_equal:period_started_on'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'revision_reason' => ['required', 'string', 'min:5', 'max:500'],
        ];
    }

    public function toData(): StudentAchievementMutationData
    {
        $data = $this->validated();

        return new StudentAchievementMutationData(
            categoryId: isset($data['category_id']) ? (string) $data['category_id'] : null,
            categoryName: null,
            studentId: isset($data['student_id']) ? (string) $data['student_id'] : null,
            studentNo: null,
            studentName: null,
            academicPeriodId: isset($data['academic_period_id']) ? (string) $data['academic_period_id'] : null,
            academicPeriodLabel: null,
            mentorEmployeeId: isset($data['mentor_employee_id']) ? (string) $data['mentor_employee_id'] : null,
            mentorName: null,
            title: isset($data['title']) ? trim((string) $data['title']) : null,
            achievementType: isset($data['achievement_type']) ? (string) $data['achievement_type'] : null,
            level: isset($data['level']) ? (string) $data['level'] : null,
            result: isset($data['result']) ? trim((string) $data['result']) : null,
            organizer: array_key_exists('organizer', $data) && $data['organizer'] !== null ? trim((string) $data['organizer']) : null,
            eventName: array_key_exists('event_name', $data) && $data['event_name'] !== null ? trim((string) $data['event_name']) : null,
            eventLocation: array_key_exists('event_location', $data) && $data['event_location'] !== null ? trim((string) $data['event_location']) : null,
            achievedOn: isset($data['achieved_on']) ? (string) $data['achieved_on'] : null,
            periodStartedOn: isset($data['period_started_on']) ? (string) $data['period_started_on'] : null,
            periodEndedOn: isset($data['period_ended_on']) ? (string) $data['period_ended_on'] : null,
            description: array_key_exists('description', $data) && $data['description'] !== null ? trim((string) $data['description']) : null,
            notes: array_key_exists('notes', $data) && $data['notes'] !== null ? trim((string) $data['notes']) : null,
        );
    }

    public function revisionReason(): string
    {
        return trim((string) $this->validated('revision_reason'));
    }
}
