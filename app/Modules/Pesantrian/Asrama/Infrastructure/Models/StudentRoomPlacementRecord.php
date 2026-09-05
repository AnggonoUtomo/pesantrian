<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Infrastructure\Models;

use App\Modules\Pesantrian\Asrama\Database\Factories\StudentRoomPlacementRecordFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $student_id
 * @property string $dormitory_room_id
 * @property string $student_no
 * @property Carbon $started_at
 * @property Carbon|null $ended_at
 * @property string $status
 * @property string|null $reason
 * @property string|null $active_student_key
 * @property string|null $created_by
 * @property string|null $ended_by
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read DormitoryRoomRecord $room
 */
final class StudentRoomPlacementRecord extends Model
{
    /** @use HasFactory<StudentRoomPlacementRecordFactory> */
    use HasFactory;

    use HasUlids;

    protected $table = 'student_room_placements';

    protected $guarded = [];

    /** @return BelongsTo<DormitoryRoomRecord, $this> */
    public function room(): BelongsTo
    {
        return $this->belongsTo(DormitoryRoomRecord::class, 'dormitory_room_id');
    }

    protected static function newFactory(): StudentRoomPlacementRecordFactory
    {
        return StudentRoomPlacementRecordFactory::new();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'started_at' => 'immutable_datetime',
            'ended_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
