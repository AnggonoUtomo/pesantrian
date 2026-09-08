<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Database\Factories;

use App\Modules\Pesantrian\KedisiplinanSantri\Infrastructure\Models\StudentDisciplineCategoryRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentDisciplineCategoryRecord>
 */
final class StudentDisciplineCategoryRecordFactory extends Factory
{
    protected $model = StudentDisciplineCategoryRecord::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        $severity = $this->faker->randomElement(['minor', 'moderate', 'major']);

        return [
            'code' => strtoupper($this->faker->unique()->bothify('DIS-???-###')),
            'name' => $this->faker->randomElement([
                'Terlambat kegiatan',
                'Meninggalkan kegiatan tanpa izin',
                'Melanggar tata tertib',
                'Pelanggaran asrama',
            ]),
            'description' => $this->faker->optional()->sentence(),
            'default_severity' => $severity,
            'default_points' => match ($severity) {
                'major' => 50,
                'moderate' => 15,
                default => 5,
            },
            'status' => 'active',
        ];
    }
}
