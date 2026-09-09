<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Database\Factories;

use App\Modules\Pesantrian\PrestasiSantri\Infrastructure\Models\StudentAchievementCategoryRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentAchievementCategoryRecord>
 */
final class StudentAchievementCategoryRecordFactory extends Factory
{
    protected $model = StudentAchievementCategoryRecord::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->bothify('PRS-???-###')),
            'name' => $this->faker->randomElement([
                'Akademik',
                'Tahfidz',
                'Olahraga',
                'Seni',
                'Bahasa',
                'Kepemimpinan',
            ]),
            'description' => $this->faker->optional()->sentence(),
            'status' => 'active',
            'archived_at' => null,
            'archived_by' => null,
            'archive_reason' => null,
        ];
    }
}
