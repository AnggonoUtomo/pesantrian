<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Asrama\Infrastructure\Models;

use App\Modules\Pesantrian\Asrama\Database\Factories\DormitoryRecordFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $unit_id
 * @property string $code
 * @property string $name
 * @property string $gender_policy
 * @property string|null $description
 * @property string $status
 * @property Carbon|null $archived_at
 * @property string|null $archived_by
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, DormitoryRoomRecord> $rooms
 * @property-read Collection<int, DormitorySupervisorAssignmentRecord> $supervisorAssignments
 */
final class DormitoryRecord extends Model
{
    /** @use HasFactory<DormitoryRecordFactory> */
    use HasFactory;

    use HasUlids;

    protected $table = 'dormitories';

    protected $guarded = [];

    /** @return HasMany<DormitoryRoomRecord, $this> */
    public function rooms(): HasMany
    {
        return $this->hasMany(DormitoryRoomRecord::class, 'dormitory_id');
    }

    /** @return HasMany<DormitorySupervisorAssignmentRecord, $this> */
    public function supervisorAssignments(): HasMany
    {
        return $this->hasMany(DormitorySupervisorAssignmentRecord::class, 'dormitory_id');
    }

    protected static function newFactory(): DormitoryRecordFactory
    {
        return DormitoryRecordFactory::new();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'archived_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
