# Specification: System/SystemSetting

## Objective

Membuat module `System/SystemSetting` untuk mengelola konfigurasi runtime global
yang tervalidasi. Setting dapat dibaca melalui public contract, memakai safe
default saat storage bermasalah, langsung aktif tanpa deploy ulang, hanya dapat
diubah `SuperSystem`, dan setiap mutation wajib memiliki jejak AuditLog.

## Project Intake

| Item | Kondisi saat ini |
| --- | --- |
| Mode project | `module extension` pada existing starter kit |
| Backend | PHP 8.4.16, Laravel 13.23.0, Inertia Laravel 3 |
| Database | MySQL, satu database dengan ownership tabel per module |
| Runtime driver lokal | Cache, queue, dan session memakai database driver |
| Frontend | React 19, TypeScript, Vite, Tailwind CSS, shadcn/ui, Ziggy |
| Existing module | AccessControl, UserManagement, dan AuditLog valid |
| Target module | Valid dan aktif pada `app/Modules/System/SystemSetting` |
| Generator | Profile `default-v1`; dry-run `MODULE_PREVIEWED`, aktual `MODULE_CREATED` |

## Preflight Baseline dan Traceability

| Item | Acuan dan hasil |
| --- | --- |
| Authoritative source | Functional requirements, database/security/UI design, module contract, generator contract, test plan, dan kernel SystemSetting |
| Downstream docs | Folder ini, baseline task plan, ADR komunikasi module, AuditLog metadata allowlist, module README, dan execution log |
| Existing code | `AuthorizationCapability`, `AuditRecorder`, `HandleInertiaRequests`, appearance hooks, theme palette, Fortify rate limiter, session config, dan global `DatabaseSeeder` |
| Golden structure | `app/Modules/System/SystemSetting` mengikuti profile `default-v1` dan struktur DDD-lite canonical |
| Dependency | AccessControl untuk authorization dan AuditLog untuk audit mutation |
| Acceptance | Schema valid, safe default, SuperSystem-only mutation, audit fail-closed, runtime activation, UI, test, dan browser verification |
| Rollback trace | Perubahan dapat ditelusuri melalui folder project, migration module, provider registration, frontend owner folder, dan commit increment |

## Parent Boundary dan Ownership

- Parent boundary: `System`.
- Code path: `app/Modules/System/SystemSetting`.
- Namespace: `App\Modules\System\SystemSetting`.
- Data owner: `system_settings` dan `idempotency_keys`.
- Permission owner: `system_setting.view` dan `system_setting.manage`.
- Public Module API: `SystemSettingReader` dan `SettingDefinitionRegistrar`.
- Mutation owner: Application Action internal SystemSetting.
- Dependency langsung: `AccessControl`, `AuditLog`.

Walaupun permission identity tersedia, policy tetap mewajibkan actor memiliki
role `SuperSystem`. Permission yang salah diberikan kepada role lain tidak boleh
membuka halaman atau mutation SystemSetting.

## Scope Saat Ini

- Skeleton module dibuat melalui generator resmi.
- Registry definisi setting typed dan versioned.
- Persistence MySQL dengan ULID, unique key, type, description, exposure flag,
  actor pembaru, dan timestamp.
- Public reader dengan safe default dan diagnostic aman.
- Mutation kompatibel satu setting per request serta mutation kategori atomik
  untuk beberapa key dengan satu reason global wajib.
- Authorization `SuperSystem` pada middleware, policy, dan Application Action.
- Audit before/after melalui public `AuditRecorder` secara synchronous.
- Baseline key untuk rate limit, idempotency, session, branding, monitoring,
  RTO, dan RPO.
- Seeder module idempotent dan dipanggil global melalui `DatabaseSeeder`.
- Command list, get, set, dan validate.
- Page Inertia pada area System memakai `system-dashboard-layout`.
- Route frontend memakai Ziggy.
- Runtime integration untuk rate limit, idempotency, session, dan default
  branding/appearance.
- Positive, negative, architecture, migration, cache/storage failure, API,
  frontend, browser, dan accessibility test.

## Non-scope

- Menyimpan secret arbitrer di luar definition registry. `mail.username` dan
  `mail.password` yang terdaftar merupakan pengecualian terbatas: nilainya
  dienkripsi saat disimpan, dimasking pada read model, dan diredaaksi di audit.
- Mengganti halaman appearance pribadi starter kit.
- Approval workflow dua tahap untuk perubahan setting.
- Command Bus, queue, event sourcing, projection, atau read database terpisah.
- Upload logo/favicon dan instalasi Spatie Media Library pada increment awal.
- Integrasi provider monitoring eksternal tertentu.
- Backup, restore, dan migration rehearsal database production nyata.
- Multi-tenant atau project-specific setting.

## Existing Capability Contract

- `AuthorizationCapability` dari AccessControl dipakai untuk pemeriksaan
  `SuperSystem`. SystemSetting tidak membuat authorization implementation baru.
- `AuditRecorder` dan `AuditEntryData` dari AuditLog dipakai sebagai public
  Module API. SystemSetting tidak mengakses model/repository AuditLog.
- Model `App\Models\User` tetap menjadi identity foundation untuk `updated_by`.
- `system-dashboard-layout`, token `resources/css/app.css`, Sonner, dan Ziggy
  menjadi baseline frontend.
- `useAppearance` dan `useThemePalette` saat ini menyimpan pilihan pribadi di
  cookie/localStorage. Behavior tersebut dipertahankan sebagai user override.
- Rate limiter `system-api` dan middleware session membaca public runtime
  contract SystemSetting; safe default registry tetap tersedia saat storage
  gagal.

## Data Contract `system_settings`

| Column | Rule |
| --- | --- |
| `id` | ULID primary key |
| `key` | String unik dan memakai dot notation |
| `value` | JSON typed value |
| `type` | Enum schema type: integer, boolean, string, enum, atau path |
| `description` | Tujuan setting dalam Bahasa Indonesia sederhana |
| `is_sensitive` | Exposure control; baseline selalu `false` karena secret dilarang |
| `updated_by` | Nullable foreign ULID ke `users`, `nullOnDelete` |
| `created_at`, `updated_at` | Timestamp |

`key` tidak dapat diubah melalui UI. Type dan schema berasal dari registry code,
bukan dari input user. Record yang type/key-nya tidak sesuai registry dianggap
invalid dan reader memakai default aman.

## Data Contract `idempotency_keys`

| Column | Rule |
| --- | --- |
| `id` | ULID primary key |
| `actor_id` | Foreign ULID/index ke `users` |
| `key` | Client idempotency key |
| `endpoint` | Endpoint yang sudah dinormalisasi |
| `payload_hash` | Hash payload request; tidak dapat dimatikan melalui setting |
| `response_status` | HTTP status hasil pertama |
| `response_body` | JSON response yang sudah disanitasi |
| `expires_at` | Timestamp/index, default 24 jam |
| `created_at` | Timestamp |

Unique constraint: `actor_id + endpoint + key`. Key yang sama dengan payload
berbeda menghasilkan conflict. Payload sensitif tidak disimpan.

## Baseline Setting Catalog

| Key | Type | Default | Validasi utama | Owner behavior |
| --- | --- | --- | --- | --- |
| `api.rate_limit.per_minute` | integer | `60` | 1 sampai 1000 | Rate limit actor/endpoint |
| `api.idempotency.retention_hours` | integer | `24` | 1 sampai 168 | Masa replay response idempotent |
| `security.session.idle_minutes` | integer | `30` | 5 sampai 1440 | Idle session timeout |
| `security.session.absolute_hours` | integer | `12` | 1 sampai 168 dan lebih besar dari idle | Absolute session lifetime |
| `branding.app_name` | string | nilai `config('app.name')` | 1 sampai 80 karakter | Nama aplikasi global |
| `branding.logo_path` | path/null | `null` | Path lokal relatif dan protocol dilarang | Logo default |
| `branding.favicon_path` | path | `/favicon.ico` | Path lokal relatif dan protocol dilarang | Favicon default |
| `branding.palette_default` | enum | `neutral` | Harus ada pada `themePalettes` | Palette global awal |
| `branding.typography_default` | enum | `system` | `system`, `sans`, `serif`, `mono` | Typography global awal |
| `branding.appearance_default` | enum | `system` | `system`, `light`, `dark` | Mode warna global awal |
| `monitoring.external_enabled` | boolean | `false` | Boolean | Flag integrasi monitoring opsional |
| `operations.rto_hours` | integer | `4` | 1 sampai 24 | Target recovery time |
| `operations.rpo_hours` | integer | `24` | 1 sampai 168 | Target recovery point |

Default code registry tetap tersedia saat tabel belum dibuat, database gagal,
atau record invalid. Fallback tidak boleh menggunakan nilai input terakhir yang
gagal divalidasi.

## Public Contract

### `SystemSettingReader`

- Membaca key yang terdaftar.
- Mengembalikan typed `SettingValueData`, bukan Eloquent model.
- Menyertakan source `database` atau `default` untuk diagnostic internal.
- Tidak memiliki side effect.
- Menolak key yang tidak terdaftar dengan exception typed.

### `SettingDefinitionRegistrar`

- Module consumer dapat mendaftarkan definisi key miliknya saat boot.
- Definition memuat key, type, default, validator, description, exposure, dan
  owner module.
- Duplicate key dengan contract berbeda menggagalkan validation.
- Registry tidak menerima closure atau payload yang tidak deterministik pada
  output diagnostic.

Writer tidak diekspos sebagai public cross-module contract pada increment awal.
Mutation hanya melalui Action SystemSetting yang dilindungi authorization,
reason, transaction, dan audit.

## Fondasi Enterprise

| Fondasi | Status rencana | Alasan dan verification |
| --- | --- | --- |
| Contract/Interface | `implemented` | Reader, registrar, dan repository contract memiliki consumer nyata serta contract test |
| Domain Event | `not applicable` | Mutation setting belum memiliki rule domain dengan consumer internal terpisah |
| Application Event | `not applicable` | Aktivasi dipanggil eksplisit setelah mutation; tidak ada banyak handler internal |
| Integration Event | `not applicable` | Audit memakai public synchronous `AuditRecorder`; belum ada consumer eksternal |
| Command | `implemented as Action` | `UpdateSystemSetting` menjadi command-like use case tanpa Command Bus |
| Query/Read Contract | `implemented` | Query list dan public typed reader tidak memiliki side effect |
| Shared Kernel | `not applicable` | Tidak ada value object bisnis stabil yang dipakai dua module |
| Facade/Module API | `implemented via contract` | Public API memakai contract; Facade tidak dibuat |
| Queue/Job | `not applicable` | Aktivasi dan audit harus langsung serta fail-closed |

Pola runtime tetap CQRS-lite: Application Action untuk write dan Application
Query/typed reader untuk read.

## Route dan API Design

```text
GET   /system/system-settings
PATCH /system/system-settings/categories/{category}
PATCH /system/system-settings/{key}

GET   /api/v1/system-settings
PATCH /api/v1/system-settings/{key}
```

Nama route Ziggy:

- `system.system-settings.index`;
- `system.system-settings.category.update`;
- `system.system-settings.update`.

Route kategori hanya menerima `api`, `password`, `session`, `mail`,
`pagination`, `branding`, `monitoring`, atau `operations`. Payload `updates`
berbentuk daftar `{ key, value }`, minimal satu item, tanpa key duplikat. Server
memastikan setiap key benar milik kategori route, menormalisasi semua value,
memeriksa konsistensi gabungan, lalu menyimpan seluruh item dan auditnya di satu
transaction. Satu `reason` dan `correlation_id` dipakai untuk semua audit dalam
batch. Endpoint satu key/API tetap dipertahankan untuk kompatibilitas.

Command console:

```bash
php artisan system-setting:list
php artisan system-setting:get {key}
php artisan system-setting:set {key} {value} --actor={user-ulid} --reason="..."
php artisan system-setting:validate
```

`system-setting:set` wajib memvalidasi bahwa `--actor` adalah user
`SuperSystem`. Mutation tanpa actor atau reason ditolak. Output tidak boleh
menampilkan nilai sensitif atau stack trace.

## Authorization dan Audit

```text
Authentication
    -> Controller middleware
    -> SystemSettingPolicy mewajibkan SuperSystem
    -> AccessControl AuthorizationCapability
    -> UpdateSystemSetting Action melakukan re-check
    -> validasi registry dan value
    -> transaction: simpan setting + AuditRecorder
    -> response sukses
```

Audit menyimpan actor, key, kategori, label operator, before, after, reason,
result, timestamp, event ID, dan correlation ID. AuditLog metadata allowlist
harus ditambah secara eksplisit untuk `setting_key`, `setting_category`,
`setting_label`, `before_value`, dan `after_value`. Halaman operator hanya
menerima kategori, label, dan nilai before/after yang sudah diformat; key dan
metadata mentah tetap berada di contract internal. Nilai sensitif selalu
diredaaksi sebelum audit, termasuk pada mutation batch kategori.

## Runtime Activation dan Cache

- Database menjadi source of truth.
- Increment awal memakai memoization per request agar pembacaan berulang tidak
  menambah query di request yang sama.
- Nilai tidak disimpan pada cache lintas request pada increment awal. Ini
  mencegah stale value ketika invalidation cache gagal.
- Perubahan aktif pada request berikutnya. Response mutation memakai nilai baru
  yang sudah tervalidasi.
- Storage failure memakai default registry dan menghasilkan diagnostic aman.
- Cache lintas request hanya boleh ditambahkan melalui increment dan test
  versioned invalidation/failure yang terpisah.

## Frontend Contract

- Page owner: `resources/js/pages/System/SystemSetting`.
- Layout: `system-dashboard-layout`.
- Daftar setting dikelompokkan menjadi API, Password, Session, Email,
  Pagination, Branding, Monitoring, dan Operations.
- Search serta shortcut `/` tersedia.
- Edit memakai satu modal per kategori, bukan sheet atau editor per baris.
- Satu reason wajib diisi untuk seluruh perubahan yang dipilih dalam kategori.
- Nilai sensitif tidak diprefill; field kosong berarti mempertahankan nilai
  tersimpan.
- Loading, empty, validation error, storage error, success toast, dan
  unauthorized state tersedia.
- Nilai default dan source value terlihat tanpa membuka data sensitif.
- Light/dark, seluruh palette, mobile, keyboard, focus, dan WCAG dasar diuji.

## Acceptance Criteria

- Generator dry-run dan actual output mengikuti profile `default-v1` tanpa
  menimpa module existing.
- Manifest, provider, permission identity, dependency, route, migration, dan
  README module valid.
- Setting valid dapat disimpan dan dibaca sebagai typed value.
- Setting invalid, key tidak terdaftar, type salah, dan range salah ditolak.
- Non-SuperSystem ditolak pada web, API, command, policy, dan Action boundary.
- Mutation tanpa reason ditolak.
- Batch kategori menolak key lintas kategori, duplikasi, value tidak valid, dan
  ketidakkonsistenan gabungan tanpa menyimpan perubahan parsial.
- Audit gagal menyebabkan mutation gagal dan data setting tidak berubah.
- Storage unavailable atau record invalid menghasilkan safe default dan
  diagnostic tanpa secret.
- Migration fresh, rollback, relation, index, unique key, dan global seeder
  lulus pada MySQL.
- Rate limit, idempotency, session, branding, monitoring, RTO, dan RPO membaca
  reader yang sama tanpa direct repository dependency.
- Appearance pribadi tetap mengalahkan default global.
- Frontend dapat dibuka dan diuji end-to-end melalui browser.
- Module discovery, validation, inspect, test, static analysis, frontend build,
  dan CI quality gate lulus.

## Commands dan Test Plan

```bash
php artisan module:discover --json
php artisan module:validate --json
php artisan module:list --json
php artisan module:inspect System/SystemSetting --json
php artisan test --filter=SystemSetting
npm run lint:check
npm run format:check
npm run types:check
npm run build
composer ci:check
```

## Generator Contract

- Prompt: lakukan Project Intake dan Existing Module Inventory; buat module
  `SystemSetting` pada domain `System` memakai profile `default-v1`; tampilkan
  dry-run sebelum mutation dan jangan menulis file sebelum hasil ditinjau.
- Dry-run command:
  `php artisan module:make SystemSetting --domain=System --profile=default-v1 --dry-run --json`.
- Expected dry-run: `MODULE_PREVIEWED`, target dan canonical structure terlihat,
  diagnostic kosong, filesystem tidak berubah.
- Actual command:
  `php artisan module:make SystemSetting --domain=System --profile=default-v1 --force --yes --json`.
- Expected actual: `MODULE_CREATED`, path, namespace, manifest, provider,
  runtime config, permission source, route files, README, dan structure valid.

## Boundaries

- Always: validasi input, pakai ULID, authorization backend, reason, audit,
  safe default, typed contract, Ziggy, dan test positif/negatif.
- Ask first: ubah ADR, instal package media/cache baru, aktifkan cache lintas
  request, tambah secret storage, ubah schema production, atau tambah queue.
- Never: simpan secret plain, akses private model module lain, jadikan frontend
  security boundary, hardcode URL route, atau mengganti appearance pribadi.

## Status Keputusan

- ADR-0001 tentang boundary, public contract, dan audit berstatus `Accepted`.
- ADR-0002 tentang konsistensi runtime, cache, dan appearance berstatus
  `Accepted`.
- Detail upload logo/favicon tetap menjadi scope lanjutan sampai owner media,
  dependency, dan storage policy diputuskan melalui ADR terpisah.

## Revision History

| Versi | Tanggal | Perubahan |
| --- | --- | --- |
| 1.0 | 2026-08-06 | Menetapkan specification awal SystemSetting berdasarkan preflight workspace |
| 1.1 | 2026-08-06 | Menyelaraskan kondisi aktual module, runtime consumer, dan keputusan |
| 1.2 | 2026-08-10 | Menambah kategori, label operator, serta nilai before/after audit yang aman |
