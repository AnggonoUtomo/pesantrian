<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PresensiSantri\Database\Factories;

use App\Modules\Pesantrian\PresensiSantri\Infrastructure\Models\StudentAttendanceRevisionRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentAttendanceRevisionRecord>
 */
final class StudentAttendanceRevisionRecordFactory extends Factory
{
    protected $model = StudentAttendanceRevisionRecord::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'session_id' => null,
            'reason' => $this->faker->sentence(),
            'changed_by' => null,
            'changed_at' => now(),
            'summary' => ['changed_entries' => 1],
        ];
    }
}
