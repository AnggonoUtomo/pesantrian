<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\System\AccessControl\Database\Seeders\AccessControlSeeder;
use App\Modules\System\AuditLog\Application\Contracts\AuditRecorder;
use App\Modules\System\AuditLog\Application\DTO\AuditEntryData;
use App\Modules\System\SystemSetting\Database\Seeders\SystemSettingSeeder;
use App\Modules\System\SystemSetting\Infrastructure\Persistence\Models\SystemSettingRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

function presentationAuditEntry(
    string $actorId,
    string $action = 'user.updated',
    ?DateTimeImmutable $occurredAt = null,
): AuditEntryData {
    return new AuditEntryData(
        eventId: (string) Str::ulid(),
        actorId: $actorId,
        action: $action,
        subjectType: 'user',
        subjectId: (string) Str::ulid(),
        module: 'UserManagement',
        correlationId: (string) Str::ulid(),
        reason: null,
        metadata: ['changed_fields' => ['name']],
        occurredAt: $occurredAt ?? new DateTimeImmutable,
    );
}

beforeEach(function (): void {
    $this->seed(AccessControlSeeder::class);
    $this->seed(SystemSettingSeeder::class);
});

it('menolak actor yang tidak memiliki permission audit', function (): void {
    $actor = User::factory()->create();

    $this->actingAs($actor)
        ->get(route('system.audit-logs.index'))
        ->assertForbidden();
});

it('hanya menampilkan audit milik actor biasa', function (): void {
    $actor = User::factory()->create();
    $other = User::factory()->create();
    $actor->givePermissionTo('audit_log.view');
    app(AuditRecorder::class)->record(presentationAuditEntry($actor->id, 'user.updated'));
    app(AuditRecorder::class)->record(presentationAuditEntry($other->id, 'user.deleted'));

    $this->actingAs($actor)
        ->get(route('system.audit-logs.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('System/AuditLog/pages/Index')
            ->has('auditLogs.data', 1)
            ->where('auditLogs.data.0.actionLabel', 'Data pengguna diperbarui')
            ->where('auditLogs.meta.total', 1));
});

it('memberikan seluruh audit kepada SuperSystem', function (): void {
    $superSystem = User::query()->where('email', 'super-system@example.test')->firstOrFail();
    $other = User::factory()->create();
    app(AuditRecorder::class)->record(presentationAuditEntry($superSystem->id));
    app(AuditRecorder::class)->record(presentationAuditEntry($other->id));

    $this->actingAs($superSystem)
        ->get(route('system.audit-logs.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->has('auditLogs.data', 2)
            ->where('auditLogs.meta.total', 2));
});

it('mengembalikan 404 untuk detail di luar scope actor', function (): void {
    $actor = User::factory()->create();
    $other = User::factory()->create();
    $actor->givePermissionTo('audit_log.view');
    $record = app(AuditRecorder::class)->record(presentationAuditEntry($other->id));

    $this->actingAs($actor)
        ->get(route('system.audit-logs.show', $record->id))
        ->assertNotFound();
});

it('memfilter audit dan membatasi nilai per page', function (): void {
    $superSystem = User::query()->where('email', 'super-system@example.test')->firstOrFail();
    app(AuditRecorder::class)->record(presentationAuditEntry($superSystem->id, 'user.updated'));
    app(AuditRecorder::class)->record(presentationAuditEntry($superSystem->id, 'user.deleted'));

    $this->actingAs($superSystem)
        ->get(route('system.audit-logs.index', [
            'action' => 'user.deleted',
            'per_page' => 999,
        ]))
        ->assertSessionHasErrors('per_page');

    $this->actingAs($superSystem)
        ->get(route('system.audit-logs.index', ['action' => 'user.deleted']))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->has('auditLogs.data', 1)
            ->where('auditLogs.data.0.actionLabel', 'Pengguna diarsipkan'));
});

it('mengurutkan waktu audit dan tidak mengirim identifier teknis ke UI', function (): void {
    $superSystem = User::query()->where('email', 'super-system@example.test')->firstOrFail();
    app(AuditRecorder::class)->record(presentationAuditEntry(
        $superSystem->id,
        'user.updated',
        new DateTimeImmutable('2026-08-10 09:00:00'),
    ));
    app(AuditRecorder::class)->record(presentationAuditEntry(
        $superSystem->id,
        'user.deleted',
        new DateTimeImmutable('2026-08-10 10:00:00'),
    ));

    $this->actingAs($superSystem)
        ->get(route('system.audit-logs.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('auditLogs.data.0.actionLabel', 'Pengguna diarsipkan')
            ->missing('auditLogs.data.0.id')
            ->missing('auditLogs.data.0.eventId')
            ->missing('auditLogs.data.0.actorId')
            ->missing('auditLogs.data.0.subjectId')
            ->missing('auditLogs.data.0.correlationId')
            ->missing('auditLogs.data.0.metadata'));

    $this->actingAs($superSystem)
        ->get(route('system.audit-logs.index', ['sort_direction' => 'asc']))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('auditLogs.data.0.actionLabel', 'Data pengguna diperbarui')
            ->where('filters.sort_direction', 'asc'));

    $this->actingAs($superSystem)
        ->get(route('system.audit-logs.index', ['sort_direction' => 'invalid']))
        ->assertSessionHasErrors('sort_direction');
});

it('menerjemahkan istilah teknis audit menjadi label operator', function (): void {
    $superSystem = User::query()->where('email', 'super-system@example.test')->firstOrFail();
    app(AuditRecorder::class)->record(presentationAuditEntry($superSystem->id, 'user.updated'));

    $this->actingAs($superSystem)
        ->get(route('system.audit-logs.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('auditLogs.data.0.actionLabel', 'Data pengguna diperbarui')
            ->where('auditLogs.data.0.subjectLabel', 'Pengguna')
            ->where('auditLogs.data.0.moduleLabel', 'Manajemen pengguna')
            ->missing('auditLogs.data.0.action')
            ->missing('auditLogs.data.0.subjectType')
            ->missing('auditLogs.data.0.module'));
});

it('menampilkan perubahan SystemSetting dengan kategori dan nilai sebelum serta sesudah yang aman', function (): void {
    $superSystem = User::query()->where('email', 'super-system@example.test')->firstOrFail();
    app(AuditRecorder::class)->record(new AuditEntryData(
        eventId: (string) Str::ulid(),
        actorId: $superSystem->id,
        action: 'system_setting.updated',
        subjectType: 'system_setting',
        subjectId: (string) Str::ulid(),
        module: 'SystemSetting',
        correlationId: (string) Str::ulid(),
        reason: 'Menyesuaikan daftar pengguna.',
        metadata: [
            'setting_key' => 'pagination.default_per_page',
            'setting_category' => 'Pagination',
            'setting_label' => 'Jumlah data per halaman saat pengguna belum memilih ukuran.',
            'before_value' => 25,
            'after_value' => 50,
            'result' => 'updated',
        ],
        occurredAt: new DateTimeImmutable,
    ));

    $this->actingAs($superSystem)
        ->get(route('system.audit-logs.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('auditLogs.data.0.actionLabel', 'Pengaturan sistem diperbarui')
            ->where('auditLogs.data.0.settingChange.category', 'Pagination')
            ->where('auditLogs.data.0.settingChange.setting', 'Jumlah data per halaman saat pengguna belum memilih ukuran.')
            ->where('auditLogs.data.0.settingChange.beforeValue', '25')
            ->where('auditLogs.data.0.settingChange.afterValue', '50')
            ->missing('auditLogs.data.0.metadata')
            ->missing('auditLogs.data.0.settingKey'));
});

it('menyamarkan nilai rahasia pada ringkasan perubahan SystemSetting', function (): void {
    $superSystem = User::query()->where('email', 'super-system@example.test')->firstOrFail();
    app(AuditRecorder::class)->record(new AuditEntryData(
        eventId: (string) Str::ulid(),
        actorId: $superSystem->id,
        action: 'system_setting.updated',
        subjectType: 'system_setting',
        subjectId: (string) Str::ulid(),
        module: 'SystemSetting',
        correlationId: (string) Str::ulid(),
        reason: 'Mengganti kredensial SMTP.',
        metadata: [
            'setting_key' => 'mail.password',
            'setting_category' => 'Email',
            'setting_label' => 'Password SMTP terenkripsi.',
            'before_value' => '[REDACTED]',
            'after_value' => '[REDACTED]',
            'result' => 'updated',
        ],
        occurredAt: new DateTimeImmutable,
    ));

    $this->actingAs($superSystem)
        ->get(route('system.audit-logs.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('auditLogs.data.0.settingChange.beforeValue', 'Disamarkan')
            ->where('auditLogs.data.0.settingChange.afterValue', 'Disamarkan')
            ->missing('auditLogs.data.0.metadata'));
});

it('menerjemahkan record SystemSetting lama tanpa mengirim key teknis ke UI', function (): void {
    $superSystem = User::query()->where('email', 'super-system@example.test')->firstOrFail();
    app(AuditRecorder::class)->record(new AuditEntryData(
        eventId: (string) Str::ulid(),
        actorId: $superSystem->id,
        action: 'system_setting.updated',
        subjectType: 'system_setting',
        subjectId: (string) Str::ulid(),
        module: 'SystemSetting',
        correlationId: (string) Str::ulid(),
        reason: 'Menyesuaikan daftar pengguna.',
        metadata: [
            'setting_key' => 'pagination.default_per_page',
            'before_value' => 25,
            'after_value' => 50,
            'result' => 'updated',
        ],
        occurredAt: new DateTimeImmutable,
    ));

    $this->actingAs($superSystem)
        ->get(route('system.audit-logs.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('auditLogs.data.0.settingChange.category', 'Pagination')
            ->where('auditLogs.data.0.settingChange.setting', 'Jumlah data per halaman saat pengguna belum memilih ukuran.')
            ->where('auditLogs.data.0.settingChange.beforeValue', '25')
            ->where('auditLogs.data.0.settingChange.afterValue', '50')
            ->missing('auditLogs.data.0.settingKey')
            ->missing('auditLogs.data.0.metadata'));
});

it('menampilkan konteks keamanan tanpa membuka alamat IP lengkap', function (): void {
    $superSystem = User::query()->where('email', 'super-system@example.test')->firstOrFail();
    $this->app->instance(Request::class, Request::create('/login', 'POST', [], [], [], [
        'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/139.0.0.0 Safari/537.36',
        'REMOTE_ADDR' => '203.0.113.42',
    ]));
    app(AuditRecorder::class)->record(new AuditEntryData(
        eventId: (string) Str::ulid(),
        actorId: $superSystem->id,
        action: 'authentication.signed_in',
        subjectType: 'account',
        subjectId: $superSystem->id,
        module: 'Authentication',
        correlationId: (string) Str::ulid(),
        reason: null,
        metadata: [],
        occurredAt: new DateTimeImmutable,
    ));

    $this->actingAs($superSystem)
        ->get(route('system.audit-logs.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('auditLogs.data.0.actionLabel', 'Masuk ke akun')
            ->where('auditLogs.data.0.securityContext.browser', 'Chrome di Windows')
            ->where('auditLogs.data.0.securityContext.ipAddress', '203.0.113.xxx')
            ->missing('auditLogs.data.0.metadata'));
});

it('memakai pagination global untuk audit log', function (): void {
    $superSystem = User::query()->where('email', 'super-system@example.test')->firstOrFail();
    SystemSettingRecord::query()->where('key', 'pagination.per_page_options')->update(['value' => json_encode([10, 20], JSON_THROW_ON_ERROR)]);
    SystemSettingRecord::query()->where('key', 'pagination.default_per_page')->update(['value' => json_encode(10, JSON_THROW_ON_ERROR)]);
    app(AuditRecorder::class)->record(presentationAuditEntry($superSystem->id));

    $this->actingAs($superSystem)
        ->get(route('system.audit-logs.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('auditLogs.meta.perPage', 10)
            ->where('pagination.perPageOptions', [10, 20])
            ->where('pagination.defaultPerPage', 10));

    $this->actingAs($superSystem)
        ->get(route('system.audit-logs.index', ['per_page' => 25]))
        ->assertSessionHasErrors('per_page');
});

it('menyediakan response envelope pada API internal', function (): void {
    $superSystem = User::query()->where('email', 'super-system@example.test')->firstOrFail();
    app(AuditRecorder::class)->record(presentationAuditEntry($superSystem->id));

    $this->actingAs($superSystem)
        ->getJson(route('api.v1.audit-logs.index'))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.meta.total', 1)
        ->assertJsonStructure(['success', 'data' => ['data', 'meta']]);
});
