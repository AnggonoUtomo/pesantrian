<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod\Infrastructure\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $academic_year_id
 * @property string $code
 * @property string $name
 * @property int $sequence
 * @property Carbon $starts_on
 * @property Carbon $ends_on
 * @property string $status
 * @property bool $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read AcademicYearRecord $academicYear
 */
final class AcademicTermRecord extends Model
{
    use HasUlids;

    protected $table = 'academic_terms';

    protected $guarded = [];

    /** @return BelongsTo<AcademicYearRecord, $this> */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYearRecord::class, 'academic_year_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'sequence' => 'integer',
            'starts_on' => 'immutable_date',
            'ends_on' => 'immutable_date',
            'is_active' => 'boolean',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
