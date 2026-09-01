<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Santri\Infrastructure\Models;

use App\Modules\Pesantrian\Santri\Database\Factories\StudentRecordFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $student_no
 * @property string|null $admission_id
 * @property string|null $registration_no
 * @property string $full_name
 * @property string|null $preferred_name
 * @property string|null $gender
 * @property string|null $birth_place
 * @property Carbon|null $birth_date
 * @property string|null $previous_school
 * @property string|null $primary_unit_id
 * @property Carbon|null $entry_date
 * @property string $status
 * @property string|null $status_reason
 * @property Carbon|null $status_changed_at
 * @property string|null $status_changed_by
 * @property Carbon|null $archived_at
 * @property string|null $archived_by
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, StudentGuardianRecord> $guardians
 */
final class StudentRecord extends Model
{
    /** @use HasFactory<StudentRecordFactory> */
    use HasFactory;

    use HasUlids;

    protected $table = 'students';

    protected $guarded = [];

    /** @return HasMany<StudentGuardianRecord, $this> */
    public function guardians(): HasMany
    {
        return $this->hasMany(StudentGuardianRecord::class, 'student_id');
    }

    protected static function newFactory(): StudentRecordFactory
    {
        return StudentRecordFactory::new();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'birth_date' => 'immutable_date',
            'entry_date' => 'immutable_date',
            'status_changed_at' => 'immutable_datetime',
            'archived_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
