<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Santri\Database\Factories;

use App\Modules\Pesantrian\Santri\Infrastructure\Models\StudentRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentRecord>
 */
final class StudentRecordFactory extends Factory
{
    protected $model = StudentRecord::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'student_no' => 'NIS-'.$this->faker->unique()->numerify('####'),
            'admission_id' => null,
            'registration_no' => null,
            'full_name' => $this->faker->name(),
            'preferred_name' => null,
            'gender' => $this->faker->randomElement(['male', 'female']),
            'birth_place' => $this->faker->city(),
            'birth_date' => $this->faker->dateTimeBetween('-18 years', '-6 years')->format('Y-m-d'),
            'previous_school' => null,
            'primary_unit_id' => null,
            'entry_date' => now()->toDateString(),
            'status' => 'active',
            'status_reason' => null,
            'status_changed_at' => null,
            'status_changed_by' => null,
            'archived_at' => null,
            'archived_by' => null,
        ];
    }
}
