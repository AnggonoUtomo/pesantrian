<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Infrastructure\Models;

use App\Modules\Pesantrian\PrestasiSantri\Database\Factories\StudentAchievementRecordFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $achievement_no
 * @property string $category_id
 * @property string $category_name
 * @property string $student_id
 * @property string $student_no
 * @property string $student_name
 * @property string|null $academic_period_id
 * @property string|null $academic_period_label
 * @property string|null $mentor_employee_id
 * @property string|null $mentor_name
 * @property string $title
 * @property string $achievement_type
 * @property string $level
 * @property string $result
 * @property string|null $organizer
 * @property string|null $event_name
 * @property string|null $event_location
 * @property Carbon|null $achieved_on
 * @property Carbon|null $period_started_on
 * @property Carbon|null $period_ended_on
 * @property string|null $description
 * @property string|null $notes
 * @property string $status
 * @property Carbon|null $submitted_at
 * @property string|null $submitted_by
 * @property Carbon|null $verified_at
 * @property string|null $verified_by
 * @property string|null $verification_note
 * @property Carbon|null $voided_at
 * @property string|null $voided_by
 * @property string|null $void_reason
 * @property string|null $created_by
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read StudentAchievementCategoryRecord $category
 * @property-read Collection<int, StudentAchievementRevisionRecord> $revisions
 */
final class StudentAchievementRecord extends Model
{
    /** @use HasFactory<StudentAchievementRecordFactory> */
    use HasFactory;

    use HasUlids;

    protected $table = 'student_achievements';

    protected $guarded = [];

    /** @return BelongsTo<StudentAchievementCategoryRecord, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(StudentAchievementCategoryRecord::class, 'category_id');
    }

    /** @return HasMany<StudentAchievementRevisionRecord, $this> */
    public function revisions(): HasMany
    {
        return $this->hasMany(StudentAchievementRevisionRecord::class, 'achievement_id');
    }

    protected static function newFactory(): StudentAchievementRecordFactory
    {
        return StudentAchievementRecordFactory::new();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'achieved_on' => 'immutable_date',
            'period_started_on' => 'immutable_date',
            'period_ended_on' => 'immutable_date',
            'submitted_at' => 'immutable_datetime',
            'verified_at' => 'immutable_datetime',
            'voided_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
