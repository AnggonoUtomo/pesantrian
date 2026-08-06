<?php

declare(strict_types=1);

namespace App\Modules\System\SystemSetting\Infrastructure\Persistence\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $actor_id
 * @property string $key
 * @property string $endpoint
 * @property string $payload_hash
 * @property int $response_status
 * @property array<string, mixed> $response_body
 * @property Carbon $expires_at
 * @property Carbon $created_at
 * @property-read User $actor
 */
final class IdempotencyKeyRecord extends Model
{
    use HasUlids;

    public const UPDATED_AT = null;

    protected $table = 'idempotency_keys';

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
            'response_status' => 'integer',
            'response_body' => 'array',
            'expires_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
        ];
    }
}
