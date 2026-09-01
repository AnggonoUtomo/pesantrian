<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Santri\Infrastructure\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $student_id
 * @property string $guardian_name
 * @property string|null $guardian_phone
 * @property string|null $guardian_relation
 * @property bool $is_primary
 * @property bool $is_emergency_contact
 * @property string|null $notes
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read StudentRecord $student
 */
final class StudentGuardianRecord extends Model
{
    use HasUlids;

    protected $table = 'student_guardians';

    protected $guarded = [];

    /** @return BelongsTo<StudentRecord, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentRecord::class, 'student_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'is_emergency_contact' => 'boolean',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
