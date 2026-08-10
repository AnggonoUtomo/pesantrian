# AI Prompt Guide - Module & Boundary Foundation Generator

Panduan ini adalah instruksi operasional untuk agent AI ketika memeriksa,
membuat, atau memperluas module pada Laravel Engineering Starter Kit.

Dokumen ini mengikuti [`AGENTS.md`](AGENTS.md), [`README.md`](README.md),
[Generator Specification](03-IMPLEMENTATION/03.05-GENERATOR-SPEC.md),
[Module Specification](03-IMPLEMENTATION/03.07-MODULES.md), dan
[Baseline Task Plan](03-IMPLEMENTATION/03.11-BASELINE-TASK-PLAN.md).

## First Read: Intake dan Existing Module Verification

Agent tidak boleh langsung menjalankan generator. Urutan pertama yang wajib
dilakukan:

1. Baca `AGENTS.md`, `README.md`, lalu dokumen prerequisite sesuai urutan.
2. Tentukan mode project: `greenfield`, `existing starter kit`, atau
   `module extension`.
3. Inventarisasi module yang sudah dibuat pada `app/Modules`, termasuk:
   `module.json`, `module.php`, `permissions.php`, `ServiceProvider`, route,
   event, migration, test, dan README.
4. Jalankan command berikut jika tersedia:

   ```bash
   php artisan module:discover --json
   php artisan module:validate --json
   php artisan module:list --json
   ```

5. Simpan evidence inventory: nama, domain, namespace, path, version, status,
   provider, dependency, permission source, validation result, dan ownership.
6. Pastikan target module, path, namespace, permission key, dan provider belum
   dimiliki module valid lain.
7. Jika command belum tersedia, lakukan read-only scan dan laporkan keterbatasan;
   jangan menganggap project kosong.

Generator wajib berhenti ketika target sudah dimiliki module valid, kecuali user
secara eksplisit meminta `module extension` atau perubahan module existing.

## Baseline Module Order

Module baseline pertama yang dibuat adalah `AccessControl` karena module ini
memiliki ownership role, permission, policy, `SuperSystem`, dan public
authorization capability.

`UserManagement` menjadi module business pertama setelah public authorization
contract AccessControl tersedia. UserManagement tidak boleh membuat authorization
implementation sendiri.

Urutan baseline:

1. Framework prerequisite: manifest, registry, discovery, stub engine, filesystem
   safety, dan console.
2. Generate dan implement `System/AccessControl`.
3. Generate dan implement `System/UserManagement` melalui public contract.
4. Implement `AuditLog`.
5. Implement `SystemSetting`.

## Standard Prompt Template

Gunakan prompt eksplisit berikut:

```text
Lakukan Project Intake dan Existing Module Inventory terlebih dahulu.
Verifikasi module yang sudah ada dengan module:discover, module:validate, dan
module:list jika tersedia. Jangan membuat duplicate module.

Buat module {Module} pada domain {Domain} dengan profile default-v1 menggunakan:
php artisan module:make {Module} --domain={Domain} --profile=default-v1 --dry-run --json

Setelah dry-run disetujui, jalankan pembuatan aktual. Ikuti struktur DDD-lite,
manifest schema, permission schema, public contract, README module, dan test
contract pada dokumentasi repository. Implementasikan secara incremental file
demi file dan verifikasi setiap increment sebelum melanjutkan.
```

## Generator Command Sequence

Urutan command yang dianjurkan:

```bash
php artisan module:discover --json
php artisan module:validate --json
php artisan module:list --json
php artisan module:make AccessControl --domain=System --profile=default-v1 --dry-run --json
php artisan module:make AccessControl --domain=System --profile=default-v1 --force --yes --json
php artisan module:make UserManagement --domain=System --profile=default-v1 --dry-run --json
php artisan module:make UserManagement --domain=System --profile=default-v1 --force --yes --json
php artisan module:validate --json
php artisan module:list --json
```

Untuk mutasi pada environment non-interactive, gunakan `--force --yes`.
Validasi target module spesifik belum tersedia; gunakan validasi registry global.

## Standard Authorization Pattern untuk Semua Module

Gunakan pola berikut pada setiap module:

```text
Authentication
  -> Controller/Route Middleware (`can:*`)
  -> Policy/Gate module owner
  -> AccessControl public capability
  -> Spatie Permission adapter
  -> Resource/state rule
  -> Audit bila mutation atau event sensitif
```

Controller middleware adalah coarse-grained guard. Policy module owner wajib
menangani ownership, scope, state, dan resource-specific rule. Permission
identity tetap berada di `permissions.php` module owner. Cross-module
authorization memakai public contract, capability, DTO, atau public event.

Backend selalu menjadi security authority. Inertia boleh membagikan typed,
scoped `auth.authorization` berisi `abilities`, `roles`, atau
`isSuperSystem` untuk visibility dan UX. Hook frontend seperti `useAuthorization`
tidak boleh dianggap sebagai pengaman dan tidak boleh memberikan akses yang
tidak diberikan backend.

## Required UserManagement Output

Target module:

```text
app/Modules/System/UserManagement/
|-- Application/
|   |-- Contracts/ModuleCapability.php
|   |-- DTO/.gitkeep
|   |-- Actions/.gitkeep
|   |-- Queries/.gitkeep
|   `-- Services/.gitkeep
|-- Domain/
|   |-- Contracts/.gitkeep
|   |-- Entities/.gitkeep
|   |-- Events/.gitkeep
|   |-- Exceptions/.gitkeep
|   |-- Services/.gitkeep
|   `-- ValueObjects/.gitkeep
|-- Infrastructure/
|   |-- Persistence/Models/.gitkeep
|   |-- Persistence/Repositories/.gitkeep
|   |-- Observers/.gitkeep
|   |-- Providers/ServiceProvider.php
|   `-- External/.gitkeep
|-- Presentation/
|   |-- Controllers/.gitkeep
|   |-- Policies/.gitkeep
|   |-- Requests/.gitkeep
|   `-- Resources/.gitkeep
|-- Database/
|   |-- Factories/.gitkeep
|   |-- Migrations/.gitkeep
|   `-- Seeders/.gitkeep
|-- Routes/
|   |-- api.php
|   |-- web.php
|   |-- console.php
|   `-- channels.php
|-- Tests/
|   |-- Unit/ModuleStructureTest.php
|   |-- Feature/.gitkeep
|   `-- Integration/.gitkeep
|-- module.json
|-- module.php
|-- permissions.php
`-- README.md
```

`module.json` wajib memuat `schema_version`, `name`, `domain`, `namespace`,
`version`, `status`, `path`, `provider`, `dependencies`, `permission_source`,
dan `config_source`. Status manifest hanya `enabled` atau `disabled`.

## Incremental Implementation: AccessControl lalu UserManagement

Jangan membuat seluruh business logic sekaligus. AccessControl harus selesai pada
level contract, permission, policy, dan validation sebelum flow authorization
UserManagement diaktifkan. Setiap increment harus memiliki
scope, acceptance criteria, focused test, verification evidence, dan execution
log. Lanjut ke increment berikutnya hanya setelah increment sebelumnya lulus.

### AccessControl Prerequisite Increments

Implementasikan AccessControl terlebih dahulu secara incremental:

1. `module.json`, `module.php`, dan `ServiceProvider.php`.
2. `permissions.php` dengan metadata permission lengkap.
3. Public authorization contract/capability dan DTO result.
4. Role, permission sync, policy, dan `SuperSystem` behavior.
5. `Tests/Unit`, `Tests/Feature`, dan contract/permission tests.

Acceptance minimum: module dapat di-discover dan di-validate, permission schema
valid, public authorization contract dapat dikonsumsi module lain, dan tidak ada
private dependency lintas module. Setelah ini diverifikasi, lanjutkan ke
UserManagement increments berikut.

### Increment 0 - Inventory dan Dry Run

File yang dibuat/diubah: hanya evidence inventory di lokasi project yang telah
disepakati; generator belum menulis module.

Acceptance:

- module existing teridentifikasi;
- target `System/UserManagement` belum duplicate;
- dry-run menampilkan planned file tanpa write.

Verify:

```bash
php artisan module:discover --json
php artisan module:validate --json
php artisan module:list --json
php artisan module:make UserManagement --domain=System --profile=default-v1 --dry-run --json
```

### Increment 1 - Manifest dan Runtime Identity

File:

- `module.json`
- `module.php`
- `ServiceProvider.php`

Acceptance:

- namespace, path, provider, schema version, status, dan dependency valid;
- provider tidak memuat business logic;
- module dapat ditemukan dan divalidasi.

Verify:

```bash
php artisan module:discover --json
php artisan module:validate --json
```

### Increment 2 - Permission Identity

File:

- `permissions.php`
- `Application/Contracts/ModuleCapability.php`

Acceptance:

- permission memakai metadata `key`, `description`, `module`, `sensitive`;
- permission key unik;
- capability publik tidak mengakses private implementation module lain.

Verify: permission schema test, duplicate-key test, dan contract test.

### Increment 3 - Domain Boundary

File:

- `Domain/Contracts/*`
- `Domain/Entities/*`
- `Domain/ValueObjects/*`
- `Domain/Events/*`
- `Domain/Exceptions/*`
- `Domain/Services/*`

Acceptance:

- domain tidak bergantung pada Laravel, Eloquent, HTTP, atau UI;
- identifier dan correlation ID memakai ULID;
- business rule user tidak diletakkan di generator atau framework package.

Verify: unit test domain dan static dependency boundary test.

### Increment 4 - Application Boundary

File:

- `Application/Actions/*`
- `Application/DTO/*`
- `Application/Queries/*`
- `Application/Services/*`
- `Application/Contracts/*`

Acceptance:

- DTO typed dan tervalidasi;
- use case mengorkestrasi domain tanpa mengambil alih ownership auth starter
  kit;
- cross-module call hanya memakai public contract atau event.

Verify: application feature test dan contract test.

### Increment 5 - Infrastructure dan Persistence

File:

- `Infrastructure/Persistence/Models/*`
- `Infrastructure/Persistence/Repositories/*`
- `Infrastructure/Observers/*`
- `Database/Migrations/*`
- `Database/Factories/*`
- `Database/Seeders/*`

Acceptance:

- persistence implementation tidak bocor ke Domain/Application contract;
- primary key dan foreign key memakai ULID;
- migration fresh dan upgrade dapat dijalankan.

Verify: migration, repository, factory, seeder, dan integration test.

### Increment 6 - Presentation dan Routes

File:

- `Presentation/Controllers/*`
- `Presentation/Policies/*`
- `Presentation/Requests/*`
- `Presentation/Resources/*`
- `Routes/api.php`
- `Routes/web.php`
- `resources/js/pages/System/UserManagement/*` bila frontend diperlukan.

Acceptance:

- input tervalidasi sebelum side effect;
- authorization memakai policy/capability;
- route frontend menggunakan Ziggy, bukan Wayfinder;
- response tidak membocorkan secret atau sensitive payload.

Verify: API/feature test, TypeScript check, frontend test, dan browser flow bila
ada critical journey.

### Increment 7 - Tests dan README Module

File:

- `Tests/Unit/*`
- `Tests/Feature/*`
- `Tests/Integration/*`
- `README.md`

Acceptance:

- README menjelaskan purpose, boundary, public contract, permission, dependency,
  configuration, test, dan operational notes;
- generated structure snapshot sesuai golden contract;
- positive dan negative path tersedia.

Verify:

```bash
php artisan module:validate --json
```

## Safety Rules

- Default adalah no overwrite.
- `--dry-run` tidak boleh menulis file.
- Conflict harus berhenti sebelum side effect.
- Staging harus dipromosikan secara atomik setelah seluruh output tervalidasi.
- Kegagalan staging/promotion harus membersihkan temporary output.
- Generator tidak boleh menghapus dependency forbidden secara otomatis.
- Secret, token, password, credential, dan sensitive payload tidak boleh masuk
  output, log, diagnostic, template, atau generated artifact.

## Acceptance Criteria Global

- Project intake dan existing module inventory tersedia sebelum generate.
- `AccessControl` dapat dibuat sebagai module baseline pertama.
- `UserManagement` dapat dibuat setelah public authorization contract tersedia.
- Output sesuai manifest/file manifest profile `default-v1`.
- `module:discover`, `module:validate`, dan `module:list` berhasil.
- Dry-run, conflict, force/yes, cleanup, dan JSON output teruji.
- Generated structure reproducible dan tidak menghasilkan duplicate ownership.
- Tidak ada Wayfinder atau Laravel Boost pada dependency, source, config, atau
  generated output.

## Format Laporan Task

AI wajib mencatat kondisi awal, file/path yang dibuat atau diubah, perubahan
kode/configuration, alasan, acceptance criteria, command/test, hasil evidence,
dan risiko. Gunakan checklist bertingkat dan execution log yang dapat dipahami
tanpa konteks chat.

## Forbidden Prompt Pattern

Jangan gunakan prompt seperti:

```text
Buat semua module sekaligus dan langsung implementasikan seluruh business logic.
```

Prompt tersebut melewati inventory, memperbesar blast radius, dan menyulitkan
verifikasi ownership serta rollback.
