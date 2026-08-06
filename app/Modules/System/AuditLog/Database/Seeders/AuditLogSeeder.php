<?php

declare(strict_types=1);

namespace App\Modules\System\AuditLog\Database\Seeders;

use App\Models\User;
use App\Modules\System\AuditLog\Application\Contracts\AuditRecorder;
use App\Modules\System\AuditLog\Application\DTO\AuditEntryData;
use DateTimeImmutable;
use Illuminate\Database\Seeder;

final class AuditLogSeeder extends Seeder
{
    public function run(): void
    {
        if (config('app.env') === 'production') {
            return;
        }

        $actor = User::query()->where('email', 'super-system@example.test')->first();

        if (! $actor instanceof User) {
            return;
        }

        $recorder = app(AuditRecorder::class);
        $entries = [
            [
                'eventId' => '01KZBBGP5R7MT3JN65F7KP4CHS',
                'correlationId' => '01KZBBGP7MY7G4KM1QT9M0JN23',
                'action' => 'system.audit_baseline_created',
                'subjectType' => 'system',
                'subjectId' => null,
                'module' => 'AuditLog',
                'metadata' => ['result' => 'created'],
            ],
            [
                'eventId' => '01KZBBGP7KF18PBPMME14C0FVE',
                'correlationId' => '01KZBBGP7MY7G4KM1QT9M0JN24',
                'action' => 'access_control.permissions_synced',
                'subjectType' => 'role',
                'subjectId' => null,
                'module' => 'AccessControl',
                'metadata' => ['result' => 'verified'],
            ],
            [
                'eventId' => '01KZBBGP7MY7G4KM1QT9M0JN25',
                'correlationId' => '01KZBBGP7MY7G4KM1QT9M0JN26',
                'action' => 'user.lifecycle_reviewed',
                'subjectType' => 'user',
                'subjectId' => $actor->id,
                'module' => 'UserManagement',
                'metadata' => ['result' => 'reviewed'],
            ],
        ];

        foreach ($entries as $entry) {
            $recorder->record(new AuditEntryData(
                eventId: $entry['eventId'],
                actorId: $actor->id,
                action: $entry['action'],
                subjectType: $entry['subjectType'],
                subjectId: $entry['subjectId'],
                module: $entry['module'],
                correlationId: $entry['correlationId'],
                reason: 'Data contoh development tanpa payload sensitif.',
                metadata: $entry['metadata'],
                occurredAt: new DateTimeImmutable,
            ));
        }
    }
}
