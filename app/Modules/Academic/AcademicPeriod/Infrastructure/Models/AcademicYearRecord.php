<?php

declare(strict_types=1);

namespace App\Modules\Academic\AcademicPeriod\Infrastructure\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $code
 * @property string $name
 * @property Carbon $starts_on
 * @property Carbon $ends_on
 * @property string $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, AcademicTermRecord> $terms
 */
final class AcademicYearRecord extends Model
{
    use HasUlids;

    protected $table = 'academic_years';

    protected $guarded = [];

    /** @return HasMany<AcademicTermRecord, $this> */
    public function terms(): HasMany
    {
        return $this->hasMany(AcademicTermRecord::class, 'academic_year_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'starts_on' => 'immutable_date',
            'ends_on' => 'immutable_date',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
