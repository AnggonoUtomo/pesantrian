import { execFileSync } from 'node:child_process';
import { randomUUID } from 'node:crypto';
import { mkdirSync, rmSync, writeFileSync } from 'node:fs';
import path from 'node:path';
import type { TestInfo } from '@playwright/test';

export type PresensiSantriFixture = {
    email: string;
    password: string;
    sessionCode: string;
    sessionName: string;
    sessionNameAfterEdit: string;
    studentOneName: string;
    studentTwoName: string;
};

export function createPresensiSantriFixture(
    testInfo: TestInfo,
): PresensiSantriFixture {
    const uniqueId = stableUniqueId(testInfo);
    const suffix = `e2e-presensi-${uniqueId}`;
    const sessionCode = `E2E-${randomUUID().replaceAll('-', '').slice(0, 16).toUpperCase()}`;

    const fixture = {
        email: `${suffix}@example.test`,
        password: `E2E-${randomUUID()}`,
        sessionCode,
        sessionName: `Presensi E2E ${uniqueId}`,
        sessionNameAfterEdit: `Presensi E2E Update ${uniqueId}`,
        studentOneName: `Ahmad Presensi ${uniqueId}`,
        studentTwoName: `Budi Presensi ${uniqueId}`,
    } satisfies PresensiSantriFixture;

    cleanupPresensiSantriFixture(fixture);
    runLaravelFixture('setup', fixture, setupScript(fixture));
    assertFixtureReady(fixture);

    return fixture;
}

export function cleanupPresensiSantriFixture(
    fixture: PresensiSantriFixture,
): void {
    runLaravelFixture('cleanup', fixture, cleanupScript(fixture));
}

function runLaravelFixture(
    action: 'setup' | 'cleanup',
    fixture: PresensiSantriFixture,
    phpCode: string,
): string {
    const fixtureDir = path.join(process.cwd(), 'build', 'playwright', 'fixtures');
    mkdirSync(fixtureDir, { recursive: true });

    const fileName = `${action}-${slugify(fixture.sessionCode)}.php`;
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

function assertFixtureReady(fixture: PresensiSantriFixture): void {
    const output = runLaravelFixture('setup', fixture, verifyScript(fixture));
    const result = JSON.parse(output) as {
        auth: boolean;
        exists: boolean;
        hash: boolean;
        permissions: string[];
        students: number;
    };
    const expectedPermissions = [
        'system.dashboard.view',
        'presensi_santri.view',
        'presensi_santri.manage',
        'presensi_santri.submit',
        'presensi_santri.revise',
        'presensi_santri.archive',
    ];
    const missingPermissions = expectedPermissions.filter(
        (permission) => !result.permissions.includes(permission),
    );

    if (
        !result.exists ||
        !result.auth ||
        !result.hash ||
        result.students < 2 ||
        missingPermissions.length > 0
    ) {
        throw new Error(
            `Fixture PresensiSantri tidak siap: ${JSON.stringify({
                ...result,
                missingPermissions,
            })}`,
        );
    }
}

function setupScript(fixture: PresensiSantriFixture): string {
    return `<?php

declare(strict_types=1);

use App\\Models\\User;
use App\\Modules\\Pesantrian\\PresensiSantri\\Infrastructure\\Models\\StudentAttendanceSessionRecord;
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
$sessionCode = ${phpString(fixture.sessionCode)};
$studentOneName = ${phpString(fixture.studentOneName)};
$studentTwoName = ${phpString(fixture.studentTwoName)};

StudentAttendanceSessionRecord::query()
    ->where('session_code', 'like', $sessionCode.'%')
    ->delete();
DB::table('students')
    ->whereIn('full_name', [$studentOneName, $studentTwoName])
    ->orWhere('student_no', 'like', $sessionCode.'%')
    ->delete();

foreach (['system.dashboard.view', 'presensi_santri.view', 'presensi_santri.manage', 'presensi_santri.submit', 'presensi_santri.revise', 'presensi_santri.archive'] as $permissionName) {
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
    'name' => 'E2E Presensi Operator',
    'email' => $email,
    'password' => Hash::make($password),
    'status' => UserStatus::ACTIVE,
    'email_verified_at' => now(),
])->save();

$user->givePermissionTo([
    'system.dashboard.view',
    'presensi_santri.view',
    'presensi_santri.manage',
    'presensi_santri.submit',
    'presensi_santri.revise',
    'presensi_santri.archive',
]);

$studentRows = [
    [
        'id' => (string) Str::ulid(),
        'student_no' => $sessionCode.'-001',
        'full_name' => $studentOneName,
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ],
    [
        'id' => (string) Str::ulid(),
        'student_no' => $sessionCode.'-002',
        'full_name' => $studentTwoName,
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ],
];

DB::table('students')->insert($studentRows);

app(PermissionRegistrar::class)->forgetCachedPermissions();
`;
}

function cleanupScript(fixture: PresensiSantriFixture): string {
    return `<?php

declare(strict_types=1);

use App\\Models\\User;
use App\\Modules\\Pesantrian\\PresensiSantri\\Infrastructure\\Models\\StudentAttendanceSessionRecord;
use Illuminate\\Contracts\\Console\\Kernel;
use Illuminate\\Support\\Facades\\DB;
use Spatie\\Permission\\PermissionRegistrar;

$basePath = dirname(__DIR__, 3);
require $basePath.'/vendor/autoload.php';
$app = require $basePath.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$email = ${phpString(fixture.email)};
$sessionCode = ${phpString(fixture.sessionCode)};
$studentOneName = ${phpString(fixture.studentOneName)};
$studentTwoName = ${phpString(fixture.studentTwoName)};

StudentAttendanceSessionRecord::query()
    ->where('session_code', 'like', $sessionCode.'%')
    ->delete();
DB::table('students')
    ->whereIn('full_name', [$studentOneName, $studentTwoName])
    ->orWhere('student_no', 'like', $sessionCode.'%')
    ->delete();

$user = User::withTrashed()->where('email', $email)->first();

if ($user instanceof User) {
    $user->syncPermissions([]);
    $user->forceDelete();
}

app(PermissionRegistrar::class)->forgetCachedPermissions();
`;
}

function verifyScript(fixture: PresensiSantriFixture): string {
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
$studentOneName = ${phpString(fixture.studentOneName)};
$studentTwoName = ${phpString(fixture.studentTwoName)};
$user = User::where('email', $email)->first();

echo json_encode([
    'auth' => $user?->canAuthenticate() ?? false,
    'exists' => $user instanceof User,
    'hash' => $user instanceof User ? Hash::check($password, $user->password) : false,
    'permissions' => $user instanceof User ? $user->getAllPermissions()->pluck('name')->values()->all() : [],
    'students' => DB::table('students')->whereIn('full_name', [$studentOneName, $studentTwoName])->count(),
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
