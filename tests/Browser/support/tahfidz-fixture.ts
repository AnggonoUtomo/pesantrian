import { execFileSync } from 'node:child_process';
import { randomUUID } from 'node:crypto';
import { mkdirSync, rmSync, writeFileSync } from 'node:fs';
import path from 'node:path';
import type { TestInfo } from '@playwright/test';

export type TahfidzFixture = {
    email: string;
    password: string;
    programCode: string;
    programName: string;
    studentName: string;
    studentNo: string;
    supervisorName: string;
};

export function createTahfidzFixture(testInfo: TestInfo): TahfidzFixture {
    const uniqueId = stableUniqueId(testInfo);
    const suffix = `e2e-tahfidz-${uniqueId}`;
    const codePart = randomUUID().replaceAll('-', '').slice(0, 12).toUpperCase();

    const fixture = {
        email: `${suffix}@example.test`,
        password: `E2E-${randomUUID()}`,
        programCode: `E2E-THF-${codePart}`,
        programName: `Tahfidz E2E ${uniqueId}`,
        studentName: `Santri Tahfidz ${uniqueId}`,
        studentNo: `NIS-E2E-${codePart}`,
        supervisorName: `Pembimbing Tahfidz ${uniqueId}`,
    } satisfies TahfidzFixture;

    cleanupTahfidzFixture(fixture);
    runLaravelFixture('setup', fixture, setupScript(fixture));
    assertFixtureReady(fixture);

    return fixture;
}

export function cleanupTahfidzFixture(fixture: TahfidzFixture): void {
    runLaravelFixture('cleanup', fixture, cleanupScript(fixture));
}

function runLaravelFixture(
    action: 'setup' | 'cleanup',
    fixture: TahfidzFixture,
    phpCode: string,
): string {
    const fixtureDir = path.join(process.cwd(), 'build', 'playwright', 'fixtures');
    mkdirSync(fixtureDir, { recursive: true });

    const fileName = `${action}-${slugify(fixture.programCode)}.php`;
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

function assertFixtureReady(fixture: TahfidzFixture): void {
    const output = runLaravelFixture('setup', fixture, verifyScript(fixture));
    const result = JSON.parse(jsonPayload(output)) as {
        exists: boolean;
        auth: boolean;
        hash: boolean;
        permissions: string[];
        submissions: number;
    };
    const expectedPermissions = [
        'system.dashboard.view',
        'tahfidz.view',
        'tahfidz.manage',
        'tahfidz.record',
        'tahfidz.review',
        'tahfidz.archive',
    ];
    const missingPermissions = expectedPermissions.filter(
        (permission) => !result.permissions.includes(permission),
    );

    if (
        !result.exists ||
        !result.auth ||
        !result.hash ||
        result.submissions < 2 ||
        missingPermissions.length > 0
    ) {
        throw new Error(
            `Fixture Tahfidz tidak siap: ${JSON.stringify({
                ...result,
                missingPermissions,
            })}`,
        );
    }
}

function setupScript(fixture: TahfidzFixture): string {
    return `<?php

declare(strict_types=1);

use App\\Models\\User;
use App\\Modules\\Academic\\AcademicPeriod\\Infrastructure\\Models\\AcademicTermRecord;
use App\\Modules\\Academic\\AcademicPeriod\\Infrastructure\\Models\\AcademicYearRecord;
use App\\Modules\\HumanResource\\HumanResource\\Infrastructure\\Models\\EmployeeRecord;
use App\\Modules\\Pesantrian\\Santri\\Infrastructure\\Models\\StudentRecord;
use App\\Modules\\Pesantrian\\Tahfidz\\Infrastructure\\Models\\TahfidzProgramRecord;
use App\\Modules\\Pesantrian\\Tahfidz\\Infrastructure\\Models\\TahfidzSubmissionRecord;
use App\\Modules\\Pesantrian\\Tahfidz\\Infrastructure\\Models\\TahfidzSubmissionRevisionRecord;
use App\\Modules\\Pesantrian\\Tahfidz\\Infrastructure\\Models\\TahfidzTargetRecord;
use App\\Modules\\System\\AccessControl\\Infrastructure\\Persistence\\Models\\Permission;
use App\\Modules\\System\\UserManagement\\Domain\\ValueObjects\\UserStatus;
use Illuminate\\Contracts\\Console\\Kernel;
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
$programCode = ${phpString(fixture.programCode)};
$programName = ${phpString(fixture.programName)};
$studentNo = ${phpString(fixture.studentNo)};
$studentName = ${phpString(fixture.studentName)};
$supervisorName = ${phpString(fixture.supervisorName)};

TahfidzSubmissionRevisionRecord::query()
    ->whereHas('submission.program', fn ($query) => $query->where('code', $programCode))
    ->delete();
TahfidzSubmissionRecord::query()
    ->whereHas('program', fn ($query) => $query->where('code', $programCode))
    ->delete();
TahfidzTargetRecord::query()
    ->whereHas('program', fn ($query) => $query->where('code', $programCode))
    ->delete();
TahfidzProgramRecord::query()->where('code', $programCode)->delete();
StudentRecord::query()->where('student_no', $studentNo)->delete();
EmployeeRecord::query()->where('name', $supervisorName)->delete();

foreach (['system.dashboard.view', 'tahfidz.view', 'tahfidz.manage', 'tahfidz.record', 'tahfidz.review', 'tahfidz.archive'] as $permissionName) {
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
    'name' => 'E2E Tahfidz Operator',
    'email' => $email,
    'password' => Hash::make($password),
    'status' => UserStatus::ACTIVE,
    'email_verified_at' => now(),
])->save();

$user->givePermissionTo([
    'system.dashboard.view',
    'tahfidz.view',
    'tahfidz.manage',
    'tahfidz.record',
    'tahfidz.review',
    'tahfidz.archive',
]);

$year = AcademicYearRecord::firstOrCreate(
    ['code' => '2099-2100'],
    [
        'id' => (string) Str::ulid(),
        'name' => 'Tahun E2E 2099/2100',
        'starts_on' => '2099-07-01',
        'ends_on' => '2100-06-30',
        'status' => 'active',
    ],
);

$term = AcademicTermRecord::firstOrCreate(
    [
        'academic_year_id' => $year->id,
        'code' => '2099-2100-E2E',
    ],
    [
        'id' => (string) Str::ulid(),
        'name' => 'Semester E2E Tahfidz',
        'sequence' => 1,
        'starts_on' => '2099-07-01',
        'ends_on' => '2099-12-31',
        'status' => 'active',
        'is_active' => false,
    ],
);

$student = StudentRecord::create([
    'id' => (string) Str::ulid(),
    'student_no' => $studentNo,
    'full_name' => $studentName,
    'gender' => 'male',
    'status' => 'active',
    'entry_date' => now()->toDateString(),
]);

$supervisor = EmployeeRecord::create([
    'id' => (string) Str::ulid(),
    'employee_no' => 'PEG-'.$programCode,
    'name' => $supervisorName,
    'employment_type' => 'ustadz',
    'position' => 'Pembimbing Tahfidz',
    'status' => 'active',
    'joined_on' => now()->subYear()->toDateString(),
]);

$program = TahfidzProgramRecord::create([
    'id' => (string) Str::ulid(),
    'code' => $programCode,
    'name' => $programName,
    'description' => 'Program browser QA Tahfidz.',
    'status' => 'active',
    'created_by' => $user->id,
]);

$target = TahfidzTargetRecord::create([
    'id' => (string) Str::ulid(),
    'program_id' => $program->id,
    'student_id' => $student->id,
    'student_no' => $student->student_no,
    'student_name' => $student->full_name,
    'academic_period_id' => $term->id,
    'period_label' => $term->name,
    'target_juz' => 30,
    'target_surah' => 'An-Naba',
    'target_ayah_from' => 1,
    'target_ayah_to' => 40,
    'target_note' => 'Target fixture browser QA.',
    'status' => 'active',
    'created_by' => $user->id,
]);

TahfidzSubmissionRecord::create([
    'id' => (string) Str::ulid(),
    'program_id' => $program->id,
    'target_id' => $target->id,
    'student_id' => $student->id,
    'student_no' => $student->student_no,
    'student_name' => $student->full_name,
    'supervisor_id' => $supervisor->id,
    'supervisor_name' => $supervisor->name,
    'submission_date' => now()->toDateString(),
    'type' => 'new_memorization',
    'juz' => 30,
    'surah' => 'An-Naba',
    'ayah_from' => 1,
    'ayah_to' => 20,
    'status' => 'submitted',
    'quality_note' => 'Fixture submitted untuk review browser QA.',
    'created_by' => $user->id,
]);

TahfidzSubmissionRecord::create([
    'id' => (string) Str::ulid(),
    'program_id' => $program->id,
    'target_id' => $target->id,
    'student_id' => $student->id,
    'student_no' => $student->student_no,
    'student_name' => $student->full_name,
    'supervisor_id' => $supervisor->id,
    'supervisor_name' => $supervisor->name,
    'submission_date' => now()->subDay()->toDateString(),
    'type' => 'murojaah',
    'juz' => 30,
    'surah' => 'An-Naziat',
    'ayah_from' => 1,
    'ayah_to' => 15,
    'status' => 'draft',
    'quality_note' => 'Fixture draft untuk edit browser QA.',
    'created_by' => $user->id,
]);

app(PermissionRegistrar::class)->forgetCachedPermissions();
`;
}

function cleanupScript(fixture: TahfidzFixture): string {
    return `<?php

declare(strict_types=1);

use App\\Models\\User;
use App\\Modules\\HumanResource\\HumanResource\\Infrastructure\\Models\\EmployeeRecord;
use App\\Modules\\Pesantrian\\Santri\\Infrastructure\\Models\\StudentRecord;
use App\\Modules\\Pesantrian\\Tahfidz\\Infrastructure\\Models\\TahfidzProgramRecord;
use App\\Modules\\Pesantrian\\Tahfidz\\Infrastructure\\Models\\TahfidzSubmissionRecord;
use App\\Modules\\Pesantrian\\Tahfidz\\Infrastructure\\Models\\TahfidzSubmissionRevisionRecord;
use App\\Modules\\Pesantrian\\Tahfidz\\Infrastructure\\Models\\TahfidzTargetRecord;
use Illuminate\\Contracts\\Console\\Kernel;
use Spatie\\Permission\\PermissionRegistrar;

$basePath = dirname(__DIR__, 3);
require $basePath.'/vendor/autoload.php';
$app = require $basePath.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$email = ${phpString(fixture.email)};
$programCode = ${phpString(fixture.programCode)};
$studentNo = ${phpString(fixture.studentNo)};
$supervisorName = ${phpString(fixture.supervisorName)};

TahfidzSubmissionRevisionRecord::query()
    ->whereHas('submission.program', fn ($query) => $query->where('code', $programCode))
    ->delete();
TahfidzSubmissionRecord::query()
    ->whereHas('program', fn ($query) => $query->where('code', $programCode))
    ->delete();
TahfidzTargetRecord::query()
    ->whereHas('program', fn ($query) => $query->where('code', $programCode))
    ->delete();
TahfidzProgramRecord::query()->where('code', $programCode)->delete();
StudentRecord::query()->where('student_no', $studentNo)->delete();
EmployeeRecord::query()->where('name', $supervisorName)->delete();

$user = User::withTrashed()->where('email', $email)->first();

if ($user instanceof User) {
    $user->syncPermissions([]);
    $user->forceDelete();
}

app(PermissionRegistrar::class)->forgetCachedPermissions();
`;
}

function verifyScript(fixture: TahfidzFixture): string {
    return `<?php

declare(strict_types=1);

use App\\Models\\User;
use App\\Modules\\Pesantrian\\Tahfidz\\Infrastructure\\Models\\TahfidzSubmissionRecord;
use Illuminate\\Contracts\\Console\\Kernel;
use Illuminate\\Support\\Facades\\Hash;

$basePath = dirname(__DIR__, 3);
require $basePath.'/vendor/autoload.php';
$app = require $basePath.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$email = ${phpString(fixture.email)};
$password = ${phpString(fixture.password)};
$programCode = ${phpString(fixture.programCode)};
$user = User::where('email', $email)->first();

echo json_encode([
    'exists' => $user instanceof User,
    'auth' => $user?->canAuthenticate() ?? false,
    'hash' => $user instanceof User ? Hash::check($password, $user->password) : false,
    'permissions' => $user instanceof User ? $user->getAllPermissions()->pluck('name')->values()->all() : [],
    'submissions' => TahfidzSubmissionRecord::query()
        ->whereHas('program', fn ($query) => $query->where('code', $programCode))
        ->count(),
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
        throw new Error(`Fixture Tahfidz tidak mengembalikan JSON: ${output}`);
    }

    return output.slice(start);
}
