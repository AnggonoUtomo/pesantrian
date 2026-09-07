<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PerizinanSantri\Infrastructure\Models;

use App\Modules\Pesantrian\PerizinanSantri\Database\Factories\StudentPermitRecordFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $permit_no
 * @property string $student_id
 * @property string $student_no
 * @property string $student_name
 * @property string $permit_type
 * @property Carbon $starts_at
 * @property Carbon $ends_at
 * @property string|null $destination
 * @property string $reason
 * @property string|null $guardian_name
 * @property string|null $guardian_phone
 * @property string|null $guardian_relation
 * @property string $status
 * @property Carbon|null $submitted_at
 * @property string|null $submitted_by
 * @property Carbon|null $reviewed_at
 * @property string|null $reviewed_by
 * @property string|null $review_note
 * @property Carbon|null $checked_out_at
 * @property string|null $checked_out_by
 * @property Carbon|null $returned_at
 * @property string|null $returned_by
 * @property string|null $return_note
 * @property Carbon|null $voided_at
 * @property string|null $voided_by
 * @property string|null $void_reason
 * @property string|null $created_by
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, StudentPermitRevisionRecord> $revisions
 */
final class StudentPermitRecord extends Model
{
    /** @use HasFactory<StudentPermitRecordFactory> */
    use HasFactory;

    use HasUlids;

    protected $table = 'student_permits';

    protected $guarded = [];

    /** @return HasMany<StudentPermitRevisionRecord, $this> */
    public function revisions(): HasMany
    {
        return $this->hasMany(StudentPermitRevisionRecord::class, 'permit_id');
    }

    protected static function newFactory(): StudentPermitRecordFactory
    {
        return StudentPermitRecordFactory::new();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'starts_at' => 'immutable_datetime',
            'ends_at' => 'immutable_datetime',
            'submitted_at' => 'immutable_datetime',
            'reviewed_at' => 'immutable_datetime',
            'checked_out_at' => 'immutable_datetime',
            'returned_at' => 'immutable_datetime',
            'voided_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
