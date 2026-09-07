<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Infrastructure\Models;

use App\Modules\Pesantrian\Tahfidz\Database\Factories\TahfidzSubmissionRecordFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $program_id
 * @property string|null $target_id
 * @property string $student_id
 * @property string $student_no
 * @property string $student_name
 * @property string|null $supervisor_id
 * @property string|null $supervisor_name
 * @property Carbon $submission_date
 * @property string $type
 * @property int|null $juz
 * @property string|null $surah
 * @property int|null $ayah_from
 * @property int|null $ayah_to
 * @property string $status
 * @property string|null $quality_note
 * @property string|null $created_by
 * @property Carbon|null $reviewed_at
 * @property string|null $reviewed_by
 * @property Carbon|null $voided_at
 * @property string|null $voided_by
 * @property string|null $void_reason
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read TahfidzProgramRecord $program
 * @property-read TahfidzTargetRecord|null $target
 * @property-read Collection<int, TahfidzSubmissionRevisionRecord> $revisions
 */
final class TahfidzSubmissionRecord extends Model
{
    /** @use HasFactory<TahfidzSubmissionRecordFactory> */
    use HasFactory;

    use HasUlids;

    protected $table = 'tahfidz_submissions';

    protected $guarded = [];

    /** @return BelongsTo<TahfidzProgramRecord, $this> */
    public function program(): BelongsTo
    {
        return $this->belongsTo(TahfidzProgramRecord::class, 'program_id');
    }

    /** @return BelongsTo<TahfidzTargetRecord, $this> */
    public function target(): BelongsTo
    {
        return $this->belongsTo(TahfidzTargetRecord::class, 'target_id');
    }

    /** @return HasMany<TahfidzSubmissionRevisionRecord, $this> */
    public function revisions(): HasMany
    {
        return $this->hasMany(TahfidzSubmissionRevisionRecord::class, 'submission_id');
    }

    protected static function newFactory(): TahfidzSubmissionRecordFactory
    {
        return TahfidzSubmissionRecordFactory::new();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'submission_date' => 'immutable_date',
            'juz' => 'integer',
            'ayah_from' => 'integer',
            'ayah_to' => 'integer',
            'reviewed_at' => 'immutable_datetime',
            'voided_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
