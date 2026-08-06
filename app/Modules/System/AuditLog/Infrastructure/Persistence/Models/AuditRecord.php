<?php

declare(strict_types=1);

namespace App\Modules\System\AuditLog\Infrastructure\Persistence\Models;

use App\Models\User;
use App\Modules\System\AuditLog\Domain\Exceptions\ImmutableAuditRecord;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $event_id
 * @property string|null $actor_id
 * @property string $action
 * @property string $subject_type
 * @property string|null $subject_id
 * @property string $module
 * @property string|null $project_id
 * @property string|null $tenant_id
 * @property string $correlation_id
 * @property string|null $reason
 * @property array<string, mixed> $metadata
 * @property Carbon $created_at
 * @property-read User|null $actor
 */
final class AuditRecord extends Model
{
    use HasUlids;

    public const UPDATED_AT = null;

    protected $table = 'audit_logs';

    protected $guarded = [];

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        self::updating(static function (): never {
            throw new ImmutableAuditRecord;
        });

        self::deleting(static function (): never {
            throw new ImmutableAuditRecord;
        });
    }
}
