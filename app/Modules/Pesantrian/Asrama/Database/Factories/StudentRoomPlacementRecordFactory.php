<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Database\Factories;

use App\Modules\Pesantrian\Asrama\Infrastructure\Models\StudentRoomPlacementRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentRoomPlacementRecord>
 */
final class StudentRoomPlacementRecordFactory extends Factory
{
    protected $model = StudentRoomPlacementRecord::class;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'student_id' => null,
            'dormitory_room_id' => null,
            'student_no' => 'NIS-'.$this->faker->unique()->numerify('####'),
            'started_at' => now(),
            'ended_at' => null,
            'status' => 'active',
            'reason' => null,
            'active_student_key' => null,
            'created_by' => null,
            'ended_by' => null,
        ];
    }
}
