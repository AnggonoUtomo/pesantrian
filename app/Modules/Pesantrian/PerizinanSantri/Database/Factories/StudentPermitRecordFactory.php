<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PerizinanSantri\Database\Factories;

use App\Modules\Pesantrian\PerizinanSantri\Infrastructure\Models\StudentPermitRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentPermitRecord>
 */
final class StudentPermitRecordFactory extends Factory
{
    protected $model = StudentPermitRecord::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $startsAt = now()->addDays($this->faker->numberBetween(1, 14))->setTime(8, 0);

        return [
            'permit_no' => strtoupper($this->faker->unique()->bothify('IZN-######')),
            'student_id' => null,
            'student_no' => 'NIS-'.$this->faker->unique()->numerify('####'),
            'student_name' => $this->faker->name(),
            'permit_type' => $this->faker->randomElement(['leave', 'home_visit', 'sick', 'activity']),
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->addHours(8),
            'destination' => $this->faker->optional()->city(),
            'reason' => 'Keperluan '.$this->faker->sentence(4),
            'guardian_name' => $this->faker->optional()->name(),
            'guardian_phone' => $this->faker->optional()->phoneNumber(),
            'guardian_relation' => $this->faker->optional()->randomElement(['ayah', 'ibu', 'wali']),
            'status' => 'draft',
            'submitted_at' => null,
            'submitted_by' => null,
            'reviewed_at' => null,
            'reviewed_by' => null,
            'review_note' => null,
            'checked_out_at' => null,
            'checked_out_by' => null,
            'returned_at' => null,
            'returned_by' => null,
            'return_note' => null,
            'voided_at' => null,
            'voided_by' => null,
            'void_reason' => null,
            'created_by' => null,
        ];
    }
}
