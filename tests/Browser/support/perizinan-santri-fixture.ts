import { execFileSync } from 'node:child_process';
import { randomUUID } from 'node:crypto';
import { mkdirSync, rmSync, writeFileSync } from 'node:fs';
import path from 'node:path';
import type { TestInfo } from '@playwright/test';

export type PerizinanSantriFixture = {
    email: string;
    password: string;
    unitCode: string;
    studentName: string;
    studentNo: string;
};

export function createPerizinanSantriFixture(
    testInfo: TestInfo,
): PerizinanSantriFixture {
    const uniqueId = stableUniqueId(testInfo);
    const codePart = randomUUID().replaceAll('-', '').slice(0, 12).toUpperCase();

    const fixture = {
        email: `e2e-perizinan-${uniqueId}@example.test`,
        password: `E2E-${randomUUID()}`,
        unitCode: `E2E-IZN-${codePart}`,
        studentName: `Santri Perizinan ${uniqueId}`,
        studentNo: `NIS-IZN-${codePart}`,
    } satisfies PerizinanSantriFixture;

    cleanupPerizinanSantriFixture(fixture);
    runLaravelFixture('setup', fixture, setupScript(fixture));
    assertFixtureReady(fixture);

    return fixture;
}

export function cleanupPerizinanSantriFixture(
    fixture: PerizinanSantriFixture,
): void {
    runLaravelFixture('cleanup', fixture, cleanupScript(fixture));
}

function runLaravelFixture(
    action: 'setup' | 'cleanup',
    fixture: PerizinanSantriFixture,
    phpCode: string,
): string {
    const fixtureDir = path.join(process.cwd(), 'build', 'playwright', 'fixtures');
    mkdirSync(fixtureDir, { recursive: true });

    const fileName = `${action}-${slugify(fixture.studentNo)}.php`;
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

function assertFixtureReady(fixture: PerizinanSantriFixture): void {
    const output = runLaravelFixture('setup', fixture, verifyScript(fixture));
    const result = JSON.parse(jsonPayload(output)) as {
        exists: boolean;
        auth: boolean;
        hash: boolean;
        permissions: string[];
        students: number;
        guardians: number;
    };
    const expectedPermissions = [
        'system.dashboard.view',
        'perizinan_santri.view',
        'perizinan_santri.manage',
        'perizinan_santri.approve',
        'perizinan_santri.checkout',
        'perizinan_santri.return',
        'perizinan_santri.archive',
    ];
    const missingPermissions = expectedPermissions.filter(
        (permission) => !result.permissions.includes(permission),
    );

    if (
        !result.exists ||
        !result.auth ||
        !result.hash ||
        result.students < 1 ||
        result.guardians < 1 ||
        missingPermissions.length > 0
    ) {
        throw new Error(
            `Fixture PerizinanSantri tidak siap: ${JSON.stringify({
                ...result,
                missingPermissions,
            })}`,
        );
    }
}

function setupScript(fixture: PerizinanSantriFixture): string {
    return `<?php

declare(strict_types=1);

use App\\Models\\User;
use App\\Modules\\Pesantrian\\PerizinanSantri\\Infrastructure\\Models\\StudentPermitRevisionRecord;
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
$unitCode = ${phpString(fixture.unitCode)};
$studentName = ${phpString(fixture.studentName)};
$studentNo = ${phpString(fixture.studentNo)};

StudentPermitRevisionRecord::query()
    ->whereHas('permit', fn ($query) => $query->where('student_no', $studentNo))
    ->delete();
DB::table('student_permits')->where('student_no', $studentNo)->delete();
DB::table('student_guardians')->where('student_id', function ($query) use ($studentNo) {
    $query->select('id')->from('students')->where('student_no', $studentNo)->limit(1);
})->delete();
DB::table('students')->where('student_no', $studentNo)->delete();
DB::table('organization_units')->where('code', $unitCode)->delete();

foreach ([
    'system.dashboard.view',
    'perizinan_santri.view',
    'perizinan_santri.manage',
    'perizinan_santri.approve',
    'perizinan_santri.checkout',
    'perizinan_santri.return',
    'perizinan_santri.archive',
] as $permissionName) {
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
    'name' => 'E2E Perizinan Operator',
    'email' => $email,
    'password' => Hash::make($password),
    'status' => UserStatus::ACTIVE,
    'email_verified_at' => now(),
])->save();

$user->givePermissionTo([
    'system.dashboard.view',
    'perizinan_santri.view',
    'perizinan_santri.manage',
    'perizinan_santri.approve',
    'perizinan_santri.checkout',
    'perizinan_santri.return',
    'perizinan_santri.archive',
]);

$unitId = (string) Str::ulid();
$studentId = (string) Str::ulid();

DB::table('organization_units')->insert([
    'id' => $unitId,
    'code' => $unitCode,
    'name' => 'Unit E2E Perizinan',
    'type' => 'education_unit',
    'status' => 'active',
    'created_at' => now(),
    'updated_at' => now(),
]);

DB::table('students')->insert([
    'id' => $studentId,
    'student_no' => $studentNo,
    'full_name' => $studentName,
    'primary_unit_id' => $unitId,
    'status' => 'active',
    'created_at' => now(),
    'updated_at' => now(),
]);

DB::table('student_guardians')->insert([
    'id' => (string) Str::ulid(),
    'student_id' => $studentId,
    'guardian_name' => 'Wali Browser Perizinan',
    'guardian_phone' => '081234567890',
    'guardian_relation' => 'ayah',
    'is_primary' => true,
    'is_emergency_contact' => true,
    'created_at' => now(),
    'updated_at' => now(),
]);

app(PermissionRegistrar::class)->forgetCachedPermissions();
`;
}

function cleanupScript(fixture: PerizinanSantriFixture): string {
    return `<?php

declare(strict_types=1);

use App\\Models\\User;
use App\\Modules\\Pesantrian\\PerizinanSantri\\Infrastructure\\Models\\StudentPermitRevisionRecord;
use Illuminate\\Contracts\\Console\\Kernel;
use Illuminate\\Support\\Facades\\DB;
use Spatie\\Permission\\PermissionRegistrar;

$basePath = dirname(__DIR__, 3);
require $basePath.'/vendor/autoload.php';
$app = require $basePath.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$email = ${phpString(fixture.email)};
$unitCode = ${phpString(fixture.unitCode)};
$studentNo = ${phpString(fixture.studentNo)};

StudentPermitRevisionRecord::query()
    ->whereHas('permit', fn ($query) => $query->where('student_no', $studentNo))
    ->delete();
DB::table('student_permits')->where('student_no', $studentNo)->delete();
DB::table('student_guardians')->where('student_id', function ($query) use ($studentNo) {
    $query->select('id')->from('students')->where('student_no', $studentNo)->limit(1);
})->delete();
DB::table('students')->where('student_no', $studentNo)->delete();
DB::table('organization_units')->where('code', $unitCode)->delete();

$user = User::withTrashed()->where('email', $email)->first();

if ($user instanceof User) {
    $user->syncPermissions([]);
    $user->forceDelete();
}

app(PermissionRegistrar::class)->forgetCachedPermissions();
`;
}

function verifyScript(fixture: PerizinanSantriFixture): string {
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
$studentNo = ${phpString(fixture.studentNo)};
$user = User::where('email', $email)->first();
$studentId = DB::table('students')->where('student_no', $studentNo)->value('id');

echo json_encode([
    'exists' => $user instanceof User,
    'auth' => $user?->canAuthenticate() ?? false,
    'hash' => $user instanceof User ? Hash::check($password, $user->password) : false,
    'permissions' => $user instanceof User ? $user->getAllPermissions()->pluck('name')->values()->all() : [],
    'students' => DB::table('students')->where('student_no', $studentNo)->count(),
    'guardians' => $studentId === null ? 0 : DB::table('student_guardians')->where('student_id', $studentId)->count(),
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

function jsonPayload(output: string): string {
    const start = output.lastIndexOf('{');

    if (start === -1) {
        throw new Error(`Fixture PerizinanSantri tidak mengembalikan JSON: ${output}`);
    }

    return output.slice(start);
}
