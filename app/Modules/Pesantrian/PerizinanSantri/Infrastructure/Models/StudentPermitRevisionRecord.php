<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PerizinanSantri\Infrastructure\Models;

use App\Modules\Pesantrian\PerizinanSantri\Database\Factories\StudentPermitRevisionRecordFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $permit_id
 * @property string $reason
 * @property string|null $changed_by
 * @property Carbon $changed_at
 * @property array<string, mixed>|null $summary
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read StudentPermitRecord $permit
 */
final class StudentPermitRevisionRecord extends Model
{
    /** @use HasFactory<StudentPermitRevisionRecordFactory> */
    use HasFactory;

    use HasUlids;

    protected $table = 'student_permit_revisions';

    protected $guarded = [];

    /** @return BelongsTo<StudentPermitRecord, $this> */
    public function permit(): BelongsTo
    {
        return $this->belongsTo(StudentPermitRecord::class, 'permit_id');
    }

    protected static function newFactory(): StudentPermitRevisionRecordFactory
    {
        return StudentPermitRevisionRecordFactory::new();
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
