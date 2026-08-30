<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\PenerimaanSantri\Infrastructure\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $registration_no
 * @property string|null $registration_period
 * @property string $candidate_name
 * @property string|null $candidate_gender
 * @property string|null $candidate_birth_place
 * @property Carbon|null $candidate_birth_date
 * @property string|null $previous_school
 * @property string|null $target_unit_id
 * @property string $guardian_name
 * @property string|null $guardian_phone
 * @property string|null $guardian_relation
 * @property bool $registration_fee_required
 * @property string|null $registration_fee_amount
 * @property string $registration_fee_status
 * @property array<int, array<string, mixed>>|null $document_checklist
 * @property string $status
 * @property Carbon|null $registered_at
 * @property Carbon|null $decided_at
 * @property string|null $decided_by
 * @property string|null $notes
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
final class StudentAdmissionRecord extends Model
{
    use HasUlids;

    protected $table = 'student_admissions';

    protected $guarded = [];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'candidate_birth_date' => 'immutable_date',
            'registration_fee_required' => 'boolean',
            'registration_fee_amount' => 'decimal:2',
            'document_checklist' => 'array',
            'registered_at' => 'immutable_datetime',
            'decided_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
