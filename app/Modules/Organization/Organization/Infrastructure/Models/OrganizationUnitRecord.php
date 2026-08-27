<?php

declare(strict_types=1);

namespace App\Modules\Organization\Organization\Infrastructure\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string|null $parent_id
 * @property string $code
 * @property string $name
 * @property string $type
 * @property string $status
 * @property string|null $location_name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read OrganizationUnitRecord|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, OrganizationUnitRecord> $children
 */
final class OrganizationUnitRecord extends Model
{
    use HasUlids;

    protected $table = 'organization_units';

    protected $guarded = [];

    /** @return BelongsTo<OrganizationUnitRecord, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /** @return HasMany<OrganizationUnitRecord, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'created_at' => 'immutable_datetime',
            'updated_at' => 'immutable_datetime',
        ];
    }
}
