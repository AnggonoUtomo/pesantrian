<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PresensiSantri\Infrastructure\Models;

use App\Modules\Pesantrian\PresensiSantri\Database\Factories\StudentAttendanceEntryRecordFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $session_id
 * @property string $student_id
 * @property string $student_no
 * @property string $student_name
 * @property string $status
 * @property int|null $minutes_late
 * @property string|null $note
 * @property string|null $source_reference_type
 * @property string|null $source_reference_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read StudentAttendanceSessionRecord $session
 */
final class StudentAttendanceEntryRecord extends Model
{
    /** @use HasFactory<StudentAttendanceEntryRecordFactory> */
    use HasFactory;

    use HasUlids;

    protected $table = 'student_attendance_entries';

    protected $guarded = [];

    /** @return BelongsTo<StudentAttendanceSessionRecord, $this> */
    public function session(): BelongsTo
    {
        return $this->belongsTo(StudentAttendanceSessionRecord::class, 'session_id');
    }

    protected static function newFactory(): StudentAttendanceEntryRecordFactory
    {
        return StudentAttendanceEntryRecordFactory::new();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'minutes_late' => 'integer',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
