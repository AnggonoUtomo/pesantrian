<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Infrastructure\Models;

use App\Modules\Pesantrian\Tahfidz\Database\Factories\TahfidzTargetRecordFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $program_id
 * @property string $student_id
 * @property string $student_no
 * @property string $student_name
 * @property string|null $academic_period_id
 * @property string|null $period_label
 * @property int|null $target_juz
 * @property string|null $target_surah
 * @property int|null $target_ayah_from
 * @property int|null $target_ayah_to
 * @property string|null $target_note
 * @property string $status
 * @property string|null $created_by
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read TahfidzProgramRecord $program
 */
final class TahfidzTargetRecord extends Model
{
    /** @use HasFactory<TahfidzTargetRecordFactory> */
    use HasFactory;

    use HasUlids;

    protected $table = 'tahfidz_targets';

    protected $guarded = [];

    /** @return BelongsTo<TahfidzProgramRecord, $this> */
    public function program(): BelongsTo
    {
        return $this->belongsTo(TahfidzProgramRecord::class, 'program_id');
    }

    /** @return HasMany<TahfidzSubmissionRecord, $this> */
    public function submissions(): HasMany
    {
        return $this->hasMany(TahfidzSubmissionRecord::class, 'target_id');
    }

    protected static function newFactory(): TahfidzTargetRecordFactory
    {
        return TahfidzTargetRecordFactory::new();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'target_juz' => 'integer',
            'target_ayah_from' => 'integer',
            'target_ayah_to' => 'integer',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
