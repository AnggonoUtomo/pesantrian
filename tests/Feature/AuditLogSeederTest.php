<?php

declare(strict_types=1);

use App\Modules\System\AccessControl\Database\Seeders\AccessControlSeeder;
use App\Modules\System\AuditLog\Database\Seeders\AuditLogSeeder;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;

it('menjalankan seeder AuditLog secara aman dan idempotent', function (): void {
    $this->seed(AccessControlSeeder::class);
    $this->seed(AuditLogSeeder::class);
    $this->seed(AuditLogSeeder::class);

    expect(AuditRecord::query()->count())->toBe(3)
        ->and(AuditRecord::query()->whereNotNull('actor_id')->count())->toBe(3)
        ->and(AuditRecord::query()->pluck('metadata')->flatten()->all())
        ->not->toContain('password', 'token', 'credential');
});
