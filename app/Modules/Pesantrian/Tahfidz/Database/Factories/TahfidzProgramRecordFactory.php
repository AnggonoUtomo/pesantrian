<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Database\Factories;

use App\Modules\Pesantrian\Tahfidz\Infrastructure\Models\TahfidzProgramRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TahfidzProgramRecord>
 */
final class TahfidzProgramRecordFactory extends Factory
{
    protected $model = TahfidzProgramRecord::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'code' => 'THF-'.$this->faker->unique()->numerify('####'),
            'name' => 'Program Tahfidz '.$this->faker->unique()->word(),
            'description' => null,
            'status' => 'active',
            'created_by' => null,
            'archived_at' => null,
            'archived_by' => null,
        ];
    }
}
