<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Infrastructure\Models;

use App\Modules\Pesantrian\Asrama\Database\Factories\DormitoryRoomRecordFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $dormitory_id
 * @property string $code
 * @property string $name
 * @property int $capacity
 * @property string $status
 * @property Carbon|null $archived_at
 * @property string|null $archived_by
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read DormitoryRecord $dormitory
 * @property-read Collection<int, StudentRoomPlacementRecord> $placements
 * @property-read Collection<int, DormitorySupervisorAssignmentRecord> $supervisorAssignments
 */
final class DormitoryRoomRecord extends Model
{
    /** @use HasFactory<DormitoryRoomRecordFactory> */
    use HasFactory;

    use HasUlids;

    protected $table = 'dormitory_rooms';

    protected $guarded = [];

    /** @return BelongsTo<DormitoryRecord, $this> */
    public function dormitory(): BelongsTo
    {
        return $this->belongsTo(DormitoryRecord::class, 'dormitory_id');
    }

    /** @return HasMany<StudentRoomPlacementRecord, $this> */
    public function placements(): HasMany
    {
        return $this->hasMany(StudentRoomPlacementRecord::class, 'dormitory_room_id');
    }

    /** @return HasMany<DormitorySupervisorAssignmentRecord, $this> */
    public function supervisorAssignments(): HasMany
    {
        return $this->hasMany(DormitorySupervisorAssignmentRecord::class, 'dormitory_room_id');
    }

    protected static function newFactory(): DormitoryRoomRecordFactory
    {
        return DormitoryRoomRecordFactory::new();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'archived_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
