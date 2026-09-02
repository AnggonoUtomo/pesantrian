<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Infrastructure\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $unit_id
 * @property string $code
 * @property string $name
 * @property int $sequence
 * @property string $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
final class ClassLevelRecord extends Model
{
    use HasUlids;

    protected $table = 'class_levels';

    protected $guarded = [];

    /** @return HasMany<ClassGroupRecord, $this> */
    public function classGroups(): HasMany
    {
        return $this->hasMany(ClassGroupRecord::class, 'class_level_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'sequence' => 'integer',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
