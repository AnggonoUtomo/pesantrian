<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PresensiSantri\Database\Factories;

use App\Modules\Pesantrian\PresensiSantri\Infrastructure\Models\StudentAttendanceEntryRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentAttendanceEntryRecord>
 */
final class StudentAttendanceEntryRecordFactory extends Factory
{
    protected $model = StudentAttendanceEntryRecord::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'session_id' => null,
            'student_id' => null,
            'student_no' => 'NIS-'.$this->faker->unique()->numerify('####'),
            'student_name' => $this->faker->name(),
            'status' => 'present',
            'minutes_late' => null,
            'note' => null,
            'source_reference_type' => null,
            'source_reference_id' => null,
        ];
    }
}
