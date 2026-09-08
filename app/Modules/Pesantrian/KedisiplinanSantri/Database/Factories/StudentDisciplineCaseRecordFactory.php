<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Database\Factories;

use App\Modules\Pesantrian\KedisiplinanSantri\Infrastructure\Models\StudentDisciplineCaseRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentDisciplineCaseRecord>
 */
final class StudentDisciplineCaseRecordFactory extends Factory
{
    protected $model = StudentDisciplineCaseRecord::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $severity = $this->faker->randomElement(['minor', 'moderate', 'major']);

        return [
            'case_no' => strtoupper($this->faker->unique()->bothify('DIS-######')),
            'student_id' => null,
            'student_no' => 'NIS-'.$this->faker->unique()->numerify('####'),
            'student_name' => $this->faker->name(),
            'unit_id' => null,
            'unit_name' => null,
            'category_id' => null,
            'category_name' => 'Terlambat kegiatan',
            'severity' => $severity,
            'points' => match ($severity) {
                'major' => 50,
                'moderate' => 15,
                default => 5,
            },
            'occurred_at' => now()->subDays($this->faker->numberBetween(0, 14)),
            'location' => $this->faker->optional()->randomElement(['Masjid', 'Asrama', 'Kelas', 'Lapangan']),
            'description' => 'Kronologi: '.$this->faker->sentence(8),
            'reported_by' => null,
            'assigned_employee_id' => null,
            'assigned_employee_name' => null,
            'status' => 'draft',
            'submitted_at' => null,
            'reviewed_at' => null,
            'reviewed_by' => null,
            'review_note' => null,
            'action_plan' => null,
            'action_assigned_at' => null,
            'resolved_at' => null,
            'resolved_by' => null,
            'resolution_note' => null,
            'voided_at' => null,
            'voided_by' => null,
            'void_reason' => null,
            'created_by' => null,
        ];
    }
}
