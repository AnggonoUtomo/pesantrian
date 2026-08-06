<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\System\SystemSetting\Infrastructure\Persistence\Models\IdempotencyKeyRecord;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Str;

it('menghapus hanya reservation idempotency yang expired', function (): void {
    $actor = User::factory()->create();

    foreach ([now()->subMinute(), now()->addHour()] as $expiresAt) {
        IdempotencyKeyRecord::query()->create([
            'actor_id' => $actor->id,
            'key' => (string) Str::ulid(),
            'endpoint' => 'PATCH api/v1/system-settings/test',
            'payload_hash' => hash('sha256', (string) Str::ulid()),
            'response_status' => 200,
            'response_body' => ['success' => true],
            'expires_at' => $expiresAt,
        ]);
    }

    $this->artisan('system-setting:idempotency-prune', ['--json' => true])
        ->assertSuccessful()
        ->expectsOutputToContain('"deleted":1');

    expect(IdempotencyKeyRecord::query()->count())->toBe(1)
        ->and(IdempotencyKeyRecord::query()->firstOrFail()->expires_at->isFuture())->toBeTrue();
});

it('mendaftarkan idempotency prune pada scheduler', function (): void {
    $commands = array_map(
        static fn ($event): string => (string) $event->command,
        app(Schedule::class)->events(),
    );

    expect(array_any(
        $commands,
        static fn (string $command): bool => str_contains($command, 'system-setting:idempotency-prune'),
    ))->toBeTrue();
});
