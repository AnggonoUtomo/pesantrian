<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\KedisiplinanSantri\Infrastructure\Models;

use App\Modules\Pesantrian\KedisiplinanSantri\Database\Factories\StudentDisciplineRevisionRecordFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $case_id
 * @property string $reason
 * @property string|null $changed_by
 * @property Carbon $changed_at
 * @property array<string, mixed>|null $summary
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read StudentDisciplineCaseRecord $case
 */
final class StudentDisciplineRevisionRecord extends Model
{
    /** @use HasFactory<StudentDisciplineRevisionRecordFactory> */
    use HasFactory;

    use HasUlids;

    protected $table = 'student_discipline_revisions';

    protected $guarded = [];

    /** @return BelongsTo<StudentDisciplineCaseRecord, $this> */
    public function case(): BelongsTo
    {
        return $this->belongsTo(StudentDisciplineCaseRecord::class, 'case_id');
    }

    protected static function newFactory(): StudentDisciplineRevisionRecordFactory
    {
        return StudentDisciplineRevisionRecordFactory::new();
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
