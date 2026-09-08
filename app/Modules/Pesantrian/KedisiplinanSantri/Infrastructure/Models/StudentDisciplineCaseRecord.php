<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Infrastructure\Models;

use App\Modules\Pesantrian\KedisiplinanSantri\Database\Factories\StudentDisciplineCaseRecordFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $case_no
 * @property string $student_id
 * @property string $student_no
 * @property string $student_name
 * @property string|null $unit_id
 * @property string|null $unit_name
 * @property string $category_id
 * @property string $category_name
 * @property string $severity
 * @property int|null $points
 * @property Carbon $occurred_at
 * @property string|null $location
 * @property string $description
 * @property string|null $reported_by
 * @property string|null $assigned_employee_id
 * @property string|null $assigned_employee_name
 * @property string $status
 * @property Carbon|null $submitted_at
 * @property Carbon|null $reviewed_at
 * @property string|null $reviewed_by
 * @property string|null $review_note
 * @property string|null $action_plan
 * @property Carbon|null $action_assigned_at
 * @property Carbon|null $resolved_at
 * @property string|null $resolved_by
 * @property string|null $resolution_note
 * @property Carbon|null $voided_at
 * @property string|null $voided_by
 * @property string|null $void_reason
 * @property string|null $created_by
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read StudentDisciplineCategoryRecord $category
 * @property-read Collection<int, StudentDisciplineRevisionRecord> $revisions
 */
final class StudentDisciplineCaseRecord extends Model
{
    /** @use HasFactory<StudentDisciplineCaseRecordFactory> */
    use HasFactory;

    use HasUlids;

    protected $table = 'student_discipline_cases';

    protected $guarded = [];

    /** @return BelongsTo<StudentDisciplineCategoryRecord, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(StudentDisciplineCategoryRecord::class, 'category_id');
    }

    /** @return HasMany<StudentDisciplineRevisionRecord, $this> */
    public function revisions(): HasMany
    {
        return $this->hasMany(StudentDisciplineRevisionRecord::class, 'case_id');
    }

    protected static function newFactory(): StudentDisciplineCaseRecordFactory
    {
        return StudentDisciplineCaseRecordFactory::new();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'points' => 'integer',
            'occurred_at' => 'immutable_datetime',
            'submitted_at' => 'immutable_datetime',
            'reviewed_at' => 'immutable_datetime',
            'action_assigned_at' => 'immutable_datetime',
            'resolved_at' => 'immutable_datetime',
            'voided_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
