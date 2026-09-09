<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PrestasiSantri\Infrastructure\Models;

use App\Modules\Pesantrian\PrestasiSantri\Database\Factories\StudentAchievementCategoryRecordFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property string $status
 * @property Carbon|null $archived_at
 * @property string|null $archived_by
 * @property string|null $archive_reason
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, StudentAchievementRecord> $achievements
 */
final class StudentAchievementCategoryRecord extends Model
{
    /** @use HasFactory<StudentAchievementCategoryRecordFactory> */
    use HasFactory;

    use HasUlids;

    protected $table = 'student_achievement_categories';

    protected $guarded = [];

    /** @return HasMany<StudentAchievementRecord, $this> */
    public function achievements(): HasMany
    {
        return $this->hasMany(StudentAchievementRecord::class, 'category_id');
    }

    protected static function newFactory(): StudentAchievementCategoryRecordFactory
    {
        return StudentAchievementCategoryRecordFactory::new();
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
