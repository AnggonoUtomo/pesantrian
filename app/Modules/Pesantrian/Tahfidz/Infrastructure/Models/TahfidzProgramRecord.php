<?php

declare(strict_types=1);

namespace App\Modules\Pesantrian\Tahfidz\Infrastructure\Models;

use App\Modules\Pesantrian\Tahfidz\Database\Factories\TahfidzProgramRecordFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $code
 * @property string $name
 * @property string|null $description
 * @property string $status
 * @property string|null $created_by
 * @property Carbon|null $archived_at
 * @property string|null $archived_by
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Collection<int, TahfidzTargetRecord> $targets
 * @property-read Collection<int, TahfidzSubmissionRecord> $submissions
 */
final class TahfidzProgramRecord extends Model
{
    /** @use HasFactory<TahfidzProgramRecordFactory> */
    use HasFactory;

    use HasUlids;

    protected $table = 'tahfidz_programs';

    protected $guarded = [];

    /** @return HasMany<TahfidzTargetRecord, $this> */
    public function targets(): HasMany
    {
        return $this->hasMany(TahfidzTargetRecord::class, 'program_id');
    }

    /** @return HasMany<TahfidzSubmissionRecord, $this> */
    public function submissions(): HasMany
    {
        return $this->hasMany(TahfidzSubmissionRecord::class, 'program_id');
    }

    protected static function newFactory(): TahfidzProgramRecordFactory
    {
        return TahfidzProgramRecordFactory::new();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'archived_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
