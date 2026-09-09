<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Database\Factories;

use App\Modules\Pesantrian\PrestasiSantri\Infrastructure\Models\StudentAchievementRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentAchievementRecord>
 */
final class StudentAchievementRecordFactory extends Factory
{
    protected $model = StudentAchievementRecord::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'achievement_no' => strtoupper($this->faker->unique()->bothify('PRS-######')),
            'category_id' => null,
            'category_name' => 'Akademik',
            'student_id' => null,
            'student_no' => 'NIS-'.$this->faker->unique()->numerify('####'),
            'student_name' => $this->faker->name(),
            'academic_period_id' => null,
            'academic_period_label' => null,
            'mentor_employee_id' => null,
            'mentor_name' => null,
            'title' => $this->faker->randomElement([
                'Juara Olimpiade Matematika',
                'Peserta Terbaik Pidato Bahasa Arab',
                'Juara Lomba Kaligrafi',
                'Delegasi Pramuka Pesantren',
            ]),
            'achievement_type' => $this->faker->randomElement(['competition', 'award', 'delegation', 'publication']),
            'level' => $this->faker->randomElement(['internal', 'district', 'city', 'province', 'national']),
            'result' => $this->faker->randomElement(['Juara 1', 'Juara 2', 'Finalis', 'Peserta Terbaik', 'Delegasi']),
            'organizer' => $this->faker->optional()->company(),
            'event_name' => $this->faker->optional()->sentence(3),
            'event_location' => $this->faker->optional()->city(),
            'achieved_on' => now()->subDays($this->faker->numberBetween(0, 90))->toDateString(),
            'period_started_on' => null,
            'period_ended_on' => null,
            'description' => $this->faker->optional()->sentence(10),
            'notes' => null,
            'status' => 'draft',
            'submitted_at' => null,
            'submitted_by' => null,
            'verified_at' => null,
            'verified_by' => null,
            'verification_note' => null,
            'voided_at' => null,
            'voided_by' => null,
            'void_reason' => null,
            'created_by' => null,
        ];
    }
}
