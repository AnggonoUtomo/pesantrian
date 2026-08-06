# Implementation Plan: System/SystemSetting

## Status

`Selesai; seluruh increment dan final quality gate lulus`.

ADR-0001 dan ADR-0002 berstatus `Accepted` pada 6 Agustus 2026.

## Architecture

### Read Flow

```text
Module consumer atau Presentation Query
    -> SystemSettingReader
    -> SettingDefinitionRegistry
    -> request memoization
    -> SystemSettingRepository
    -> system_settings
    -> SettingValueData typed
       atau safe default + diagnostic jika storage/record invalid
```

### Mutation Flow

```text
HTTP/API/Console input
    -> FormRequest atau typed command input
    -> middleware dan SystemSettingPolicy
    -> UpdateSystemSetting Action
    -> re-check SuperSystem melalui AuthorizationCapability
    -> registry validation + reason validation
    -> database transaction
       -> repository upsert
       -> AuditRecorder menyimpan before/after
    -> request memoization diperbarui
    -> Inertia/JSON/console response
```

Controller tidak boleh berisi query Eloquent, schema validation, persistence
mutation, atau aturan `SuperSystem`.

## Authoritative Source

Dokumen yang dibaca sebelum plan dibuat:

- root `AGENTS.md`, `docs/AGENTS.md`, `docs/README.md`, dan
  `docs/AI-PROMPT-GUIDE.md`;
- `01.01-FUNCTIONAL-REQUIREMENTS.md` dan
  `01.05-BASELINE-SPECIFICATION.md`;
- database, security, system, dan UI/UX design;
- folder structure, generator specification, module contract, test plan,
  baseline task plan, serta module communication;
- generator engine, stub engine, console contract, dan kernel SystemSetting;
- template project serta implementasi AccessControl, UserManagement, dan
  AuditLog yang menjadi dependency.

## Preflight dan Generator

### Project Intake dan Existing Inventory

Command read-only yang sudah dijalankan:

```bash
php artisan about --only=environment,cache,drivers
php artisan module:discover --json
php artisan module:validate --json
php artisan module:list --json
php artisan module:inspect System/AccessControl --json
php artisan module:inspect System/UserManagement --json
php artisan module:inspect System/AuditLog --json
php artisan module:inspect System/SystemSetting --json
```

Hasil penting:

- AccessControl, UserManagement, dan AuditLog valid;
- dependency module existing sesuai manifest;
- pada preflight awal target `System/SystemSetting` belum ada;
- MySQL aktif dan runtime lokal memakai database driver untuk cache, queue,
  serta session.

### Prompt Resmi

```text
Lakukan Project Intake dan Existing Module Inventory terlebih dahulu.
Verifikasi AccessControl, UserManagement, dan AuditLog dengan module:discover,
module:validate, module:list, serta module:inspect. Buat module SystemSetting
pada domain System memakai profile default-v1. Jalankan dry-run JSON lebih
dahulu. Pastikan target app/Modules/System/SystemSetting, namespace
App\Modules\System\SystemSetting, dan canonical structure benar. Jangan menulis
file sebelum dry-run, ADR, dan checklist ditinjau.
```

### Dry-run

```bash
php artisan module:make SystemSetting --domain=System --profile=default-v1 --dry-run --json
```

Hasil aktual preflight:

- code: `MODULE_PREVIEWED`;
- target: `app/Modules/System/SystemSetting`;
- profile: `default-v1`;
- diagnostic: kosong;
- filesystem module: tidak berubah.

### Actual Generator

Command aktual yang telah dijalankan:

```bash
php artisan module:make SystemSetting --domain=System --profile=default-v1 --force --yes --json
```

Hasil aktual:

- code `MODULE_CREATED`;
- manifest, runtime config, permission source, provider, README, dan empat route
  entry point tersedia;
- directory DDD-lite canonical terbentuk;
- module existing tidak tertimpa;
- business logic belum dianggap selesai hanya karena skeleton tersedia.

## Urutan Implementasi

### Increment 01 — Dokumentasi dan Keputusan

- Selesaikan specification, plan, tasks, execution log, dan ADR.
- Tinjau dependency, schema, public contract, cache, appearance, asset path,
  authorization, dan audit.
- Minta persetujuan ADR sebelum coding.

Checkpoint: tautan valid, checklist detail, keputusan terbuka terlihat, dan
`git diff --check` lulus.

### Increment 02 — Generator dan Runtime Identity

- Jalankan generator aktual.
- Isi dependency manifest `AccessControl` dan `AuditLog`.
- Tetapkan permission identity, config source, provider registration, dan README.
- Verifikasi discovery, validate, list, dan inspect.

Checkpoint: positive structure test dan negative duplicate/conflict test lulus.

### Increment 03 — Definition Registry dan Public Contract

- Buat value object/type untuk key, type, definition, dan value.
- Buat `SystemSettingReader` serta `SettingDefinitionRegistrar`.
- Buat registry dengan duplicate detection dan baseline definition.
- Mulai dari test RED untuk unknown key, duplicate key, type, range, dan default.

Checkpoint: unit/contract test lulus tanpa database.

### Increment 04 — Persistence MySQL dan Safe Default

- Buat migration `system_settings` dan `idempotency_keys`.
- Buat Eloquent model dan repository adapter.
- Buat reader dengan request memoization, safe default, dan diagnostic sanitized.
- Uji fresh migration, rollback, relation, unique/index, invalid record, dan
  storage failure.

Checkpoint: persistence test MySQL dan negative failure test lulus.

### Increment 05 — Mutation, Authorization, dan Audit

- Buat `UpdateSystemSetting` Action.
- Re-check role `SuperSystem` melalui `AuthorizationCapability`.
- Wajibkan reason dan correlation ID ULID.
- Simpan setting dan audit dalam satu transaction.
- Tambah allowlist AuditLog untuk metadata SystemSetting.
- Uji rollback saat AuditRecorder gagal.

Checkpoint: non-SuperSystem, invalid value, missing reason, redaction, dan
fail-closed test lulus.

### Increment 06 — Seeder Global dan Command

- Buat seeder baseline idempotent pada owner module.
- Tambahkan SystemSettingSeeder ke `DatabaseSeeder` setelah AuditLog.
- Buat command list/get/set/validate.
- Command set mewajibkan actor SuperSystem dan reason.

Checkpoint: `migrate:fresh --seed`, focused seeder, idempotency, dan command
positive/negative test lulus.

### Increment 07 — Presentation Web/API dan Frontend

- Buat FormRequest, policy, thin controller, resource, dan routes.
- Buat page/component/types/schema di owner frontend folder.
- Tambahkan menu sidebar dan command palette dengan visibility SuperSystem.
- Gunakan Ziggy, modal edit, Sonner, shortcut, state lengkap, responsive, dan
  baseline theme module.

Checkpoint: feature/API test, typecheck, build, browser, keyboard, console, dan
accessibility review lulus.

### Increment 08 — Runtime Consumer

- Rate limiter membaca `api.rate_limit.per_minute`.
- Idempotency middleware/service memakai retention dan payload hash wajib.
- Session middleware menerapkan idle serta absolute lifetime.
- Shared props/bootstrap membaca branding default.
- Preference appearance/palette user tetap menjadi override.
- Operational diagnostic menampilkan RTO/RPO aktif secara aman.

Checkpoint: setiap consumer memiliki test default, custom value, invalid
storage, dan no-private-dependency.

### Increment 09 — Final Quality Gate

- Review correctness, readability, architecture, security, performance,
  migration, dependency, dan frontend.
- Jalankan test fokus lalu `composer ci:check`.
- Jalankan module discovery/validation/inspect.
- Uji browser desktop/mobile, light/dark, palette, shortcut, dan unauthorized.
- Perbarui checklist, execution log, README, ADR, dan open risk.

Checkpoint: Definition of Done tercentang hanya berdasarkan evidence nyata.

## Struktur File yang Direncanakan

```text
app/Modules/System/SystemSetting/
|-- Application/
|   |-- Actions/UpdateSystemSetting.php
|   |-- Contracts/
|   |   |-- SettingDefinitionRegistrar.php
|   |   |-- SystemSettingReader.php
|   |   `-- SystemSettingRepository.php
|   |-- DTO/
|   |-- Queries/
|   `-- Services/
|-- Domain/
|   |-- Exceptions/
|   `-- ValueObjects/
|-- Infrastructure/
|   |-- Persistence/Models/
|   |-- Persistence/Repositories/
|   `-- Providers/
|-- Presentation/
|   |-- Console/Commands/
|   |-- Controllers/
|   |-- Policies/
|   |-- Requests/
|   `-- Resources/
|-- Database/
|   |-- Migrations/
|   `-- Seeders/
|-- Routes/
|-- module.json
|-- module.php
|-- permissions.php
|-- ServiceProvider.php
`-- README.md

resources/js/pages/System/SystemSetting/
|-- components/
|-- pages/Index.tsx
|-- schemas.ts
`-- types.ts
```

Nama class final dapat disederhanakan selama responsibility dan boundary tetap
sesuai specification. Perubahan public contract memerlukan pembaruan dokumen
lebih dahulu.

## Test Strategy

| Level | Fokus |
| --- | --- |
| Unit | Key/type/value object, registry, validation, safe default |
| Contract | Reader, registrar, DTO, no private model leak |
| Feature | Action, policy, controller, command, API, audit failure |
| Integration | MySQL migration/repository, idempotency, session, rate limit |
| Architecture | Dependency manifest dan forbidden concrete import |
| Frontend | Type, schema, state, keyboard, Ziggy, authorization visibility |
| Browser | List/edit, invalid input, success/error toast, mobile, light/dark |
| Static/CI | Pint, PHPStan, ESLint, Prettier, TypeScript, Vite, full test |

## Risiko

| Risiko | Dampak | Mitigasi |
| --- | --- | --- |
| Cache mengembalikan nilai lama | Setting tidak langsung aktif | DB source of truth dan request memoization pada increment awal |
| Non-SuperSystem memperoleh permission | Akses privileged bocor | Policy dan Action mewajibkan role SuperSystem, bukan permission saja |
| Audit gagal setelah setting berubah | Perubahan tanpa evidence | Setting dan AuditRecorder berada dalam transaction yang sama |
| Nilai invalid tersimpan | Runtime tidak aman | Registry code, FormRequest, Action validation, DB contract, safe default |
| Secret masuk setting/audit | Kebocoran data | Secret dilarang, key allowlist, metadata redaction, security test |
| Session baru memutus user aktif | Gangguan akses | Range aman, reason, confirmation UI, test current/next request |
| Branding global menimpa user preference | UX user hilang | Global hanya default; cookie/localStorage user tetap override |
| Asset path disalahgunakan | Traversal/XSS/external load | Hanya path lokal tervalidasi, upload dan protocol eksternal ditolak |
| Idempotency response menyimpan payload sensitif | Kebocoran replay data | Sanitized response contract dan sensitive endpoint review |
| Migration shared/production | Risiko schema/data nyata | Fresh/upgrade test lokal; backup dan rehearsal production tetap terpisah |

## Rollback

- Sebelum migration dipakai bersama, rollback dapat dilakukan dengan melepas
  provider, route, frontend menu, dan module folder pada commit increment.
- Migration module menyediakan `down()` untuk tabel baru saat belum ada data
  production yang harus dipertahankan.
- Setelah setting dipakai runtime, rollback code harus mempertahankan default
  config yang sama agar aplikasi tidak kehilangan nilai aman.
- Tabel `system_settings` tidak boleh dihapus di production tanpa backup,
  dependency inventory, downtime assessment, dan rehearsal.
- Rollback runtime integration dilakukan consumer per consumer; public reader
  dapat tetap ada selama masa transisi.

## Risiko Tersisa dan Scope Lanjutan

Tidak ada OPEN RISK untuk scope workspace SystemSetting saat ini. Batasan yang
tetap dicatat sebagai pekerjaan terpisah adalah:

- upload logo/favicon memerlukan owner media, storage policy, threat model, dan
  ADR baru;
- cache lintas request baru boleh dipertimbangkan setelah ada pengukuran traffic
  serta strategi invalidation multi-worker;
- migration shared/production memerlukan database deployment nyata, backup,
  rehearsal, dan persetujuan operasi. Hal ini tidak dapat dibuktikan dengan
  database lokal workspace.

## Revision History

| Versi | Tanggal | Perubahan |
| --- | --- | --- |
| 1.0 | 2026-08-06 | Menambahkan rencana incremental SystemSetting berdasarkan preflight |
| 1.1 | 2026-08-06 | Mencatat seluruh increment selesai dan memisahkan scope lanjutan |
