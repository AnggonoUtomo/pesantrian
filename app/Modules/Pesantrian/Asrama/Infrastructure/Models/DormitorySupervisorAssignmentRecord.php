<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Infrastructure\Models;

use App\Modules\Pesantrian\Asrama\Database\Factories\DormitorySupervisorAssignmentRecordFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $employee_id
 * @property string|null $dormitory_id
 * @property string|null $dormitory_room_id
 * @property string $employee_name
 * @property string $role
 * @property Carbon $started_at
 * @property Carbon|null $ended_at
 * @property string $status
 * @property string|null $reason
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read DormitoryRecord|null $dormitory
 * @property-read DormitoryRoomRecord|null $room
 */
final class DormitorySupervisorAssignmentRecord extends Model
{
    /** @use HasFactory<DormitorySupervisorAssignmentRecordFactory> */
    use HasFactory;

    use HasUlids;

    protected $table = 'dormitory_supervisor_assignments';

    protected $guarded = [];

    /** @return BelongsTo<DormitoryRecord, $this> */
    public function dormitory(): BelongsTo
    {
        return $this->belongsTo(DormitoryRecord::class, 'dormitory_id');
    }

    /** @return BelongsTo<DormitoryRoomRecord, $this> */
    public function room(): BelongsTo
    {
        return $this->belongsTo(DormitoryRoomRecord::class, 'dormitory_room_id');
    }

    protected static function newFactory(): DormitorySupervisorAssignmentRecordFactory
    {
        return DormitorySupervisorAssignmentRecordFactory::new();
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
