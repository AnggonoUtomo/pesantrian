import { execFileSync } from 'node:child_process';
import { randomUUID } from 'node:crypto';
import { mkdirSync, rmSync, writeFileSync } from 'node:fs';
import path from 'node:path';
import type { TestInfo } from '@playwright/test';

export type PrestasiSantriFixture = {
    email: string;
    password: string;
    categoryCode: string;
    categoryName: string;
    achievementCode: string;
    achievementTitle: string;
    submittedTitle: string;
    studentName: string;
    studentNo: string;
    newCategoryCode: string;
    newCategoryName: string;
    newAchievementTitle: string;
};

export function createPrestasiSantriFixture(
    testInfo: TestInfo,
): PrestasiSantriFixture {
    const uniqueId = stableUniqueId(testInfo);
    const codePart = randomUUID()
        .replaceAll('-', '')
        .slice(0, 12)
        .toUpperCase();

    const fixture = {
        email: `e2e-prestasi-${uniqueId}@example.test`,
        password: `E2E-${randomUUID()}`,
        categoryCode: `E2E-PRS-${codePart}`,
        categoryName: `Kategori Prestasi ${uniqueId}`,
        achievementCode: `E2E-PRS-DRAFT-${codePart}`,
        achievementTitle: `Prestasi Draft ${uniqueId}`,
        submittedTitle: `Prestasi Submitted ${uniqueId}`,
        studentName: `Santri Prestasi ${uniqueId}`,
        studentNo: `NIS-PRS-${codePart}`,
        newCategoryCode: `E2E-PRS-NEW-${codePart}`,
        newCategoryName: `Kategori Baru ${uniqueId}`,
        newAchievementTitle: `Prestasi Baru ${uniqueId}`,
    } satisfies PrestasiSantriFixture;

    cleanupPrestasiSantriFixture(fixture);
    runLaravelFixture('setup', fixture, setupScript(fixture));
    assertFixtureReady(fixture);

    return fixture;
}

export function cleanupPrestasiSantriFixture(
    fixture: PrestasiSantriFixture,
): void {
    runLaravelFixture('cleanup', fixture, cleanupScript(fixture));
}

function runLaravelFixture(
    action: 'setup' | 'cleanup',
    fixture: PrestasiSantriFixture,
    phpCode: string,
): string {
    const fixtureDir = path.join(
        process.cwd(),
        'build',
        'playwright',
        'fixtures',
    );
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

function assertFixtureReady(fixture: PrestasiSantriFixture): void {
    const output = runLaravelFixture('setup', fixture, verifyScript(fixture));
    const result = JSON.parse(jsonPayload(output)) as {
        exists: boolean;
        auth: boolean;
        hash: boolean;
        permissions: string[];
        categories: number;
        achievements: number;
        students: number;
    };
    const expectedPermissions = [
        'system.dashboard.view',
        'prestasi_santri.view',
        'prestasi_santri.manage',
        'prestasi_santri.record',
        'prestasi_santri.verify',
        'prestasi_santri.archive',
    ];
    const missingPermissions = expectedPermissions.filter(
        (permission) => !result.permissions.includes(permission),
    );

    if (
        !result.exists ||
        !result.auth ||
        !result.hash ||
        result.categories < 1 ||
        result.achievements < 2 ||
        result.students < 1 ||
        missingPermissions.length > 0
    ) {
        throw new Error(
            `Fixture PrestasiSantri tidak siap: ${JSON.stringify({
                ...result,
                missingPermissions,
            })}`,
        );
    }
}

function setupScript(fixture: PrestasiSantriFixture): string {
    return `<?php

declare(strict_types=1);

use App\\Models\\User;
use App\\Modules\\Pesantrian\\PrestasiSantri\\Infrastructure\\Models\\StudentAchievementCategoryRecord;
use App\\Modules\\Pesantrian\\PrestasiSantri\\Infrastructure\\Models\\StudentAchievementRecord;
use App\\Modules\\Pesantrian\\PrestasiSantri\\Infrastructure\\Models\\StudentAchievementRevisionRecord;
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
$categoryCode = ${phpString(fixture.categoryCode)};
$categoryName = ${phpString(fixture.categoryName)};
$achievementCode = ${phpString(fixture.achievementCode)};
$submittedTitle = ${phpString(fixture.submittedTitle)};
$studentName = ${phpString(fixture.studentName)};
$studentNo = ${phpString(fixture.studentNo)};
$newCategoryCode = ${phpString(fixture.newCategoryCode)};

$achievementIds = DB::table('student_achievements')
    ->where('achievement_no', 'like', 'E2E-PRS-%')
    ->where('student_no', $studentNo)
    ->pluck('id');
StudentAchievementRevisionRecord::query()
    ->whereIn('achievement_id', $achievementIds)
    ->delete();
DB::table('student_achievements')
    ->where('achievement_no', 'like', 'E2E-PRS-%')
    ->where('student_no', $studentNo)
    ->delete();
StudentAchievementCategoryRecord::query()
    ->whereIn('code', [$categoryCode, $newCategoryCode])
    ->delete();
DB::table('students')->where('student_no', $studentNo)->delete();

foreach ([
    'system.dashboard.view',
    'prestasi_santri.view',
    'prestasi_santri.manage',
    'prestasi_santri.record',
    'prestasi_santri.verify',
    'prestasi_santri.archive',
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
    'name' => 'E2E Prestasi Operator',
    'email' => $email,
    'password' => Hash::make($password),
    'status' => UserStatus::ACTIVE,
    'email_verified_at' => now(),
])->save();

$user->givePermissionTo([
    'system.dashboard.view',
    'prestasi_santri.view',
    'prestasi_santri.manage',
    'prestasi_santri.record',
    'prestasi_santri.verify',
    'prestasi_santri.archive',
]);

$studentId = (string) Str::ulid();
DB::table('students')->insert([
    'id' => $studentId,
    'student_no' => $studentNo,
    'full_name' => $studentName,
    'status' => 'active',
    'created_at' => now(),
    'updated_at' => now(),
]);

$category = StudentAchievementCategoryRecord::query()->create([
    'id' => (string) Str::ulid(),
    'code' => $categoryCode,
    'name' => $categoryName,
    'description' => 'Kategori fixture browser QA Prestasi Santri.',
    'status' => 'active',
]);

$draft = StudentAchievementRecord::query()->create([
    'id' => (string) Str::ulid(),
    'achievement_no' => $achievementCode,
    'category_id' => $category->id,
    'category_name' => $category->name,
    'student_id' => $studentId,
    'student_no' => $studentNo,
    'student_name' => $studentName,
    'title' => ${phpString(fixture.achievementTitle)},
    'achievement_type' => 'competition',
    'level' => 'city',
    'result' => 'Juara 1',
    'organizer' => 'Panitia Browser QA',
    'event_name' => 'Lomba Fixture Prestasi',
    'event_location' => 'Aula Fixture',
    'achieved_on' => '2027-09-15',
    'description' => 'Draft fixture untuk QA browser Prestasi Santri.',
    'notes' => 'Fixture browser QA.',
    'status' => 'draft',
    'created_by' => $user->id,
    'created_at' => now(),
    'updated_at' => now(),
]);

StudentAchievementRevisionRecord::query()->create([
    'id' => (string) Str::ulid(),
    'achievement_id' => $draft->id,
    'from_status' => null,
    'to_status' => 'draft',
    'reason' => 'Draft fixture dibuat.',
    'changed_by' => $user->id,
    'changed_at' => now(),
    'summary' => ['action' => 'create'],
    'created_at' => now(),
    'updated_at' => now(),
]);

$submitted = StudentAchievementRecord::query()->create([
    'id' => (string) Str::ulid(),
    'achievement_no' => $achievementCode.'-SUB',
    'category_id' => $category->id,
    'category_name' => $category->name,
    'student_id' => $studentId,
    'student_no' => $studentNo,
    'student_name' => $studentName,
    'title' => $submittedTitle,
    'achievement_type' => 'award',
    'level' => 'province',
    'result' => 'Finalis',
    'organizer' => 'Panitia Browser QA',
    'event_name' => 'Festival Fixture Prestasi',
    'event_location' => 'Gedung Fixture',
    'achieved_on' => '2027-09-16',
    'description' => 'Submitted fixture untuk QA browser Prestasi Santri.',
    'notes' => 'Menunggu verifikasi.',
    'status' => 'submitted',
    'submitted_at' => now(),
    'submitted_by' => $user->id,
    'created_by' => $user->id,
    'created_at' => now(),
    'updated_at' => now(),
]);

StudentAchievementRevisionRecord::query()->create([
    'id' => (string) Str::ulid(),
    'achievement_id' => $submitted->id,
    'from_status' => 'draft',
    'to_status' => 'submitted',
    'reason' => 'Submitted fixture dibuat.',
    'changed_by' => $user->id,
    'changed_at' => now(),
    'summary' => ['action' => 'submit'],
    'created_at' => now(),
    'updated_at' => now(),
]);

app(PermissionRegistrar::class)->forgetCachedPermissions();
`;
}

function cleanupScript(fixture: PrestasiSantriFixture): string {
    return `<?php

declare(strict_types=1);

use App\\Models\\User;
use App\\Modules\\Pesantrian\\PrestasiSantri\\Infrastructure\\Models\\StudentAchievementCategoryRecord;
use App\\Modules\\Pesantrian\\PrestasiSantri\\Infrastructure\\Models\\StudentAchievementRevisionRecord;
use Illuminate\\Contracts\\Console\\Kernel;
use Illuminate\\Support\\Facades\\DB;
use Spatie\\Permission\\PermissionRegistrar;

$basePath = dirname(__DIR__, 3);
require $basePath.'/vendor/autoload.php';
$app = require $basePath.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$email = ${phpString(fixture.email)};
$categoryCode = ${phpString(fixture.categoryCode)};
$studentNo = ${phpString(fixture.studentNo)};
$newCategoryCode = ${phpString(fixture.newCategoryCode)};

$achievementIds = DB::table('student_achievements')
    ->where('achievement_no', 'like', 'E2E-PRS-%')
    ->where('student_no', $studentNo)
    ->pluck('id');
StudentAchievementRevisionRecord::query()
    ->whereIn('achievement_id', $achievementIds)
    ->delete();
DB::table('student_achievements')
    ->where('achievement_no', 'like', 'E2E-PRS-%')
    ->where('student_no', $studentNo)
    ->delete();
StudentAchievementCategoryRecord::query()
    ->whereIn('code', [$categoryCode, $newCategoryCode])
    ->delete();
DB::table('students')->where('student_no', $studentNo)->delete();

$user = User::withTrashed()->where('email', $email)->first();

if ($user instanceof User) {
    $user->syncPermissions([]);
    $user->forceDelete();
}

app(PermissionRegistrar::class)->forgetCachedPermissions();
`;
}

function verifyScript(fixture: PrestasiSantriFixture): string {
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
$categoryCode = ${phpString(fixture.categoryCode)};
$studentNo = ${phpString(fixture.studentNo)};
$user = User::where('email', $email)->first();

echo json_encode([
    'exists' => $user instanceof User,
    'auth' => $user?->canAuthenticate() ?? false,
    'hash' => $user instanceof User ? Hash::check($password, $user->password) : false,
    'permissions' => $user instanceof User ? $user->getAllPermissions()->pluck('name')->values()->all() : [],
    'categories' => DB::table('student_achievement_categories')->where('code', $categoryCode)->count(),
    'achievements' => DB::table('student_achievements')->where('student_no', $studentNo)->count(),
    'students' => DB::table('students')->where('student_no', $studentNo)->count(),
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
        throw new Error(
            `Fixture PrestasiSantri tidak mengembalikan JSON: ${output}`,
        );
    }

    return output.slice(start);
}
