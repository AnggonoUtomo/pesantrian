<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Infrastructure\Models;

use App\Modules\Pesantrian\PrestasiSantri\Database\Factories\StudentAchievementRevisionRecordFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $achievement_id
 * @property string|null $from_status
 * @property string $to_status
 * @property string $reason
 * @property string|null $changed_by
 * @property Carbon $changed_at
 * @property array<string, mixed>|null $summary
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read StudentAchievementRecord $achievement
 */
final class StudentAchievementRevisionRecord extends Model
{
    /** @use HasFactory<StudentAchievementRevisionRecordFactory> */
    use HasFactory;

    use HasUlids;

    protected $table = 'student_achievement_revisions';

    protected $guarded = [];

    /** @return BelongsTo<StudentAchievementRecord, $this> */
    public function achievement(): BelongsTo
    {
        return $this->belongsTo(StudentAchievementRecord::class, 'achievement_id');
    }

    protected static function newFactory(): StudentAchievementRevisionRecordFactory
    {
        return StudentAchievementRevisionRecordFactory::new();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'changed_at' => 'immutable_datetime',
            'summary' => 'array',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
