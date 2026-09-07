<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Infrastructure\Models;

use App\Modules\Pesantrian\Tahfidz\Database\Factories\TahfidzSubmissionRevisionRecordFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $submission_id
 * @property string $reason
 * @property string|null $changed_by
 * @property Carbon $changed_at
 * @property array<string, mixed>|null $summary
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read TahfidzSubmissionRecord $submission
 */
final class TahfidzSubmissionRevisionRecord extends Model
{
    /** @use HasFactory<TahfidzSubmissionRevisionRecordFactory> */
    use HasFactory;

    use HasUlids;

    protected $table = 'tahfidz_submission_revisions';

    protected $guarded = [];

    /** @return BelongsTo<TahfidzSubmissionRecord, $this> */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(TahfidzSubmissionRecord::class, 'submission_id');
    }

    protected static function newFactory(): TahfidzSubmissionRevisionRecordFactory
    {
        return TahfidzSubmissionRevisionRecordFactory::new();
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
