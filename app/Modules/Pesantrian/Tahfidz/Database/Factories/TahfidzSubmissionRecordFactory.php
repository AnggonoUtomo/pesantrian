<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Database\Factories;

use App\Modules\Pesantrian\Tahfidz\Infrastructure\Models\TahfidzSubmissionRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TahfidzSubmissionRecord>
 */
final class TahfidzSubmissionRecordFactory extends Factory
{
    protected $model = TahfidzSubmissionRecord::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'program_id' => null,
            'target_id' => null,
            'student_id' => null,
            'student_no' => 'NIS-'.$this->faker->unique()->numerify('####'),
            'student_name' => $this->faker->name(),
            'supervisor_id' => null,
            'supervisor_name' => null,
            'submission_date' => now()->toDateString(),
            'type' => 'new_memorization',
            'juz' => $this->faker->numberBetween(1, 30),
            'surah' => null,
            'ayah_from' => null,
            'ayah_to' => null,
            'status' => 'draft',
            'quality_note' => null,
            'created_by' => null,
            'reviewed_at' => null,
            'reviewed_by' => null,
            'voided_at' => null,
            'voided_by' => null,
            'void_reason' => null,
        ];
    }
}
