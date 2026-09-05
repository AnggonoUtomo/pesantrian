<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Database\Factories;

use App\Modules\Pesantrian\Asrama\Infrastructure\Models\DormitorySupervisorAssignmentRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DormitorySupervisorAssignmentRecord>
 */
final class DormitorySupervisorAssignmentRecordFactory extends Factory
{
    protected $model = DormitorySupervisorAssignmentRecord::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'employee_id' => null,
            'dormitory_id' => null,
            'dormitory_room_id' => null,
            'employee_name' => $this->faker->name(),
            'role' => 'musyrif',
            'started_at' => now(),
            'ended_at' => null,
            'status' => 'active',
            'reason' => null,
        ];
    }
}
