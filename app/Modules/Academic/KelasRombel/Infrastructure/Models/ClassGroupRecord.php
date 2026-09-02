<?php

declare(strict_types=1);

namespace App\Modules\Academic\KelasRombel\Infrastructure\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $academic_year_id
 * @property string $academic_term_id
 * @property string $unit_id
 * @property string|null $curriculum_id
 * @property string $class_level_id
 * @property string $code
 * @property string $name
 * @property int|null $capacity
 * @property string $status
 * @property Carbon|null $archived_at
 * @property string|null $archived_by
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read AcademicCurriculumRecord|null $curriculum
 * @property-read ClassLevelRecord $classLevel
 * @property-read Collection<int, ClassGroupStudentRecord> $students
 * @property-read Collection<int, ClassGroupHomeroomRecord> $homerooms
 */
final class ClassGroupRecord extends Model
{
    use HasUlids;

    protected $table = 'class_groups';

    protected $guarded = [];

    /** @return BelongsTo<AcademicCurriculumRecord, $this> */
    public function curriculum(): BelongsTo
    {
        return $this->belongsTo(AcademicCurriculumRecord::class, 'curriculum_id');
    }

    /** @return BelongsTo<ClassLevelRecord, $this> */
    public function classLevel(): BelongsTo
    {
        return $this->belongsTo(ClassLevelRecord::class, 'class_level_id');
    }

    /** @return HasMany<ClassGroupStudentRecord, $this> */
    public function students(): HasMany
    {
        return $this->hasMany(ClassGroupStudentRecord::class, 'class_group_id');
    }

    /** @return HasMany<ClassGroupHomeroomRecord, $this> */
    public function homerooms(): HasMany
    {
        return $this->hasMany(ClassGroupHomeroomRecord::class, 'class_group_id');
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
