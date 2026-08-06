# Specification: System/AuditLog

## Status

`Implemented` pada 6 Agustus 2026. Seluruh acceptance criteria scope awal telah
diverifikasi.

## Objective

Membuat AuditLog sebagai owner tunggal histori aktivitas keamanan dan bisnis
penting. Record harus append-only, ter-redaksi, memiliki identitas korelasi,
dapat dibaca berdasarkan scope server, dan disimpan minimum satu tahun.

## Parent Boundary dan Ownership

- Parent boundary: `System`.
- Code path: `app/Modules/System/AuditLog`.
- Namespace: `App\Modules\System\AuditLog`.
- Data owner: tabel `audit_logs`.
- Permission owner: `AuditLog/permissions.php`.
- Public API owner: `AuditRecorder` dan DTO audit yang aman.
- Presentation owner: route, policy, controller, resource, dan frontend
  `resources/js/pages/System/AuditLog`.

## Preflight Traceability

| Item | Hasil |
| --- | --- |
| Authoritative source | `01.01`, `01.05`, `02.01` sampai `02.05`, `03.01` sampai `03.12`, ADR-0001, ADR-0003, `07.05-AUDIT.md` |
| Downstream docs | Database design, security design, UserManagement event status, changelog, README module, tasks, dan execution log |
| Existing code | AccessControl dan UserManagement valid; event impersonation sudah ada tetapi belum menjadi integration event versioned |
| Golden structure | `app/Modules/System/{Module}` sesuai `03.04-FOLDER-STRUCTURE.md` dan profile `default-v1` |
| Dependency | AccessControl untuk authorization; public event AccessControl dan UserManagement untuk ingestion |
| Acceptance | Append-only, redaction, idempotency, correlation, scope, retensi, frontend, dan test lulus |
| Rollback trace | Folder module, provider registration, route/menu, migration `audit_logs`, event producer, dan dokumen ini dapat ditelusuri per task |

Hasil preflight command:

- `module:discover`, `module:validate`, dan `module:list` menemukan dua module
  valid: AccessControl dan UserManagement;
- `module:inspect System/AccessControl` dan
  `module:inspect System/UserManagement` berhasil;
- `module:inspect System/AuditLog` menghasilkan `MODULE_NOT_FOUND`. Hasil ini
  diharapkan karena target belum dibuat dan membuktikan tidak ada duplicate
  module identity.

## Scope Saat Ini

- skeleton module melalui generator resmi;
- permission `audit_log.view`;
- public contract `AuditRecorder` dengan input DTO typed;
- audit record append-only dengan id ULID;
- `event_id` ULID unik untuk mencegah pencatatan event yang sama dua kali;
- `correlation_id` ULID untuk menghubungkan request, event, dan audit;
- redaksi metadata dengan allowlist, denylist key sensitif, batas kedalaman,
  dan batas panjang nilai;
- synchronous integration event consumer untuk mutasi AccessControl dan
  UserManagement;
- query list/detail dengan filter dan page pagination;
- server-side authorization dan scope actor;
- Inertia page, Ziggy, sidebar, command palette, detail dialog, responsive,
  loading, empty, error, dan accessibility;
- module seeder development yang aman dan idempotent;
- migration module-local serta global `DatabaseSeeder` entry point.

## Non-Scope

- automatic purge atau archive;
- partition tabel;
- export CSV/PDF;
- delegated project/tenant/module scope karena model scope tersebut belum ada;
- queue, retry worker, dead-letter queue, atau event sourcing;
- penyimpanan request body, password, token, credential, cookie, session ID,
  atau secret;
- perubahan atau penghapusan audit dari UI/API.

## Data Contract

Tabel `audit_logs`:

| Field | Contract |
| --- | --- |
| `id` | ULID primary key |
| `event_id` | ULID unique; idempotency key integration event |
| `actor_id` | nullable ULID, FK `users.id`, `nullOnDelete` |
| `action` | string dan index |
| `subject_type` | string |
| `subject_id` | nullable ULID dan index |
| `module` | string dan index |
| `project_id` | nullable ULID dan index untuk scope masa depan |
| `tenant_id` | nullable ULID dan index untuk scope masa depan |
| `correlation_id` | ULID dan index |
| `reason` | nullable text yang sudah disanitasi |
| `metadata` | JSON hasil filter/redaksi |
| `created_at` | timestamp event; tidak ada `updated_at` dan `deleted_at` |

`actor_id` dapat menjadi `null` untuk system process atau setelah hard delete
actor. Histori tidak boleh cascade delete.

### Metadata yang diizinkan

Allowlist awal:

- `role_name`;
- `permission_keys`;
- `permission_count`;
- `changed_fields`;
- `from_status`;
- `to_status`;
- `result`.

Key yang mengandung `password`, `token`, `secret`, `credential`,
`authorization`, `cookie`, `session`, atau `api_key` selalu menjadi
`[REDACTED]`, walaupun salah satu key tersebut nanti masuk allowlist. Key lain
di luar allowlist dibuang.

## Public Contract

```php
interface AuditRecorder
{
    public function record(AuditEntryData $entry): AuditRecordData;
}
```

Contract tidak mengembalikan Eloquent model. `AuditEntryData` wajib membawa
`eventId`, `actorId`, `action`, `subjectType`, `subjectId`, `module`,
`correlationId`, `reason`, `metadata`, dan `occurredAt`.

Pencatatan event yang memiliki `event_id` sama bersifat idempotent: consumer
mengembalikan record yang sudah ada tanpa membuat row kedua.

## Integration Event Contract

Producer menyediakan event public versioned:

- `AccessControlActivityOccurred`;
- `UserManagementActivityOccurred`.

Minimum envelope:

```text
event_name, version, event_id, occurred_at, correlation_id,
actor_id, action, subject_type, subject_id, reason, metadata
```

AuditLog mendaftarkan listener synchronous. Kegagalan audit pada mutasi sensitif
dipropagasikan agar request tidak dilaporkan berhasil tanpa evidence audit.
Duplicate delivery aman karena unique `event_id`.

## Authorization dan Visibility Scope

- route memakai middleware `auth`, `verified`, dan `can:audit_log.view`;
- policy AuditLog tetap menjadi server-side authority;
- `SuperSystem` dapat membaca seluruh record melalui guard global yang sudah ada;
- actor biasa dengan `audit_log.view` hanya melihat record dengan
  `actor_id` miliknya;
- record di luar scope tidak dikembalikan dan detail merespons `404`;
- frontend permission hanya mengatur visibility/UX.

Scope project, tenant, dan delegated module sengaja ditunda sampai owner model
scope tersedia. Default actor-own scope dipilih karena paling aman dan dapat
diuji tanpa mengarang model organisasi.

## Retention dan Append-only

- konfigurasi awal `retention_days` adalah 365;
- tidak ada penghapusan otomatis pada increment ini;
- update/delete melalui model dan repository aplikasi ditolak;
- tidak ada route mutation untuk audit record;
- purge/archive memerlukan ADR, authorization, backup, operational audit, dan
  rehearsal tersendiri;
- evaluasi partition/archive dilakukan saat threshold pada database design
  terpenuhi.

## Route dan API Design

```text
GET /system/audit-logs
GET /system/audit-logs/{auditLog}
GET /api/v1/audit-logs
GET /api/v1/audit-logs/{auditLog}
```

Nama route Inertia:

```text
system.audit-logs.index
system.audit-logs.show
```

Filter awal: `search`, `module`, `action`, `date_from`, `date_to`, `page`, dan
`per_page`. `per_page` dibatasi 10, 25, 50, atau 100 dengan default 25.

## Frontend Contract

- memakai `SystemDashboardLayout`;
- memakai token `dashboard-card`, `dashboard-subcard`, `dashboard-icon`, dan
  `dashboard-badge`;
- light memakai card putih bersih dan subcard abu-abu netral;
- dark memakai surface palette aktif dan subcard sedikit lebih gelap;
- icon memakai warna semantic, bukan surface hardcode;
- detail dibuka melalui `Dialog`;
- shortcut `/` fokus ke pencarian dan `Esc` menutup dialog;
- tabel memiliki state loading, empty, error, pagination, dan mobile fallback;
- menu sidebar serta command palette hanya muncul jika actor berhak.

## Fondasi Enterprise AuditLog

| Fondasi | Status | Owner dan alasan | Acceptance/verification |
| --- | --- | --- | --- |
| Contract/Interface | `implemented` pada task contract | AuditLog memiliki `AuditRecorder` dan repository/read contract typed | Contract test dan PHPStan |
| Domain Event | `not applicable` untuk scope awal | AuditLog menyimpan fakta dari producer; tidak ada state domain lain yang perlu dipancarkan | Review event inventory |
| Application Event | `not applicable` | Satu handler synchronous cukup; tidak ada koordinasi multi-handler internal | Architecture review |
| Integration Event | `implemented` | Producer AccessControl/UserManagement memiliki event versioned; AuditLog consumer nyata | Consumer, redaction, duplicate-event test |
| Command | `implemented` sebagai Application Action | `RecordAuditEntry` adalah mutation synchronous CQRS-lite; Command Bus belum diperlukan | Unit/integration test |
| Query/Read Contract | `implemented` | `ListAuditLogs` dan `GetAuditLog` mengembalikan DTO/paginator typed tanpa side effect | Query, scope, pagination test |
| Shared Kernel | `not applicable` | Belum ada value object bisnis stabil dengan dua owner; package framework bukan Shared Kernel | Dependency scan |
| Facade/Module API | `implemented` melalui contract | `AuditRecorder` adalah public Module API; Facade Laravel tidak dibuat | Container binding dan contract test |
| Queue/Job | `not applicable` pada scope awal | Audit keamanan perlu fail-closed synchronous; queue memerlukan retry/dead-letter policy terpisah | Pastikan listener tidak queued |

Runtime tetap CQRS-lite. Perubahan ke queue, Command Bus, projection, atau CQRS
penuh memerlukan increment dan ADR baru.

## Acceptance Criteria

- module dapat dibuat tanpa duplicate identity;
- discovery, validation, list, dan inspect lulus;
- migration memakai ULID, index, FK `nullOnDelete`, dan tidak membuat timestamp
  mutation;
- record tidak dapat di-update atau di-delete melalui application model;
- metadata tidak menyimpan key/value sensitif dan unknown key dibuang;
- duplicate `event_id` tidak membuat duplicate row;
- correlation ID valid dan tetap sama dari event ke record;
- mutasi AccessControl dan UserManagement menghasilkan audit;
- auditor biasa hanya melihat audit miliknya;
- `SuperSystem` dapat melihat seluruh audit;
- detail di luar scope merespons `404`;
- frontend dapat dibuka, difilter, dipaginasi, dan detail dialog accessible;
- global migration/seeder, focused test, browser test, dan quality gate lulus.

## Generator Contract

- Prompt: gunakan prompt resmi pada implementation plan.
- Dry-run:
  `php artisan module:make AuditLog --domain=System --profile=default-v1 --dry-run --json`.
- Expected dry-run: `MODULE_PREVIEWED`, planned path benar, filesystem tidak
  berubah.
- Actual:
  `php artisan module:make AuditLog --domain=System --profile=default-v1 --force --yes --json`.
- Expected actual: `MODULE_CREATED`, manifest, provider, runtime config,
  permission source, README, route entry point, dan structure canonical valid.

## Boundaries

- Always: ULID, typed DTO, append-only, redaction allowlist, server-side scope,
  Ziggy, dan UI baseline System.
- Ask first: purge/archive, delegated scope, export, queue, dan perubahan
  retention di bawah 365 hari.
- Never: update/delete audit biasa, menyimpan secret, memakai frontend sebagai
  security boundary, atau mengimpor private implementation module lain.

## Revision History

| Versi | Tanggal | Perubahan |
| --- | --- | --- |
| 1.0 | 2026-08-06 | Menetapkan contract data, keamanan, integration event, UI, dan fondasi enterprise |
| 1.1 | 2026-08-06 | Menandai specification terimplementasi setelah browser test dan full CI lulus |
