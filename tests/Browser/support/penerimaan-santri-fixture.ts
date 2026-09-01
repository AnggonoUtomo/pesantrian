import { execFileSync } from 'node:child_process';
import { randomUUID } from 'node:crypto';
import { mkdirSync, rmSync, writeFileSync } from 'node:fs';
import path from 'node:path';
import type { TestInfo } from '@playwright/test';

export type PenerimaanSantriFixture = {
    candidateName: string;
    email: string;
    guardianName: string;
    password: string;
    period: string;
    previousSchoolAfterEdit: string;
    unitCode: string;
    unitName: string;
};

export function createPenerimaanSantriFixture(
    testInfo: TestInfo,
): PenerimaanSantriFixture {
    const uniqueId = stableUniqueId(testInfo);
    const titleSlug = slugify(testInfo.title).slice(0, 24);
    const suffix = `${titleSlug}-${uniqueId}`;

    const fixture = {
        candidateName: `E2E PPDB Santri ${suffix}`,
        email: `e2e-ppdb-${suffix}@example.test`,
        guardianName: `Wali E2E ${suffix}`,
        password: `E2E-${randomUUID()}`,
        period: `E2E-PPDB-${suffix}`,
        previousSchoolAfterEdit: `SD E2E Update ${suffix}`,
        unitCode: `E2EPPDB${uniqueId.replaceAll('-', '').slice(0, 12).toUpperCase()}`,
        unitName: `Unit E2E PPDB ${suffix}`,
    } satisfies PenerimaanSantriFixture;

    cleanupPenerimaanSantriFixture(fixture);
    runLaravelFixture('setup', fixture, setupScript(fixture));
    assertFixtureReady(fixture);

    return fixture;
}

export function cleanupPenerimaanSantriFixture(
    fixture: PenerimaanSantriFixture,
): void {
    runLaravelFixture('cleanup', fixture, cleanupScript(fixture));
}

function runLaravelFixture(
    action: 'setup' | 'cleanup',
    fixture: PenerimaanSantriFixture,
    phpCode: string,
): string {
    const fixtureDir = path.join(process.cwd(), 'build', 'playwright', 'fixtures');
    mkdirSync(fixtureDir, { recursive: true });

    const fileName = `${action}-${slugify(fixture.period)}.php`;
    const fixturePath = path.join(fixtureDir, fileName);
    const relativePath = path
        .relative(process.cwd(), fixturePath)
        .replaceAll(path.sep, '/');

    writeFileSync(fixturePath, phpCode, { encoding: 'utf8' });

    try {
        return execFileSync('php', [relativePath], {
            cwd: process.cwd(),
            env: { ...process.env, APP_ENV: process.env.APP_ENV ?? 'local' },
            encoding: 'utf8',
        }).trim();
    } finally {
        rmSync(fixturePath, { force: true });
    }
}

function assertFixtureReady(fixture: PenerimaanSantriFixture): void {
    const output = runLaravelFixture('setup', fixture, verifyScript(fixture));
    const result = JSON.parse(output) as {
        auth: boolean;
        exists: boolean;
        hash: boolean;
        permissions: string[];
        status: string | null;
        unit: boolean;
    };
    const expectedPermissions = [
        'system.dashboard.view',
        'penerimaan_santri.view',
        'penerimaan_santri.manage',
        'penerimaan_santri.decide',
    ];
    const missingPermissions = expectedPermissions.filter(
        (permission) => !result.permissions.includes(permission),
    );

    if (
        !result.exists ||
        result.status !== 'active' ||
        !result.auth ||
        !result.hash ||
        !result.unit ||
        missingPermissions.length > 0
    ) {
        throw new Error(
            `Fixture PPDB tidak siap: ${JSON.stringify({
                ...result,
                missingPermissions,
            })}`,
        );
    }
}

function setupScript(fixture: PenerimaanSantriFixture): string {
    return `<?php

declare(strict_types=1);

use App\\Models\\User;
use App\\Modules\\Pesantrian\\PenerimaanSantri\\Infrastructure\\Models\\StudentAdmissionRecord;
use App\\Modules\\System\\AccessControl\\Infrastructure\\Persistence\\Models\\Permission;
use App\\Modules\\System\\UserManagement\\Domain\\ValueObjects\\UserStatus;
use Illuminate\\Contracts\\Console\\Kernel;
use Illuminate\\Support\\Facades\\DB;
use Illuminate\\Support\\Facades\\Hash;
use Illuminate\\Support\\Str;
use Spatie\\Permission\\PermissionRegistrar;

$basePath = dirname(__DIR__, 3);
require $basePath.'/vendor/autoload.php';
$app = require $basePath.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

app(PermissionRegistrar::class)->forgetCachedPermissions();

$email = ${phpString(fixture.email)};
$password = ${phpString(fixture.password)};
$period = ${phpString(fixture.period)};
$candidateName = ${phpString(fixture.candidateName)};
$unitCode = ${phpString(fixture.unitCode)};
$unitName = ${phpString(fixture.unitName)};

StudentAdmissionRecord::query()
    ->where(static function ($query) use ($period, $candidateName): void {
        $query
            ->where('registration_period', $period)
            ->orWhere('candidate_name', 'like', $candidateName.'%');
    })
    ->delete();

DB::table('organization_units')->where('code', $unitCode)->delete();

$unitId = (string) Str::ulid();
DB::table('organization_units')->insert([
    'id' => $unitId,
    'code' => $unitCode,
    'name' => $unitName,
    'type' => 'education_unit',
    'status' => 'active',
    'location_name' => 'E2E',
    'created_at' => now(),
    'updated_at' => now(),
]);

foreach (['system.dashboard.view', 'penerimaan_santri.view', 'penerimaan_santri.manage', 'penerimaan_santri.decide'] as $permissionName) {
    Permission::query()->firstOrCreate([
        'name' => $permissionName,
        'guard_name' => 'web',
    ]);
}

$user = User::withTrashed()->where('email', $email)->first();

if (! $user instanceof User) {
    $user = new User();
    $user->email = $email;
}

if (method_exists($user, 'trashed') && $user->trashed()) {
    $user->restore();
}

$user->forceFill([
    'name' => 'E2E PPDB Operator',
    'email' => $email,
    'password' => Hash::make($password),
    'status' => UserStatus::ACTIVE,
    'email_verified_at' => now(),
])->save();

$user->givePermissionTo([
    'system.dashboard.view',
    'penerimaan_santri.view',
    'penerimaan_santri.manage',
    'penerimaan_santri.decide',
]);

app(PermissionRegistrar::class)->forgetCachedPermissions();
`;
}

function cleanupScript(fixture: PenerimaanSantriFixture): string {
    return `<?php

declare(strict_types=1);

use App\\Models\\User;
use App\\Modules\\Pesantrian\\PenerimaanSantri\\Infrastructure\\Models\\StudentAdmissionRecord;
use Illuminate\\Contracts\\Console\\Kernel;
use Illuminate\\Support\\Facades\\DB;
use Spatie\\Permission\\PermissionRegistrar;

$basePath = dirname(__DIR__, 3);
require $basePath.'/vendor/autoload.php';
$app = require $basePath.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$email = ${phpString(fixture.email)};
$period = ${phpString(fixture.period)};
$candidateName = ${phpString(fixture.candidateName)};
$unitCode = ${phpString(fixture.unitCode)};

StudentAdmissionRecord::query()
    ->where(static function ($query) use ($period, $candidateName): void {
        $query
            ->where('registration_period', $period)
            ->orWhere('candidate_name', 'like', $candidateName.'%');
    })
    ->delete();

DB::table('organization_units')->where('code', $unitCode)->delete();

$user = User::withTrashed()->where('email', $email)->first();

if ($user instanceof User) {
    $user->syncPermissions([]);
    $user->forceDelete();
}

app(PermissionRegistrar::class)->forgetCachedPermissions();
`;
}

function verifyScript(fixture: PenerimaanSantriFixture): string {
    return `<?php

declare(strict_types=1);

use App\\Models\\User;
use Illuminate\\Contracts\\Console\\Kernel;
use Illuminate\\Support\\Facades\\DB;
use Illuminate\\Support\\Facades\\Hash;

$basePath = dirname(__DIR__, 3);
require $basePath.'/vendor/autoload.php';
$app = require $basePath.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$email = ${phpString(fixture.email)};
$password = ${phpString(fixture.password)};
$unitCode = ${phpString(fixture.unitCode)};
$user = User::where('email', $email)->first();

echo json_encode([
    'auth' => $user?->canAuthenticate() ?? false,
    'exists' => $user instanceof User,
    'hash' => $user instanceof User ? Hash::check($password, $user->password) : false,
    'permissions' => $user instanceof User ? $user->getAllPermissions()->pluck('name')->values()->all() : [],
    'status' => $user?->status?->value,
    'unit' => DB::table('organization_units')->where('code', $unitCode)->exists(),
]);
`;
}

function stableUniqueId(testInfo: TestInfo): string {
    const project = slugify(testInfo.project.name || 'default');
    const retry = testInfo.retry === 0 ? 'r0' : `r${testInfo.retry}`;
    const worker = `w${testInfo.workerIndex}`;
    const randomPart = randomUUID().slice(0, 8);

    return `${project}-${retry}-${worker}-${randomPart}`;
}

function slugify(value: string): string {
    return value
        .toLowerCase()
        .replaceAll(/[^a-z0-9]+/g, '-')
        .replaceAll(/^-|-$/g, '');
}

function phpString(value: string): string {
    return JSON.stringify(value);
}
