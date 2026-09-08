<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Infrastructure\Models;

use App\Modules\Pesantrian\KedisiplinanSantri\Database\Factories\StudentDisciplineCategoryRecordFactory;
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
 * @property string $default_severity
 * @property int|null $default_points
 * @property string $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, StudentDisciplineCaseRecord> $cases
 */
final class StudentDisciplineCategoryRecord extends Model
{
    /** @use HasFactory<StudentDisciplineCategoryRecordFactory> */
    use HasFactory;

    use HasUlids;

    protected $table = 'student_discipline_categories';

    protected $guarded = [];

    /** @return HasMany<StudentDisciplineCaseRecord, $this> */
    public function cases(): HasMany
    {
        return $this->hasMany(StudentDisciplineCaseRecord::class, 'category_id');
    }

    protected static function newFactory(): StudentDisciplineCategoryRecordFactory
    {
        return StudentDisciplineCategoryRecordFactory::new();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'default_points' => 'integer',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
