<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Database\Factories;

use App\Modules\Pesantrian\KedisiplinanSantri\Infrastructure\Models\StudentDisciplineRevisionRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentDisciplineRevisionRecord>
 */
final class StudentDisciplineRevisionRecordFactory extends Factory
{
    protected $model = StudentDisciplineRevisionRecord::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'case_id' => null,
            'reason' => $this->faker->randomElement([
                'Koreksi kronologi kejadian.',
                'Menambahkan tindakan pembinaan.',
                'Koreksi status penyelesaian.',
            ]),
            'changed_by' => null,
            'changed_at' => now(),
            'summary' => ['changed_fields' => ['status']],
        ];
    }
}
