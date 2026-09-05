<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Database\Factories;

use App\Modules\Pesantrian\Asrama\Infrastructure\Models\DormitoryRoomRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DormitoryRoomRecord>
 */
final class DormitoryRoomRecordFactory extends Factory
{
    protected $model = DormitoryRoomRecord::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'dormitory_id' => null,
            'code' => $this->faker->unique()->bothify('K-##'),
            'name' => 'Kamar '.$this->faker->unique()->numberBetween(1, 99),
            'capacity' => $this->faker->numberBetween(4, 16),
            'status' => 'active',
            'archived_at' => null,
            'archived_by' => null,
        ];
    }
}
