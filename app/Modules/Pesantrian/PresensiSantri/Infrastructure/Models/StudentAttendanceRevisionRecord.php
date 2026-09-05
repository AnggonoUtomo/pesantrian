<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PresensiSantri\Infrastructure\Models;

use App\Modules\Pesantrian\PresensiSantri\Database\Factories\StudentAttendanceRevisionRecordFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $session_id
 * @property string $reason
 * @property string|null $changed_by
 * @property Carbon $changed_at
 * @property array<string, mixed>|null $summary
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read StudentAttendanceSessionRecord $session
 */
final class StudentAttendanceRevisionRecord extends Model
{
    /** @use HasFactory<StudentAttendanceRevisionRecordFactory> */
    use HasFactory;

    use HasUlids;

    protected $table = 'student_attendance_revisions';

    protected $guarded = [];

    /** @return BelongsTo<StudentAttendanceSessionRecord, $this> */
    public function session(): BelongsTo
    {
        return $this->belongsTo(StudentAttendanceSessionRecord::class, 'session_id');
    }

    protected static function newFactory(): StudentAttendanceRevisionRecordFactory
    {
        return StudentAttendanceRevisionRecordFactory::new();
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
