<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PresensiSantri\Infrastructure\Models;

use App\Modules\Pesantrian\PresensiSantri\Database\Factories\StudentAttendanceSessionRecordFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property Carbon $attendance_date
 * @property string $context_type
 * @property string|null $context_id
 * @property string $context_name
 * @property string $session_code
 * @property string $session_name
 * @property string $status
 * @property Carbon|null $submitted_at
 * @property string|null $submitted_by
 * @property Carbon|null $voided_at
 * @property string|null $voided_by
 * @property string|null $void_reason
 * @property string|null $created_by
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, StudentAttendanceEntryRecord> $entries
 * @property-read Collection<int, StudentAttendanceRevisionRecord> $revisions
 */
final class StudentAttendanceSessionRecord extends Model
{
    /** @use HasFactory<StudentAttendanceSessionRecordFactory> */
    use HasFactory;

    use HasUlids;

    protected $table = 'student_attendance_sessions';

    protected $guarded = [];

    /** @return HasMany<StudentAttendanceEntryRecord, $this> */
    public function entries(): HasMany
    {
        return $this->hasMany(StudentAttendanceEntryRecord::class, 'session_id');
    }

    /** @return HasMany<StudentAttendanceRevisionRecord, $this> */
    public function revisions(): HasMany
    {
        return $this->hasMany(StudentAttendanceRevisionRecord::class, 'session_id');
    }

    protected static function newFactory(): StudentAttendanceSessionRecordFactory
    {
        return StudentAttendanceSessionRecordFactory::new();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'attendance_date' => 'immutable_date',
            'submitted_at' => 'immutable_datetime',
            'voided_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
