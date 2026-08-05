# Tasks: AccessControl Module

Setiap task harus kecil, dapat diverifikasi, dan tidak mencampur capability
UserManagement, AuditLog, atau SystemSetting.

## Task 01 — Namespace dan boundary module

**Tujuan:** menetapkan `System/AccessControl` sebagai module authorization
baseline dengan namespace yang konsisten.

**Files:** folder `docs/Projects/06.System/01.AccessControl/`, ADR namespace,
manifest module, dan README module.

**Acceptance criteria:**

- [x] Namespace `App\Modules\System\AccessControl` disepakati.
- [x] Path `app/Modules/System/AccessControl` tidak bentrok dengan module valid.
- [x] Ownership capability authorization dan permission tercatat.

**Hasil implementasi:** Disetujui pada 2026-08-05. Namespace, domain, ownership
capability, role `SuperSystem`, permission naming, shared authorization context,
dan boundary Spatie Permission sudah ditetapkan.

**Test:** `php artisan module:discover --json`

**Evidence:** ADR-001 berstatus `Diterima`; inventory module saat ini kosong dan
tidak ada target `System/AccessControl` yang bentrok.

## Task 02 — Module skeleton dan manifest

**Tujuan:** membuat struktur module dari profile generator dan memastikan
manifest tervalidasi registry.

**Files:** `app/Modules/System/AccessControl/`, module manifest, provider,
runtime config, permission identity, dan README module.

**Acceptance criteria:**

- [x] Struktur golden module tersedia.
- [x] `module.json` dan `module.php` valid.
- [x] Module tidak menimpa module existing.

**Hasil implementasi:** Selesai pada 2026-08-05. Generator profile `default-v1`
membuat `app/Modules/System/AccessControl` dengan directory DDD-lite,
`module.json`, `module.php`, `permissions.php`, `ServiceProvider.php`, README,
dan route entry point. Permission final, policy, service, migration, test
module, dan business logic belum dibuat.

**Test:** `php artisan module:make AccessControl --domain=System --dry-run --json`

**Evidence:** `module:discover --json`, `module:validate --json`, dan
`module:list --json` menemukan satu module valid tanpa diagnostic. Percobaan
tanpa `--force` ditolak dengan `MODULE_GENERATION_INVALID`; percobaan ulang pada
target existing ditolak dengan `MODULE_GENERATION_FAILED`. `git diff --check`
lulus.

## Task 03 — Permission identity

**Tujuan:** menetapkan dan memvalidasi permission identity yang dimiliki
`AccessControl`.

**Files:** `permissions.php`, permission contract, validator, dan test.

**Acceptance criteria:**

- [x] Permission key mengikuti format yang disepakati.
- [x] Owner module adalah `AccessControl`.
- [x] Duplicate permission ditolak.
- [x] Permission sensitif diberi metadata yang tepat.
- [x] Role privileged menggunakan nama `SuperSystem`.

**Hasil implementasi:** Selesai pada 2026-08-05. `permissions.php` berisi lima
permission AccessControl dengan owner, format key, dan metadata `sensitive`.

- [x] Scope task selesai.
  - Kondisi awal: `permissions.php` masih kosong dari generator.
  - Perubahan: menambahkan permission identity AccessControl, termasuk
    `system.dashboard.view`,
    dan focused permission test.
  - Alasan: vocabulary permission harus tersedia sebelum adapter Spatie.
  - Evidence: focused test lulus dengan 13 test dan 30 assertion.

**Test:** focused permission contract test.

## Task 04 — Public authorization capability

**Tujuan:** menyediakan contract typed untuk pemeriksaan authorization oleh
module lain.

**Files:** public contract, DTO/result, adapter Spatie internal, service, dan
contract test.

**Acceptance criteria:**

- [x] Actor berizin dapat melewati pemeriksaan.
- [x] Actor tanpa izin ditolak.
- [x] Module pemanggil tidak mengimpor private model atau repository.
- [x] Hasil capability typed dan tidak memuat data sensitif.
- [x] Policy menangani resource, scope, state, dan `SuperSystem` pada capability
  dasar role.
- [x] Use case mengulang authorization sebelum mutation.

**Hasil implementasi:** Selesai pada 2026-08-05 untuk capability dasar.

- [x] Scope task selesai.
  - Kondisi awal: module belum memiliki public contract dan adapter runtime.
  - Perubahan: menambahkan `AuthorizationCapability`,
    `AuthorizationDecision`, adapter Spatie internal, binding provider, dan
    feature test.
  - Alasan: module lain perlu memeriksa authorization tanpa mengimpor model
    atau detail package Spatie.
  - Evidence: `AccessControlAuthorizationCapabilityTest` lulus, 2 test dan
    7 assertion; Pint juga lulus.
  - Batasan: policy resource/state untuk resource bisnis selain role akan
    dibuat oleh module pemilik resource.

## Open risk — ULID dan runtime Spatie

- [x] Schema starter kit dan Spatie menggunakan ULID.
  - Kondisi awal: migration starter kit dan migration bawaan Spatie memakai
    `bigint`.
  - Perubahan: migration `users`, `passkeys`, dan `jobs` memakai ULID; module
    menambahkan migration permission ULID; model `User`, `Role`, dan
    `Permission` memakai `HasUlids`.
  - Alasan: aturan baseline melarang schema campuran integer dan ULID.
  - Evidence: `AccessControlSchemaTest` lulus dan role `SuperSystem` dapat
    diberikan kepada user dengan ID string.
- [x] Provider module memuat migration permission.
  - Kondisi awal: provider module belum terdaftar dan migration module tidak
    terdeteksi Laravel.
  - Perubahan: mendaftarkan provider pada `bootstrap/providers.php` dan memakai
    `loadMigrationsFrom`.
  - Alasan: schema module harus ikut lifecycle Laravel.
  - Evidence: `php artisan migrate:status` menampilkan migration permission
    module sebagai pending.
- [x] Migration upgrade dari database integer ke ULID memiliki runbook dan
  guard baseline lokal.
  - Kondisi awal: migration source sebelumnya memakai integer, sehingga perlu
    dipastikan database lokal tidak tertinggal dari source tersebut.
  - Perubahan: migration baseline dan module sudah memakai ULID; audit schema
    lokal memastikan `users`, `passkeys`, `jobs`, role, permission, dan seluruh
    pivot memakai tipe string ULID.
  - Alasan: database lokal saat ini adalah baseline development dan tidak boleh
    menyimpan schema campuran.
  - Evidence: `migrate:status` seluruh migration berstatus `Ran`, audit tabel
    PostgreSQL menunjukkan kolom ULID, dan `AccessControlSchemaTest` memeriksa
    seluruh ID terkait.
  - Perubahan tambahan: menambahkan `upgrade-runbook.md` dengan prosedur
    backup, mapping immutable, expand-and-contract, validasi, dan rollback.
  - Status risiko: terkendali untuk workspace development. Eksekusi shared
    environment menjadi release gate terpisah yang wajib memiliki database,
    backup restore, maintenance window, dan approval release.

## Quality risk — frontend bundle

- [x] Warning chunk frontend di atas 500 kB ditutup.
  - Kondisi awal: `npm run build` menghasilkan chunk aplikasi sekitar 500 kB
    dan memberi warning ukuran bundle.
  - Perubahan: `vite.config.ts` memisahkan dependency `node_modules` menjadi
    vendor chunk berdasarkan package.
  - Alasan: chunk aplikasi utama lebih kecil dan dependency dapat di-cache
    terpisah oleh browser.
  - Evidence: `npm run build` lulus tanpa warning ukuran chunk; chunk aplikasi
    turun menjadi sekitar 55 kB dan chunk vendor terbesar sekitar 202 kB.

**Test:** positive dan negative authorization contract test.

## Task 05 — Integration, security, dan quality gate

**Tujuan:** memastikan module dapat dipakai dalam flow Laravel dan aman sebagai
security authority.

**Files:** middleware/policy integration, authorization context, tests,
README, dan execution evidence.

**Acceptance criteria:**

- [x] Server-side denial terbukti.
- [x] Frontend context hanya digunakan untuk UX.
- [x] Shared props memakai `roles`, `permissions`, dan `superSystem`.
- [x] `roles` dan `permissions` berbentuk associative object boolean.
- [x] Seeder dummy membuat permission, role, dan user demo secara idempotent.
- [x] Sidebar memiliki menu Access Control dengan visibility berbasis permission.
- [x] Discovery, validation, list, dan test lulus.
- [x] Forbidden dependency dan sensitive output scan bersih.
- [x] Frontend role/permission page, state UI, dan browser/accessibility test
  tersedia.
  - Kondisi awal: backend selesai, tetapi dialog lifecycle role, endpoint
    create/delete, dan quality gate browser masih terbuka.
  - Perubahan: menambahkan dialog role, endpoint create/delete dengan policy,
    test positive/negative, responsive verification, dan Lighthouse audit.
  - Alasan: module baru dapat dinyatakan selesai hanya jika capability dapat
    ditinjau dan diuji langsung melalui UI.
  - Evidence: full suite `php artisan test` lulus 113 test dan 374 assertion;
    `npm run types:check`, `npm run lint:check`, build, serta Lighthouse mobile
    lulus.

Rincian frontend ada di [Frontend AccessControl](frontend/README.md). Task
frontend dikerjakan setelah dokumentasi frontend disetujui dan sebelum module
AccessControl dinyatakan selesai.

**Hasil implementasi:** Backend dan frontend AccessControl selesai pada
2026-08-05. Page role/permission, mutation role, route Ziggy, sidebar menu,
seeder demo, browser review, dan accessibility check sudah tersedia.

- [x] Scope task selesai.
  - Kondisi awal: middleware, policy, use case re-check, dan shared Inertia
    authorization context belum tersedia.
  - Perubahan: menambahkan `AccessControlPolicy`, `RoleController` dengan
    `can:*` middleware, `AuthorizeRoleMutation`, helper authorization pada
    `User`, dan props `auth.roles`, `auth.permissions`, `auth.superSystem`.
  - Alasan: backend harus tetap menjadi security authority; context frontend
    hanya dipakai untuk visibility dan UX.
  - Evidence: policy/context test lulus dengan 5 test dan 11 assertion; full
    suite lulus dengan 113 test dan 374 assertion; Pint, discovery, validation,
    forbidden dependency scan, dan sensitive output scan lulus.
  - Perbaikan quality gate: test generator hanya membersihkan fixture miliknya
    sendiri agar tidak menghapus module existing; aturan profile validation
    menerima ULID string.
  - Catatan: `AppServiceProvider` memiliki satu `Gate::before` terpusat untuk
    `SuperSystem`; ability `impersonate` tetap mengembalikan `null` agar aturan
    khusus impersonation tetap wajib dibuat oleh capability tersendiri.
  - Batasan: module belum boleh dinyatakan selesai sampai page frontend,
    responsive state, dan browser/accessibility test tersedia.

**Test:** full relevant quality gate.

**Evidence tambahan untuk seeder dan sidebar:**

- `AccessControlSeeder` membaca permission identity seluruh module valid melalui
  `ModuleRegistry`, lalu membuat role `SuperSystem`, role `SecurityAdmin`, serta
  dua user demo. Seeder memakai `firstOrCreate`,
  `syncPermissions`, dan `syncRoles`, sehingga aman dijalankan berulang.
- Seeder dipanggil oleh `database/seeders/DatabaseSeeder.php` melalui dependency
  order global, sehingga `php artisan migrate:fresh --seed` menjalankan
  bootstrap AccessControl tanpa command module satu per satu.
- Command `php artisan access-control:seed` tetap tersedia sebagai adapter untuk
  focused operation atau test module.
- Seeder tidak membuat data jika `app.env` adalah `production`. Password demo
  berasal dari `ACCESS_CONTROL_DUMMY_PASSWORD` atau dibuat acak.
- Jika env password diisi, command module dapat menyetel ulang password user
  demo existing; jika kosong, password existing tidak diubah.
- `app-sidebar.tsx` menambahkan menu `Access Control` melalui route Ziggy
  `access-control.index`; menu disembunyikan untuk actor tanpa capability.
- Endpoint `PUT access-control.roles.permissions.update` menyimpan permission
  role biasa dan menolak `SuperSystem` melalui policy server-side.
- Route `system.dashboard` menyediakan halaman dashboard khusus System dengan
  menu sidebar `System Dashboard`; middleware tetap memeriksa permission
  `access_control.role.manage`.
- `AccessControlSeederTest` lulus: empat test, 15 assertion.
- `AccessControlPageTest` lulus: sepuluh test, 41 assertion.
- `vendor/bin/pint --test` dan `npm run types:check` lulus.
- Browser mobile snapshot pada `/system/access-control` setelah login
  `security-admin@example.test` menampilkan menu `Access Control`, page role,
  dan permission group; credential hanya digunakan pada environment lokal.

## Final quality checkpoint

- [x] Inventory sebelum perubahan tersedia.
- [x] Positive dan negative test tersedia untuk identity dan schema dasar.
- [x] Authorization, security, audit, dan dependency impact ditinjau.
- [x] Module discovery/validation/list lulus sebelum perubahan runtime.
- [x] Documentation dan execution evidence diperbarui.
- [x] Seeder demo dan menu sidebar ditinjau ulang setelah implementasi.
- [x] Open risk migration upgrade baseline lokal ditutup dan risiko deployment
  existing dikendalikan melalui runbook release.
- [x] OPEN RISK quality gate PHPStan ditutup.
  - Kondisi awal: `CreateRole` mengembalikan hasil `Spatie\Permission\Contracts\Role`,
    seeder memakai `collect(require ...)` tanpa tipe yang dapat diinferensikan,
    dan membaca `env()` langsung.
  - Perubahan: `CreateRole` memakai `Role::query()->create()`, permission
    definition diproses melalui method typed `permissionKeys()`, dan password
    demo dibaca dari `config('access_control.dummy_password')` melalui
    `config/access_control.php`.
  - Alasan: memastikan return type model module jelas, generic PHPStan dapat
    diinferensikan, dan konfigurasi tetap aman saat config cache aktif.
  - Evidence: `vendor/bin/phpstan analyse --no-progress` lulus 0 error;
    `composer ci:check` lulus dengan 118 test dan 401 assertion.

## Increment review boundary controller

- [x] Logic query, mutation, dan validasi dikeluarkan dari `RoleController`.
  - Kondisi awal: `RoleController` langsung menjalankan query `Role` dan
    `Permission`, membentuk array page, memvalidasi input, membuat role,
    melakukan sync permission, dan menghapus role.
  - Perubahan: menambahkan `Application/Queries/BuildAccessControlDashboard`,
    `Application/DTO/AccessControlDashboardData`, action `CreateRole`,
    `SyncRolePermissions`, `DeleteRole`, serta request
    `StoreRoleRequest` dan `SyncRolePermissionsRequest`. Controller sekarang
    hanya menangani middleware, orchestration, flash toast, dan response.
    `AuthorizeRoleMutation` menjadi pemeriksaan authorization use-case yang
    dipakai bersama policy dan action.
  - Alasan: mengikuti DDD-lite modular monolith. Presentation tidak boleh
    mengambil alih business/persistence logic, dan authorization tidak boleh
    tersebar pada setiap mutation.
  - Acceptance: response Inertia dan behavior mutation tetap sama, actor tanpa
    permission tetap ditolak, `SuperSystem` tetap protected, dan controller
    tidak berisi query/persistence/validasi langsung.
  - Evidence: `AccessControlArchitectureTest` lulus 2 test dengan 15
    assertion; `AccessControlPageTest` dan
    `AccessControlPolicyAndContextTest` bersama-sama lulus 17 test dengan 70
    assertion; Pint lulus. Regression test memeriksa controller tidak memuat
    `Role::query`, `Permission::query`, `Role::create`, persistence mutation,
    atau `$request->validate()`.
