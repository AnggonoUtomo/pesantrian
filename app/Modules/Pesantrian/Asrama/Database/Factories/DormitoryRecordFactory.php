<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Database\Factories;

use App\Modules\Pesantrian\Asrama\Infrastructure\Models\DormitoryRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DormitoryRecord>
 */
final class DormitoryRecordFactory extends Factory
{
    protected $model = DormitoryRecord::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'unit_id' => null,
            'code' => 'ASR-'.$this->faker->unique()->bothify('??-##'),
            'name' => 'Asrama '.$this->faker->unique()->word(),
            'gender_policy' => $this->faker->randomElement(['male', 'female', 'mixed', 'unspecified']),
            'description' => null,
            'status' => 'active',
            'archived_at' => null,
            'archived_by' => null,
        ];
    }
}
