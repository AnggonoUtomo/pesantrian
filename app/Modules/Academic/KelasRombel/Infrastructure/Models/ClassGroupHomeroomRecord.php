<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Infrastructure\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $class_group_id
 * @property string $employee_id
 * @property string $employee_name
 * @property Carbon $assigned_on
 * @property Carbon|null $ended_on
 * @property string $status
 * @property string|null $reason
 * @property string|null $active_class_group_key
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read ClassGroupRecord $classGroup
 */
final class ClassGroupHomeroomRecord extends Model
{
    use HasUlids;

    protected $table = 'class_group_homerooms';

    protected $guarded = [];

    /** @return BelongsTo<ClassGroupRecord, $this> */
    public function classGroup(): BelongsTo
    {
        return $this->belongsTo(ClassGroupRecord::class, 'class_group_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'assigned_on' => 'immutable_date',
            'ended_on' => 'immutable_date',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
