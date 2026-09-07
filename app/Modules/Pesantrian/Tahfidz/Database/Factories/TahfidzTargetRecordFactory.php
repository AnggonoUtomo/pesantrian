<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Database\Factories;

use App\Modules\Pesantrian\Tahfidz\Infrastructure\Models\TahfidzTargetRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TahfidzTargetRecord>
 */
final class TahfidzTargetRecordFactory extends Factory
{
    protected $model = TahfidzTargetRecord::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'program_id' => null,
            'student_id' => null,
            'student_no' => 'NIS-'.$this->faker->unique()->numerify('####'),
            'student_name' => $this->faker->name(),
            'academic_period_id' => null,
            'period_label' => null,
            'target_juz' => $this->faker->numberBetween(1, 30),
            'target_surah' => null,
            'target_ayah_from' => null,
            'target_ayah_to' => null,
            'target_note' => null,
            'status' => 'active',
            'created_by' => null,
        ];
    }
}
