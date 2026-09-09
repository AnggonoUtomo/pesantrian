<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Database\Factories;

use App\Modules\Pesantrian\PrestasiSantri\Infrastructure\Models\StudentAchievementRevisionRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentAchievementRevisionRecord>
 */
final class StudentAchievementRevisionRecordFactory extends Factory
{
    protected $model = StudentAchievementRevisionRecord::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'achievement_id' => null,
            'from_status' => 'draft',
            'to_status' => 'submitted',
            'reason' => $this->faker->randomElement([
                'Catatan prestasi diajukan untuk verifikasi.',
                'Koreksi hasil prestasi.',
                'Catatan prestasi sudah diverifikasi.',
            ]),
            'changed_by' => null,
            'changed_at' => now(),
            'summary' => ['changed_fields' => ['status']],
        ];
    }
}
