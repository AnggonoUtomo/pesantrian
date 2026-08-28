<?php

declare(strict_types=1);

use App\Models\User;
use App\Modules\Academic\AcademicPeriod\Infrastructure\Models\AcademicTermRecord;
use App\Modules\Academic\AcademicPeriod\Infrastructure\Models\AcademicYearRecord;
use App\Modules\System\AccessControl\Infrastructure\Persistence\Models\Permission;
use App\Modules\System\AuditLog\Infrastructure\Persistence\Models\AuditRecord;
use Illuminate\Support\Str;

it('mengembalikan list tahun akademik dengan filter pagination sort dan envelope canonical', function (): void {
    $view = Permission::create(['name' => 'academic_period.view', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($view);
    $year = AcademicYearRecord::query()->create([
        'code' => '2026-2027',
        'name' => 'Tahun Akademik 2026/2027',
        'starts_on' => '2026-07-01',
        'ends_on' => '2027-06-30',
        'status' => 'draft',
    ]);
    AcademicYearRecord::query()->create([
        'code' => '2025-2026',
        'name' => 'Tahun Akademik Lama',
        'starts_on' => '2025-07-01',
        'ends_on' => '2026-06-30',
        'status' => 'closed',
    ]);

    $query = http_build_query([
        'search' => '2026/2027',
        'filter' => ['status' => 'draft'],
        'page' => 1,
        'per_page' => 10,
        'sort' => 'code',
    ]);

    $this->actingAs($actor)
        ->getJson(route('api.v1.academic.periods.years.index').'?'.$query)
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Daftar tahun akademik berhasil dibaca.')
        ->assertJsonPath('data.0.id', $year->id)
        ->assertJsonPath('data.0.code', '2026-2027')
        ->assertJsonPath('data.0.name', 'Tahun Akademik 2026/2027')
        ->assertJsonPath('data.0.status', 'draft')
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonPath('meta.per_page', 10)
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('meta.last_page', 1)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [[
                'id',
                'code',
                'name',
                'starts_on',
                'ends_on',
                'status',
                'created_at',
                'updated_at',
            ]],
            'meta' => ['correlation_id', 'current_page', 'per_page', 'total', 'last_page'],
        ]);
});

it('membuat dan memperbarui tahun akademik melalui API terotorisasi', function (): void {
    $manage = Permission::create(['name' => 'academic_period.manage', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($manage);

    $created = $this->actingAs($actor)->postJson(route('api.v1.academic.periods.years.store'), [
        'code' => '2026-2027',
        'name' => 'Tahun Akademik 2026/2027',
        'starts_on' => '2026-07-01',
        'ends_on' => '2027-06-30',
        'status' => 'draft',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Tahun akademik berhasil dibuat.')
        ->assertJsonPath('data.code', '2026-2027')
        ->assertJsonPath('data.status', 'draft');

    $yearId = (string) $created->json('data.id');

    $this->actingAs($actor)->patchJson(route('api.v1.academic.periods.years.update', $yearId), [
        'name' => 'TA 2026/2027',
        'status' => 'active',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Tahun akademik berhasil diperbarui.')
        ->assertJsonPath('data.name', 'TA 2026/2027')
        ->assertJsonPath('data.status', 'active');

    $this->assertDatabaseHas('academic_years', [
        'id' => $yearId,
        'code' => '2026-2027',
        'name' => 'TA 2026/2027',
        'status' => 'active',
    ]);
});

it('mengembalikan list term akademik dan membuat update term melalui API terotorisasi', function (): void {
    $view = Permission::create(['name' => 'academic_period.view', 'guard_name' => 'web']);
    $manage = Permission::create(['name' => 'academic_period.manage', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo([$view, $manage]);
    $year = AcademicYearRecord::query()->create([
        'code' => '2026-2027',
        'name' => 'Tahun Akademik 2026/2027',
        'starts_on' => '2026-07-01',
        'ends_on' => '2027-06-30',
        'status' => 'active',
    ]);
    AcademicTermRecord::query()->create([
        'academic_year_id' => $year->id,
        'code' => '2026-2027-GANJIL',
        'name' => 'Semester Ganjil',
        'sequence' => 1,
        'starts_on' => '2026-07-01',
        'ends_on' => '2026-12-31',
        'status' => 'draft',
        'is_active' => false,
    ]);

    $this->actingAs($actor)
        ->getJson(route('api.v1.academic.periods.terms.index', [
            'filter' => ['academic_year_id' => $year->id, 'status' => 'draft'],
            'per_page' => 10,
            'sort' => 'sequence',
        ]))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Daftar term akademik berhasil dibaca.')
        ->assertJsonPath('data.0.code', '2026-2027-GANJIL')
        ->assertJsonPath('data.0.academic_year_id', $year->id)
        ->assertJsonPath('data.0.is_active', false);

    $created = $this->actingAs($actor)->postJson(route('api.v1.academic.periods.terms.store'), [
        'academic_year_id' => $year->id,
        'code' => '2026-2027-GENAP',
        'name' => 'Semester Genap',
        'sequence' => 2,
        'starts_on' => '2027-01-01',
        'ends_on' => '2027-06-30',
        'status' => 'draft',
        'is_active' => false,
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertCreated()
        ->assertJsonPath('message', 'Term akademik berhasil dibuat.')
        ->assertJsonPath('data.code', '2026-2027-GENAP')
        ->assertJsonPath('data.sequence', 2);

    $termId = (string) $created->json('data.id');

    $this->actingAs($actor)->patchJson(route('api.v1.academic.periods.terms.update', $termId), [
        'name' => 'Semester 2',
        'status' => 'active',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertOk()
        ->assertJsonPath('message', 'Term akademik berhasil diperbarui.')
        ->assertJsonPath('data.name', 'Semester 2')
        ->assertJsonPath('data.status', 'active');
});

it('mengelola lifecycle active term global melalui API terotorisasi', function (): void {
    $view = Permission::create(['name' => 'academic_period.view', 'guard_name' => 'web']);
    $manage = Permission::create(['name' => 'academic_period.manage', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo([$view, $manage]);
    $year = AcademicYearRecord::query()->create([
        'code' => '2026-2027',
        'name' => 'Tahun Akademik 2026/2027',
        'starts_on' => '2026-07-01',
        'ends_on' => '2027-06-30',
        'status' => 'draft',
    ]);
    $ganjil = AcademicTermRecord::query()->create([
        'academic_year_id' => $year->id,
        'code' => '2026-2027-GANJIL',
        'name' => 'Semester Ganjil',
        'sequence' => 1,
        'starts_on' => '2026-07-01',
        'ends_on' => '2026-12-31',
        'status' => 'draft',
        'is_active' => false,
    ]);
    $genap = AcademicTermRecord::query()->create([
        'academic_year_id' => $year->id,
        'code' => '2026-2027-GENAP',
        'name' => 'Semester Genap',
        'sequence' => 2,
        'starts_on' => '2027-01-01',
        'ends_on' => '2027-06-30',
        'status' => 'draft',
        'is_active' => false,
    ]);

    $this->actingAs($actor)->patchJson(
        route('api.v1.academic.periods.terms.activate', $ganjil->id),
        [],
        ['Idempotency-Key' => (string) Str::ulid()],
    )
        ->assertOk()
        ->assertJsonPath('message', 'Term akademik berhasil diaktifkan.')
        ->assertJsonPath('data.id', $ganjil->id)
        ->assertJsonPath('data.status', 'active')
        ->assertJsonPath('data.is_active', true);

    $this->assertDatabaseHas('academic_terms', [
        'id' => $ganjil->id,
        'status' => 'active',
        'is_active' => true,
    ]);
    $this->assertDatabaseHas('academic_years', [
        'id' => $year->id,
        'status' => 'active',
    ]);

    $this->actingAs($actor)->patchJson(
        route('api.v1.academic.periods.terms.activate', $genap->id),
        [],
        ['Idempotency-Key' => (string) Str::ulid()],
    )
        ->assertOk()
        ->assertJsonPath('data.id', $genap->id)
        ->assertJsonPath('data.is_active', true);

    $this->assertDatabaseHas('academic_terms', [
        'id' => $ganjil->id,
        'is_active' => false,
    ]);
    $this->assertDatabaseHas('academic_terms', [
        'id' => $genap->id,
        'status' => 'active',
        'is_active' => true,
    ]);

    $this->actingAs($actor)
        ->getJson(route('api.v1.academic.periods.terms.current'))
        ->assertOk()
        ->assertJsonPath('message', 'Term akademik aktif berhasil dibaca.')
        ->assertJsonPath('data.id', $genap->id);

    $this->actingAs($actor)->patchJson(
        route('api.v1.academic.periods.terms.close', $genap->id),
        [],
        ['Idempotency-Key' => (string) Str::ulid()],
    )
        ->assertOk()
        ->assertJsonPath('message', 'Term akademik berhasil ditutup.')
        ->assertJsonPath('data.status', 'closed')
        ->assertJsonPath('data.is_active', false);

    $this->actingAs($actor)
        ->getJson(route('api.v1.academic.periods.terms.current'))
        ->assertOk()
        ->assertJsonPath('data', null);
});

it('menolak closed term sebagai active period dan direct is_active mutation', function (): void {
    $manage = Permission::create(['name' => 'academic_period.manage', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($manage);
    $year = AcademicYearRecord::query()->create([
        'code' => '2026-2027',
        'name' => 'Tahun Akademik 2026/2027',
        'starts_on' => '2026-07-01',
        'ends_on' => '2027-06-30',
        'status' => 'active',
    ]);
    $closed = AcademicTermRecord::query()->create([
        'academic_year_id' => $year->id,
        'code' => '2026-2027-GANJIL',
        'name' => 'Semester Ganjil',
        'sequence' => 1,
        'starts_on' => '2026-07-01',
        'ends_on' => '2026-12-31',
        'status' => 'closed',
        'is_active' => false,
    ]);

    $this->actingAs($actor)->patchJson(
        route('api.v1.academic.periods.terms.activate', $closed->id),
        [],
        ['Idempotency-Key' => (string) Str::ulid()],
    )
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('code', 'ACADEMIC_PERIOD_LIFECYCLE_INVALID')
        ->assertJsonPath('errors.term.0', 'Closed term tidak bisa dijadikan active period.');

    $this->actingAs($actor)->patchJson(route('api.v1.academic.periods.terms.update', $closed->id), [
        'is_active' => true,
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnprocessable()
        ->assertJsonPath('code', 'VALIDATION_ERROR')
        ->assertJsonStructure(['errors' => ['is_active']]);
});

it('mencatat audit mutation academic period dengan metadata aman', function (): void {
    $manage = Permission::create(['name' => 'academic_period.manage', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($manage);
    $yearCreateCorrelationId = (string) Str::ulid();
    $yearUpdateCorrelationId = (string) Str::ulid();
    $termCreateCorrelationId = (string) Str::ulid();
    $termUpdateCorrelationId = (string) Str::ulid();
    $termActivateCorrelationId = (string) Str::ulid();
    $termCloseCorrelationId = (string) Str::ulid();

    $createdYear = $this->actingAs($actor)->postJson(route('api.v1.academic.periods.years.store'), [
        'code' => '2026-2027',
        'name' => 'Tahun Akademik 2026/2027',
        'starts_on' => '2026-07-01',
        'ends_on' => '2027-06-30',
        'status' => 'draft',
    ], [
        'Idempotency-Key' => (string) Str::ulid(),
        'X-Correlation-ID' => $yearCreateCorrelationId,
    ])->assertCreated();

    $yearId = (string) $createdYear->json('data.id');

    $this->actingAs($actor)->patchJson(route('api.v1.academic.periods.years.update', $yearId), [
        'name' => 'TA 2026/2027',
    ], [
        'Idempotency-Key' => (string) Str::ulid(),
        'X-Correlation-ID' => $yearUpdateCorrelationId,
    ])->assertOk();

    $createdTerm = $this->actingAs($actor)->postJson(route('api.v1.academic.periods.terms.store'), [
        'academic_year_id' => $yearId,
        'code' => '2026-2027-GANJIL',
        'name' => 'Semester Ganjil',
        'sequence' => 1,
        'starts_on' => '2026-07-01',
        'ends_on' => '2026-12-31',
        'status' => 'draft',
    ], [
        'Idempotency-Key' => (string) Str::ulid(),
        'X-Correlation-ID' => $termCreateCorrelationId,
    ])->assertCreated();

    $termId = (string) $createdTerm->json('data.id');

    $this->actingAs($actor)->patchJson(route('api.v1.academic.periods.terms.update', $termId), [
        'name' => 'Semester 1',
    ], [
        'Idempotency-Key' => (string) Str::ulid(),
        'X-Correlation-ID' => $termUpdateCorrelationId,
    ])->assertOk();

    $this->actingAs($actor)->patchJson(route('api.v1.academic.periods.terms.activate', $termId), [], [
        'Idempotency-Key' => (string) Str::ulid(),
        'X-Correlation-ID' => $termActivateCorrelationId,
    ])->assertOk();

    $this->actingAs($actor)->patchJson(route('api.v1.academic.periods.terms.close', $termId), [], [
        'Idempotency-Key' => (string) Str::ulid(),
        'X-Correlation-ID' => $termCloseCorrelationId,
    ])->assertOk();

    expect(AuditRecord::query()->where('module', 'AcademicPeriod')->count())->toBe(6);

    $yearCreateAudit = AuditRecord::query()->where('action', 'academic_period.year.created')->firstOrFail();
    $yearUpdateAudit = AuditRecord::query()->where('action', 'academic_period.year.updated')->firstOrFail();
    $termCreateAudit = AuditRecord::query()->where('action', 'academic_period.term.created')->firstOrFail();
    $termUpdateAudit = AuditRecord::query()->where('action', 'academic_period.term.updated')->firstOrFail();
    $termActivateAudit = AuditRecord::query()->where('action', 'academic_period.term.activated')->firstOrFail();
    $termCloseAudit = AuditRecord::query()->where('action', 'academic_period.term.closed')->firstOrFail();

    expect($yearCreateAudit->actor_id)->toBe($actor->id)
        ->and($yearCreateAudit->subject_type)->toBe('academic_year')
        ->and($yearCreateAudit->subject_id)->toBe($yearId)
        ->and($yearCreateAudit->correlation_id)->toBe($yearCreateCorrelationId)
        ->and($yearCreateAudit->metadata)->toMatchArray([
            'changed_fields' => ['code', 'name', 'starts_on', 'ends_on', 'status'],
            'result' => [
                'code' => '2026-2027',
                'name' => 'Tahun Akademik 2026/2027',
                'starts_on' => '2026-07-01',
                'ends_on' => '2027-06-30',
                'status' => 'draft',
            ],
        ])
        ->and($yearUpdateAudit->correlation_id)->toBe($yearUpdateCorrelationId)
        ->and($yearUpdateAudit->metadata)->toMatchArray([
            'changed_fields' => ['name'],
            'to_status' => 'draft',
        ])
        ->and($termCreateAudit->subject_type)->toBe('academic_term')
        ->and($termCreateAudit->subject_id)->toBe($termId)
        ->and($termCreateAudit->correlation_id)->toBe($termCreateCorrelationId)
        ->and($termCreateAudit->metadata)->toMatchArray([
            'changed_fields' => ['academic_year_id', 'code', 'name', 'sequence', 'starts_on', 'ends_on', 'status'],
            'result' => [
                'academic_year_id' => $yearId,
                'code' => '2026-2027-GANJIL',
                'name' => 'Semester Ganjil',
                'sequence' => 1,
                'starts_on' => '2026-07-01',
                'ends_on' => '2026-12-31',
                'status' => 'draft',
                'is_active' => false,
            ],
        ])
        ->and($termUpdateAudit->correlation_id)->toBe($termUpdateCorrelationId)
        ->and($termUpdateAudit->metadata)->toMatchArray([
            'changed_fields' => ['name'],
            'to_status' => 'draft',
        ])
        ->and($termActivateAudit->correlation_id)->toBe($termActivateCorrelationId)
        ->and($termActivateAudit->metadata)->toMatchArray([
            'changed_fields' => ['status', 'is_active'],
            'to_status' => 'active',
        ])
        ->and($termActivateAudit->metadata['result'])->toMatchArray([
            'status' => 'active',
            'is_active' => true,
        ])
        ->and($termCloseAudit->correlation_id)->toBe($termCloseCorrelationId)
        ->and($termCloseAudit->metadata)->toMatchArray([
            'changed_fields' => ['status', 'is_active'],
            'to_status' => 'closed',
        ])
        ->and($termCloseAudit->metadata['result'])->toMatchArray([
            'status' => 'closed',
            'is_active' => false,
        ])
        ->and(array_keys($yearCreateAudit->metadata))->not->toContain('password')
        ->and(array_keys($termActivateAudit->metadata))->not->toContain('token');
});

it('menolak guest actor tanpa permission dan payload invalid academic period', function (): void {
    $this->getJson(route('api.v1.academic.periods.years.index'))
        ->assertUnauthorized()
        ->assertJsonPath('code', 'UNAUTHENTICATED');

    $unauthorized = User::factory()->create();
    $this->actingAs($unauthorized)
        ->getJson(route('api.v1.academic.periods.years.index'))
        ->assertForbidden()
        ->assertJsonPath('code', 'FORBIDDEN');

    $manage = Permission::create(['name' => 'academic_period.manage', 'guard_name' => 'web']);
    $actor = User::factory()->create();
    $actor->givePermissionTo($manage);
    $year = AcademicYearRecord::query()->create([
        'code' => '2026-2027',
        'name' => 'Tahun Akademik 2026/2027',
        'starts_on' => '2026-07-01',
        'ends_on' => '2027-06-30',
        'status' => 'draft',
    ]);

    $this->actingAs($actor)->postJson(route('api.v1.academic.periods.years.store'), [
        'code' => '2026-2027',
        'name' => 'Duplikat',
        'starts_on' => '2027-07-01',
        'ends_on' => '2027-06-30',
        'status' => 'unknown',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnprocessable()
        ->assertJsonPath('success', false)
        ->assertJsonPath('code', 'VALIDATION_ERROR')
        ->assertJsonStructure(['errors' => ['code', 'ends_on', 'status']]);

    $this->actingAs($actor)->postJson(route('api.v1.academic.periods.terms.store'), [
        'academic_year_id' => $year->id,
        'code' => '2026-2027-GANJIL',
        'name' => 'Semester Ganjil',
        'sequence' => 1,
        'starts_on' => '2026-07-01',
        'ends_on' => '2026-12-31',
        'status' => 'draft',
    ], ['Idempotency-Key' => (string) Str::ulid()])->assertCreated();

    $this->actingAs($actor)->postJson(route('api.v1.academic.periods.terms.store'), [
        'academic_year_id' => $year->id,
        'code' => '2026-2027-GANJIL',
        'name' => 'Duplikat',
        'sequence' => 1,
        'starts_on' => '2026-12-31',
        'ends_on' => '2026-07-01',
        'status' => 'bad',
    ], ['Idempotency-Key' => (string) Str::ulid()])
        ->assertUnprocessable()
        ->assertJsonPath('code', 'VALIDATION_ERROR')
        ->assertJsonStructure(['errors' => ['code', 'sequence', 'ends_on', 'status']]);

    $this->actingAs($actor)->patchJson(
        route('api.v1.academic.periods.years.update', (string) Str::ulid()),
        ['name' => 'Tidak Ada'],
        ['Idempotency-Key' => (string) Str::ulid()],
    )->assertNotFound()->assertJsonPath('code', 'RESOURCE_NOT_FOUND');
});
