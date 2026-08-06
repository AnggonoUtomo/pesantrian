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
    MySQL menunjukkan kolom ULID, dan `AccessControlSchemaTest` memeriksa
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
- [x] Fondasi enterprise module didokumentasikan.
  - Kondisi awal: status Contract/Interface, Domain Event, Application Event,
    Integration Event, Command, Query/Read Contract, Shared Kernel,
    Facade/Module API, dan Queue/Job tersebar di beberapa dokumen dan belum
    memiliki matriks status tunggal untuk increment berikutnya.
  - Perubahan: README, specification, implementation plan, task, code-flow,
    baseline framework, dan ADR-0003 sekarang memuat status, owner/boundary,
    dependency, acceptance, verification, serta aturan evolusi untuk sembilan
    fondasi enterprise tersebut.
  - Alasan: implementasi module enterprise tidak boleh menambahkan pola
    Command, Event, Facade, Shared Kernel, atau Queue secara sembarangan.
  - Evidence: `git diff --check` lulus; semua status AccessControl dapat
    ditelusuri dari README module sampai dokumen authoritative 03.12 dan ADR.
  - Batasan: runtime AccessControl tetap CQRS-lite sampai increment Command /
    Handler, Domain Event, atau Integration Event disetujui dan diuji.
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

## Documentation alignment — komunikasi dan eksekusi module

- [x] Status Contract dan Module API dicatat.
  - Kondisi awal: AccessControl sudah memiliki `AuthorizationCapability` dan
    `RoleAssignmentCapability`, tetapi batas public API belum dirangkum pada
    checkpoint task.
  - Perubahan: status kedua contract dicatat sebagai public capability module.
    Pemanggil lintas module tidak boleh mengambil model, repository, atau
    adapter Spatie secara langsung.
  - Alasan: contract menjadi batas dependency yang dapat ditelusuri saat
    evaluasi atau rollback AccessControl.
  - Evidence: `README.md`, `specification.md`, `implementation-plan.md`, dan
    `docs/03-IMPLEMENTATION/03.12-MODULE-COMMUNICATION-AND-EXECUTION.md`.
- [x] Status Event, Action, Query, dan CQRS-lite dicatat.
  - Kondisi awal: document menyebut Action dan Query, tetapi belum membedakan
    Domain Event dari Integration Event serta belum menyatakan bahwa Command
    Bus bukan dependency runtime saat ini.
  - Perubahan: AccessControl ditetapkan memakai Action untuk mutation dan
    Query untuk read. Domain Event, Application Event, Integration Event,
    Command Bus, dan Queue/Job belum aktif pada runtime module.
  - Alasan: implementasi saat ini adalah CQRS-lite; penambahan bus atau event
    lintas module tanpa consumer nyata akan menambah coupling tanpa kebutuhan.
  - Evidence: ADR-0003 dan dokumen authoritative 03.12.
- [x] Batas Shared Kernel dan Facade ditinjau.
  - Kondisi awal: belum ada Shared Kernel domain atau Facade AccessControl.
  - Perubahan: `packages/StarterKit` dicatat sebagai framework package, bukan
    Shared Kernel domain. Public contract tetap menjadi Module API dan Facade
    tidak ditambahkan.
  - Alasan: mencegah private implementation berubah menjadi dependency lintas
    module.
  - Evidence: `docs/06-FRAMEWORK/06.02-MODULE-CONTRACTS.md`, root
    `AGENTS.md`, dan `docs/AGENTS.md`.
- [x] Checklist ditinjau setelah pembaruan.
  - Kondisi awal: dokumen module dan baseline memiliki istilah komunikasi
    yang belum seragam.
  - Perubahan: specification, plan, README, task, code-flow, baseline,
    framework contract, dan ADR diselaraskan tanpa mengubah source code.
  - Alasan: rollback berikutnya harus dapat dimulai dari AccessControl dengan
    baseline dokumentasi yang sama.
  - Evidence: `git diff --check` lulus; tidak ada file `app/`, `resources/`,
    `tests/`, atau konfigurasi runtime yang diubah.

## Increment penutupan Open Risk

- [x] Ownership route AccessControl dipindahkan ke module.
  - Kondisi awal: route AccessControl berada di `routes/web.php`, sedangkan
    `app/Modules/System/AccessControl/Routes/web.php` kosong.
  - Perubahan: route dipindahkan ke file module dan `ServiceProvider` memuat
    route web/API dengan `loadRoutesFrom()`; URL dan nama route tetap sama.
  - Alasan: module harus memiliki route, provider, migration, dan permission
    boundary sendiri agar discovery, rollback, dan isolasi dapat ditelusuri.
  - Evidence: `php artisan route:list --path=system` tetap menampilkan route
    AccessControl; focused page test dan Ziggy route test lulus.
- [x] Layout legacy ditutup dengan re-export.
  - Kondisi awal: terdapat implementasi shell kedua pada folder page
    AccessControl.
  - Perubahan: file legacy sekarang me-re-export
    `resources/js/layouts/system-dashboard-layout.tsx`.
  - Alasan: import lama tidak boleh menghasilkan footer atau perilaku shell
    yang berbeda.
  - Evidence: `rg` tidak menemukan import aktif ke layout legacy; type check,
    lint, dan build lulus.
- [x] Test SuperSystem diperkuat.
  - Kondisi awal: test hanya membuktikan actor biasa tidak dapat memutasi role
    `SuperSystem`; belum ada regression test untuk bypass global SuperSystem.
  - Perubahan: menambahkan test bahwa SuperSystem dapat mengelola role biasa,
    tetapi tetap ditolak saat memutasi role SuperSystem.
  - Alasan: `Gate::before` tidak boleh menghapus proteksi resource protected.
  - Evidence: `AccessControlPageTest` lulus.
- [x] Metadata owner permission diselaraskan.
  - Kondisi awal: `system.dashboard.view` didefinisikan di `AccessControl` tetapi
    metadata `module` bernilai `System`.
  - Perubahan: metadata seluruh permission pada `permissions.php` memakai
    owner `AccessControl`; unit test memvalidasi lima identity tersebut.
  - Alasan: permission identity harus dimiliki oleh module yang menjadi sumber
    dan pemilik lifecycle-nya.
  - Evidence: `AccessControlPermissionIdentityTest` lulus.
- [x] Checklist Open Risk ditinjau setelah implementasi.
  - Kondisi akhir: seluruh empat Open Risk evaluasi sudah memiliki perubahan,
    test, evidence, atau batasan yang jelas.
  - Evidence: `composer ci:check` lulus; tidak ada Open Risk aktif pada scope
    ini.

## Increment penyelarasan AccessControl — RoleAssignmentCapability runtime

- [x] Public role-assignment contract memiliki implementasi runtime.
  - Kondisi awal: `RoleAssignmentCapability` sudah didefinisikan dan dipakai
    oleh UserManagement, tetapi `AccessControl\ServiceProvider` belum memiliki
    binding untuk contract tersebut. Container belum membuktikan bahwa action
    role assignment dapat di-resolve.
  - Perubahan: menambahkan
    `app/Modules/System/AccessControl/Infrastructure/Services/SpatieRoleAssignmentAdapter.php`
    dan binding singleton pada
    `app/Modules/System/AccessControl/ServiceProvider.php`.
  - Alasan: AccessControl adalah owner role/permission dan harus menyediakan
    public capability yang benar-benar dapat dipakai module lain.
  - Acceptance: contract dapat di-resolve; actor dengan permission dapat
    assign/revoke role biasa; actor tanpa permission ditolak; role
    `SuperSystem` tidak dapat dikelola actor biasa.
  - Evidence: `AccessControlRoleAssignmentCapabilityTest` lulus 3 test,
    bersama test authorization dan contract menjadi 7 test/60 assertion;
    Pint lulus; PHPStan lulus tanpa error.

- [x] Security boundary role assignment ditinjau.
  - Kondisi awal: contract hanya mendefinisikan method tanpa aturan runtime
    tentang permission assignment dan role protected.
  - Perubahan: adapter memeriksa `access_control.role.assign`, memvalidasi
    role pada guard `web`, memeriksa target mendukung API role, dan menolak
    assignment/revoke `SuperSystem` dari actor non-`SuperSystem`.
  - Alasan: permission sensitive tidak boleh berubah menjadi jalur eskalasi
    privilege yang tidak terkontrol.
  - Evidence: focused negative test actor tanpa permission dan actor biasa
    terhadap `SuperSystem` sama-sama lulus.

- [x] Checklist ditinjau kembali setelah implementasi.
  - Kondisi akhir: contract, binding, adapter, test, dan dokumentasi downstream
    sudah diperiksa. Tidak ada Open Risk aktif untuk runtime binding pada scope
    increment ini.
  - Evidence: `git diff --check` lulus; dokumentasi AccessControl dan referensi
    UserManagement menyebut status adapter runtime yang sama.

- [x] Public role catalog capability tersedia untuk consumer module.
  - Kondisi awal: UserManagement membutuhkan daftar role untuk UI assignment,
    tetapi tidak boleh membaca model Role atau adapter Spatie AccessControl.
  - Perubahan: menambahkan `RoleCatalogCapability`, DTO `RoleOption`, adapter
    `SpatieRoleCatalogAdapter`, dan binding pada `ServiceProvider`.
  - Alasan: daftar role harus tersedia melalui public API owner tanpa concrete
    dependency lintas module.
  - Evidence: `AccessControlRoleAssignmentCapabilityTest` memverifikasi binding
    dan hasil role catalog; PHPStan dan full CI lulus.

- [x] Seluruh Open Risk AccessControl ditinjau dan statusnya ditetapkan.
  - Kondisi awal: daftar risiko mencampur defect runtime, keputusan scope
    CQRS-lite, dan release gate migrasi database existing.
  - Perubahan: defect binding role assignment ditutup melalui adapter dan test;
    fondasi CQRS yang belum aktif ditetapkan sebagai keputusan scope, bukan
    risiko terbuka; migrasi existing tetap dicatat sebagai release gate yang
    terkendali dan tidak dapat dieksekusi tanpa environment shared nyata.
  - Alasan: tim harus dapat membedakan pekerjaan yang wajib diperbaiki sekarang
    dari capability yang memang menunggu consumer atau keputusan increment baru.
  - Acceptance: tidak ada Open Risk runtime yang belum memiliki mitigasi;
    batasan CQRS dan migrasi external memiliki owner/trigger yang jelas.
  - Evidence: `composer ci:check` lulus dengan 168 test dan 630 assertion;
    `module:validate` dan `module:inspect` lulus; README, specification,
    implementation plan, tasks, dan upgrade runbook menyatakan status yang
    konsisten.
