<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PerizinanSantri\Database\Factories;

use App\Modules\Pesantrian\PerizinanSantri\Infrastructure\Models\StudentPermitRevisionRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentPermitRevisionRecord>
 */
final class StudentPermitRevisionRecordFactory extends Factory
{
    protected $model = StudentPermitRevisionRecord::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'permit_id' => null,
            'reason' => 'Koreksi data izin santri.',
            'changed_by' => null,
            'changed_at' => now(),
            'summary' => [
                'changed_fields' => ['status'],
            ],
        ];
    }
}
