<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PresensiSantri\Database\Factories;

use App\Modules\Pesantrian\PresensiSantri\Infrastructure\Models\StudentAttendanceSessionRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentAttendanceSessionRecord>
 */
final class StudentAttendanceSessionRecordFactory extends Factory
{
    protected $model = StudentAttendanceSessionRecord::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'attendance_date' => now()->toDateString(),
            'context_type' => 'activity',
            'context_id' => null,
            'context_name' => 'Kegiatan Umum',
            'session_code' => strtoupper($this->faker->unique()->bothify('SES-####')),
            'session_name' => 'Presensi '.$this->faker->word(),
            'status' => 'draft',
            'submitted_at' => null,
            'submitted_by' => null,
            'voided_at' => null,
            'voided_by' => null,
            'void_reason' => null,
            'created_by' => null,
        ];
    }
}
