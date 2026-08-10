# Tasks: System/UserManagement

Status pekerjaan: `Task 10 quality checkpoint selesai`. Mutation UI umum tetap
menjadi scope lanjutan dan tidak boleh dianggap sudah selesai.

## Task 01 — Prompt generator, project intake, dan dry-run

**Tujuan:** memastikan target UserManagement berada pada boundary `System`,
belum dimiliki module valid, dan generator menghasilkan preview target yang
sesuai tanpa menulis file.

**Files:** `app/Modules`, `module.json` existing, command module, dan dokumen
baseline generator.

**Acceptance criteria:**

- [x] Inventory module existing mencatat AccessControl sebagai module valid.
    - Kondisi awal: registry hanya memiliki module `System/AccessControl`.
    - Perubahan: inventory dijalankan melalui discovery, validation, list, dan inspect.
    - Alasan: UserManagement tidak boleh dibuat tanpa mengetahui owner dan dependency existing.
    - Evidence: `module:discover`, `module:validate`, `module:list`, dan
      `module:inspect System/AccessControl` lulus.
- [x] `module:inspect System/AccessControl --json` menampilkan detail manifest,
      permission source, runtime config, dependency, dan diagnostic tanpa side effect.
    - Evidence: output `MODULE_INSPECTED` memuat manifest, dependency, permission,
      dan `diagnostics: []`.
- [x] Parent boundary target tercatat sebagai `app/Modules/System/`.
    - Kondisi awal: target UserManagement belum memiliki skeleton.
    - Perubahan: target ditetapkan sebagai `app/Modules/System/UserManagement` dengan
      namespace `App\\Modules\\System\\UserManagement`.
    - Alasan: UserManagement berada dalam boundary parent `System`.
    - Evidence: output dry-run menampilkan target dan planned structure yang benar.
- [x] Target `System/UserManagement` belum duplicate pada name, path, namespace,
      provider, atau permission key.
    - Kondisi awal: hanya AccessControl ditemukan pada inventory.
    - Perubahan: generator menyusun target baru tanpa conflict diagnostic.
    - Alasan: duplicate identity dapat menimpa atau membingungkan registry.
    - Evidence: output dry-run `diagnostics: []`.
- [x] Dry-run tidak menulis file.
    - Kondisi awal: folder `app/Modules/System/UserManagement` belum tersedia.
    - Perubahan: hanya preview generator dijalankan.
    - Alasan: dry-run harus aman sebelum mutasi filesystem.
    - Evidence: output `MODULE_PREVIEWED` menyatakan tidak ada file ditulis.
- [x] Output JSON memiliki code `MODULE_PREVIEWED` dan diagnostics yang dapat
      ditindaklanjuti.
    - Evidence: target `app/Modules/System/UserManagement`, profile `default-v1`,
      27 directory, 9 file, dan diagnostics kosong.

**Prompt kerja:**

```text
Lakukan Project Intake dan Existing Module Inventory terlebih dahulu.
Verifikasi module yang sudah ada dengan module:discover, module:validate,
module:list, dan module:inspect System/AccessControl. Jangan membuat duplicate
module.

Buat module UserManagement pada domain System dengan profile default-v1
menggunakan:

php artisan module:make UserManagement --domain=System --profile=default-v1 --dry-run --json

Tampilkan planned file dan jangan menulis filesystem sebelum dry-run disetujui.
```

**Hasil yang diharapkan:**

- output menunjukkan `MODULE_PREVIEWED`;
- path target adalah `app/Modules/System/UserManagement`;
- planned structure mengikuti `03.04-FOLDER-STRUCTURE.md`;
- `app/Modules/System/AccessControl` tidak berubah;
- tidak ada business logic, migration business, atau frontend business yang
  dianggap selesai pada tahap ini.

**Verification:**

```bash
php artisan module:discover --json
php artisan module:validate --json
php artisan module:list --json
php artisan module:inspect System/AccessControl --json
php artisan module:make UserManagement --domain=System --profile=default-v1 --dry-run --json
```

**Hasil implementasi:** selesai pada 2026-08-06. Task 02 boleh dimulai karena
inventory dan dry-run sudah diverifikasi tanpa perubahan filesystem.

## Task 02 — Pembuatan skeleton melalui generator

**Tujuan:** membuat skeleton module setelah hasil dry-run disetujui.

**Files:** `module.json`, `module.php`, `ServiceProvider.php`, `README.md`,
directory canonical, dan route entry point.

**Acceptance criteria:**

- [x] Command aktual dijalankan dengan `--force --yes --json` setelah dry-run
      disetujui.
- [x] Namespace `App\\Modules\\System\\UserManagement` valid.
    - Evidence: `module:inspect System/UserManagement --json` menampilkan namespace
      dan path target yang sesuai.
- [x] Manifest memiliki field wajib dan belum memiliki concrete dependency
      lintas module; dependency contract AccessControl akan ditambahkan pada Task 03.
    - Evidence: manifest valid dengan `dependencies: []`, sesuai skeleton generator.
- [x] Provider hanya melakukan wiring dan tidak memuat business logic.
    - Evidence: `ServiceProvider.php` tersedia pada skeleton dan tidak ada action,
      query, migration, atau business implementation yang dibuat generator. Import
      framework provider memakai alias `FrameworkServiceProvider` agar tidak bentrok
      dengan nama provider module.
- [x] Discovery, validation, dan list tetap lulus untuk AccessControl dan
      UserManagement.
    - Evidence: discovery menemukan 2 module, validation `valid: 2`, dan list
      menampilkan AccessControl serta UserManagement tanpa diagnostics.
- [x] Output memiliki code `MODULE_CREATED`.
    - Evidence: command generator menghasilkan `MODULE_CREATED` dengan 9 file dan
      27 directory planned.
- [x] Generator tidak membuat business logic palsu atau mengambil private
      implementation AccessControl.
    - Evidence: output hanya berisi skeleton canonical, route entry point, manifest,
      provider, permission source kosong, dan README.

**Command:**

```bash
php artisan module:make UserManagement --domain=System --profile=default-v1 --force --yes --json
```

**Hasil yang diharapkan:** module skeleton tersedia pada
`app/Modules/System/UserManagement`, manifest dan structure valid, serta
AccessControl tetap tidak berubah.

**Verification:** `php artisan module:discover --json`,
`php artisan module:validate --json`, dan `php artisan module:list --json`.

**Hasil implementasi:** selesai pada 2026-08-06. Skeleton valid dan siap masuk
Task 03. Business logic belum dibuat.

## Task 03 — Permission identity dan public contract

**Tujuan:** menetapkan vocabulary permission UserManagement dan boundary
komunikasi dengan AccessControl.

**Files:** `permissions.php`, `Application/Contracts`, DTO, dan contract test.

**Acceptance criteria:**

- [x] Permission key unik dan owner-nya UserManagement.
    - Kondisi awal: `app/Modules/System/UserManagement/permissions.php` masih berupa
      source kosong dari generator.
    - Perubahan: ditambahkan enam key `user.view`, `user.create`, `user.update`,
      `user.status.manage`, `user.delete`, dan `user.impersonate`; seluruh identity
      memakai owner `UserManagement`.
    - Alasan: vocabulary permission harus dimiliki module yang memakai capability
      tersebut agar tidak ada duplikasi identity lintas module.
    - Evidence: `UserManagementContractTest` memeriksa jumlah, keunikan, owner,
      dan field wajib setiap permission.
- [x] Permission `user.impersonate` ditandai sensitive.
    - Kondisi awal: belum ada permission identity untuk alur UserManagement.
    - Perubahan: `user.impersonate` ditambahkan dengan `sensitive: true`.
    - Alasan: impersonation mengubah konteks identitas aktif dan memerlukan kontrol
      serta audit khusus pada task lanjutan.
    - Evidence: contract test memeriksa flag sensitive pada key tersebut.
- [x] UserManagement tidak mengimpor private class AccessControl.
    - Kondisi awal: boundary komunikasi role assignment belum memiliki contract
      yang dapat dirujuk.
    - Perubahan: UserManagement hanya mendeklarasikan dependency manifest terhadap
      capability publik AccessControl; tidak ada import model, repository, policy,
      atau service private AccessControl.
    - Alasan: dependency lintas module harus melalui public contract, bukan detail
      implementasi module lain.
    - Evidence: `module:inspect System/UserManagement --json` menampilkan
      dependency `AccessControl`, sedangkan contract test memeriksa boundary
      `RoleAssignmentCapability`.
- [x] Contract role assignment memakai public capability atau contract yang
      disetujui.
    - Kondisi awal: AccessControl belum memiliki contract khusus untuk assignment
      role.
    - Perubahan: ditambahkan `RoleAssignmentCapability` pada
      `app/Modules/System/AccessControl/Application/Contracts` dengan operasi
      `assignRole` dan `revokeRole` yang menerima `Authenticatable`, tanpa type
      Spatie pada interface publik.
    - Alasan: UserManagement membutuhkan boundary stabil tanpa mengimpor concrete
      model atau adapter AccessControl.
    - Evidence: reflection assertion memastikan interface dan dua method publik
      tersedia.
- [x] Test positive dan negative untuk permission identity tersedia.
    - Kondisi awal: belum ada focused test untuk identity dan contract UserManagement.
    - Perubahan: ditambahkan `tests/Feature/UserManagementContractTest.php` dengan
      test positive untuk enam permission dan test boundary untuk capability.
    - Alasan: perubahan contract harus memiliki bukti otomatis sebelum task domain
      dimulai.
    - Evidence: `php artisan test tests/Feature/UserManagementContractTest.php`
      lulus dengan 2 test dan 48 assertions. Negative assertion untuk permission
      sensitif dan boundary private dependency tercakup dalam pemeriksaan contract.
    - Evidence tambahan: `vendor/bin/pint --test` dan `git diff --check` lulus.

**Verification:** `php artisan module:discover --json`,
`php artisan module:validate --json`, `php artisan module:inspect
System/UserManagement --json`, dan focused contract test.

**Hasil implementasi:** selesai pada 2026-08-06. Permission identity,
dependency manifest, public role-assignment contract, focused test, dan adapter
runtime AccessControl sudah tersedia. Adapter berada di owner module
AccessControl dan di-resolve melalui container; UserManagement tetap tidak
mengimpor private implementation. `composer types:check` lulus dengan PHPStan
tanpa error.

## Task 04 — Domain lifecycle user

**Tujuan:** membuat aturan status, active/inactive, soft delete, dan protected
user tanpa ketergantungan Eloquent di Domain.

**Files:** `Domain/Contracts`, `Domain/Entities`, `Domain/ValueObjects`,
`Domain/Exceptions`, dan unit test.

**Acceptance criteria:**

- [x] Status user memiliki vocabulary dan transisi yang disetujui.
    - Kondisi awal: UserManagement belum memiliki domain vocabulary untuk lifecycle
      user.
    - Perubahan: ditambahkan enum `Domain/ValueObjects/UserStatus.php` dengan nilai
      `active`, `inactive`, dan `suspended`. `UserLifecycle` menolak perubahan ke
      status yang sama.
    - Alasan: status harus typed dan aturan lifecycle tidak boleh tersebar di
      controller atau model persistence.
    - Evidence: test transisi active ke suspended lulus; test status sama ditolak
      dan state sebelumnya tetap terjaga.
- [x] `SuperSystem` tidak dapat dinonaktifkan atau dihapus.
    - Kondisi awal: belum ada domain guard untuk user protected.
    - Perubahan: `UserLifecycle::forProtectedUser()` membuat entity protected;
      `changeStatus()` dan `softDelete()` melempar `ProtectedUserMutation`.
    - Alasan: perlindungan SuperSystem harus berada di domain boundary dan tidak
      bergantung pada model Spatie atau HTTP request.
    - Evidence: test protected user menolak perubahan status dan soft delete.
- [x] Domain tidak bergantung pada HTTP, Eloquent, atau UI.
    - Kondisi awal: folder Domain belum memiliki implementation.
    - Perubahan: entity, enum, dan exception hanya memakai PHP native serta
      `DomainException`/`InvalidArgumentException`.
    - Alasan: Domain harus dapat diuji tanpa framework dan tetap dapat dipakai oleh
      Application boundary.
    - Evidence: architecture test memindai seluruh file Domain dan menolak import
      `Illuminate`, `Eloquent`, `Spatie`, `Http`, atau `Inertia`.
- [x] Positive, negative, dan boundary test tersedia.
    - Kondisi awal: belum ada focused lifecycle test.
    - Perubahan: ditambahkan `tests/Unit/UserManagementLifecycleTest.php` untuk
      transisi valid, status sama, protected mutation, soft delete user biasa, dan
      dependency boundary.
    - Alasan: lifecycle memiliki jalur normal, penolakan, dan batas protected user
      yang harus dibuktikan sebelum application layer dibuat.
    - Evidence: `php artisan test tests/Unit/UserManagementLifecycleTest.php`
      lulus dengan 5 test dan 25 assertions; `composer types:check` lulus.

**Verification:** focused domain unit test dan architecture test.

**Hasil implementasi:** selesai pada 2026-08-06. Domain lifecycle tersedia tanpa
ketergantungan framework. Persistence `deleted_at`, migration, dan adapter
repository belum dibuat; pekerjaan tersebut menjadi Task 06 dan sudah tercatat
sebagai dependency berikutnya, bukan risiko terbuka Task 04.

**Open risk Task 04:** tidak ada. Risiko persistence ditransisikan ke Task 06
dengan acceptance migration additive, fresh migration, dan upgrade verification.

## Task 05 — Application actions dan queries

**Tujuan:** memisahkan orchestration lifecycle dari controller.

**Files:** `Application/Actions`, `Application/Queries`, `Application/DTO`,
`Application/Services`, dan test application.

**Acceptance criteria:**

- [x] List/detail query memakai DTO typed.
    - Kondisi awal: Application UserManagement belum memiliki DTO, repository port,
      atau query.
    - Perubahan: ditambahkan `UserData`, `UserListFilter`, `ListUsers`, dan
      `GetUser`. Query hanya memanggil `UserRepository` dan tidak membaca Eloquent.
    - Alasan: controller dan infrastructure harus menerima bentuk data typed yang
      stabil.
    - Evidence: `UserManagementApplicationTest` mengembalikan instance `UserData`
      dari detail query; `UserRepository` memiliki return type DTO dan list typed.
- [x] Create/update/status/soft delete action tervalidasi.
    - Kondisi awal: lifecycle baru tersedia di Domain, tetapi belum ada orchestration
      application.
    - Perubahan: ditambahkan `CreateUser`, `UpdateUser`, `ChangeUserStatus`, dan
      `SoftDeleteUser`. Semua action memeriksa permission melalui
      `AuthorizeUserAction` sebelum memanggil repository contract; DTO create/update
      memvalidasi nama dan email, sedangkan `CreateUserData` menerima password
      transient minimal 8 karakter tanpa menulisnya ke output.
        - Alasan: authorization dan orchestration harus selesai sebelum persistence
          dibuat pada Task 06.
        - Evidence: test positive update/status dan negative create tanpa permission
          serta protected soft delete lulus.
- [x] Role assignment hanya melalui public AccessControl contract.
    - Kondisi awal: `RoleAssignmentCapability` sudah tersedia, tetapi belum dipakai
      oleh application action UserManagement.
    - Perubahan: `AssignUserRole` menerima `RoleAssignmentCapability` dan hanya
      memanggil `assignRole` setelah permission `user.update` lulus; tidak ada import
      model atau adapter Spatie.
    - Alasan: UserManagement tidak boleh mengambil alih ownership role/permission
      AccessControl.
    - Evidence: test memverifikasi capability publik dipanggil dengan actor, target,
      dan role yang benar.
- [x] Impersonation membutuhkan permission dan reason.
    - Kondisi awal: flow impersonation belum memiliki application boundary.
    - Perubahan: ditambahkan `ImpersonationRequestData` yang mewajibkan target dan
      reason, serta `StartImpersonation` yang memeriksa `user.impersonate` dan
      menolak target protected.
    - Alasan: impersonation merupakan mutation sensitif dan tidak boleh berjalan
      hanya karena frontend menampilkan tombol.
    - Evidence: test memverifikasi permission, reason, dan target user sebelum
      session dimulai.
- [x] Actor asli dan target dipisahkan pada session contract.
    - Kondisi awal: belum ada port session untuk menyimpan konteks impersonation.
    - Perubahan: ditambahkan `ImpersonationSession::start()` dengan actor asli,
      target ULID, dan reason sebagai parameter terpisah.
    - Alasan: session adapter Task 09 dapat menyimpan identitas asli dan target
      tanpa mencampurnya pada satu payload ambigu.
    - Evidence: test memverifikasi `start($actor, $targetUserId, $reason)` dipanggil
      dengan nilai terpisah.

**Verification:** application feature test dan authorization negative test.

**Hasil implementasi:** selesai pada 2026-08-06. Application boundary sudah
memiliki DTO, repository/session contract, query, authorization service, dan
action lifecycle. Repository implementation, migration, dan session adapter
belum dibuat; pekerjaan infrastructure dimulai pada Task 06.

**Open risk Task 05:** tidak ada. Dependency infrastructure dan session adapter
ditransisikan ke Task 06/Task 09 sesuai batas scope.

## Task 06 — Infrastructure dan migration

**Tujuan:** menyediakan persistence yang kompatibel dengan tabel `users`
starter kit.

**Files:** migration additive, model/repository, factory, seeder, dan
integration test.

**Acceptance criteria:**

- [x] Semua identifier tetap ULID.
    - Kondisi awal: tabel `users` starter kit sudah memakai ULID, tetapi belum ada
      repository UserManagement.
    - Perubahan: migration tidak mengubah `users.id`; repository memakai string ULID
      dari model existing dan passkey tetap memakai foreign ULID.
    - Alasan: identity authentication dan relasi existing tidak boleh berubah pada
      module extension.
    - Evidence: schema test memeriksa `users.id`; test upgrade membuat passkey
      dengan ULID dan data tetap terbaca.
- [x] Migration tidak merusak authentication, Passkey, atau 2FA.
    - Kondisi awal: User model hanya mendukung Fortify, Passkey, dan 2FA tanpa
      lifecycle column.
    - Perubahan: migration additive menambah `status` dan `deleted_at`; model
      menambahkan `SoftDeletes`, cast lifecycle, dan tidak mengubah field password,
      token, 2FA, atau passkey.
    - Alasan: UserManagement mengelola lifecycle di atas identity source of truth
      starter kit.
    - Evidence: upgrade simulation mempertahankan `two_factor_secret` dan satu row
      passkey setelah migration diterapkan ulang.
- [x] `deleted_at` dan status hanya ditambahkan setelah keputusan disetujui.
    - Kondisi awal: specification dan ADR sudah menetapkan status `active`,
      `inactive`, `suspended`, serta soft delete untuk user non-`SuperSystem`.
    - Perubahan: migration module menambah `status` default `active` dan nullable
      `deleted_at`, keduanya memiliki index.
    - Alasan: field schema mengikuti keputusan domain dan dapat di-rollback.
    - Evidence: schema test menemukan kedua kolom; `UserLifecycle` dan repository
      menjaga protected user serta soft delete.
- [x] Fresh migration dan upgrade verification lulus.
    - Kondisi awal: belum ada migration UserManagement atau bukti upgrade additive.
    - Perubahan: provider module memuat migration; test menjalankan fresh database
      melalui `RefreshDatabase` dan upgrade simulation pada schema users existing.
    - Alasan: fresh install dan existing data memiliki risiko yang berbeda.
    - Evidence: `UserManagementInfrastructureTest` lulus 4 test dan 12 assertions,
      termasuk schema, repository, filter, dan upgrade 2FA/passkey.
- [x] Password, token, dan secret tidak masuk output atau log.
    - Kondisi awal: create application membutuhkan password untuk kolom wajib users.
    - Perubahan: password diterima hanya sebagai field transient `CreateUserData`
      dan diserahkan ke model untuk hashing; tidak dimasukkan ke `UserData`, DTO
      response, test output, atau log.
    - Alasan: repository tidak boleh membuat credential hardcoded atau mengekspos
      secret pada boundary read.
    - Evidence: `UserData` tidak memiliki password; `composer types:check` dan
      focused infrastructure test lulus.

**Verification:** migration test, repository test, `php artisan migrate:fresh`,
dan `php artisan migrate:fresh --seed` melalui global `DatabaseSeeder`.

**Hasil implementasi:** selesai pada 2026-08-06. Migration additive, model
`SoftDeletes`, repository Eloquent, provider binding, dan integration test sudah
tersedia. Migration production/shared belum dijalankan dari workspace ini.

**Open risk Task 06:** tidak ada risiko code yang belum diverifikasi. Risiko
operasional deployment ditransisikan ke [`migration-runbook.md`](migration-runbook.md)
karena eksekusi database shared/production membutuhkan akses operator, backup
nyata, dan persetujuan deployment.

**Evidence tambahan:** `migrate:fresh --env=testing --force`,
`migrate:rollback --env=testing --step=1`, dan `migrate:status --env=testing`
berhasil. Migration kembali berstatus `Pending` setelah rollback dan dapat
dijalankan ulang pada fresh verification.

`DatabaseSeeder` global memanggil seeder module berdasarkan dependency order.
`AccessControlSeederTest::test_database_seeder_global_menjalankan_seeder_module`
membuktikan `seed()` global membuat sebelas permission, dua role, dan dua user
demo.

## Task 07 — Presentation dan authorization

**Tujuan:** menyediakan route backend yang aman dan controller tipis.

**Files:** `Presentation/Controllers`, `Policies`, `Requests`, `Resources`,
`Routes`, dan feature test.

**Acceptance criteria:**

- [x] Middleware coarse-grained diterapkan.
  - Kondisi awal: route UserManagement belum terdaftar dan belum memiliki
    authorization middleware.
  - Perubahan: `UserController::middleware()` menerapkan `can:user.view`,
    `can:user.create`, `can:user.update`, `can:user.status.manage`, dan
    `can:user.delete` sesuai method.
  - Alasan: backend harus menolak actor sebelum action atau persistence dipanggil.
  - Evidence: actor tanpa `user.view` menerima 403 pada presentation test.
- [x] Policy menangani resource, scope, state, dan protected `SuperSystem`.
  - Kondisi awal: UserManagement belum memiliki policy owner.
  - Perubahan: `UserManagementPolicy` memakai `AuthorizationCapability` publik
    AccessControl dan menolak change status, delete, serta impersonate terhadap
    `SuperSystem`.
  - Alasan: rule resource dan protected state harus dimiliki UserManagement,
    sedangkan keputusan permission tetap milik AccessControl.
  - Evidence: policy test menolak mutation target `SuperSystem`.
- [x] FormRequest memvalidasi input sebelum side effect.
  - Kondisi awal: endpoint mutation belum tersedia.
  - Perubahan: ditambahkan `StoreUserRequest`, `UpdateUserRequest`, dan
    `ChangeUserStatusRequest` untuk validasi name, email, password, unique email,
    dan enum status.
  - Alasan: input invalid harus berhenti sebelum Application Action dipanggil.
  - Evidence: FormRequest terhubung langsung pada route mutation; PHPStan lulus.
- [x] Controller tidak memiliki query Eloquent atau business rule.
  - Kondisi awal: controller belum tersedia.
  - Perubahan: `UserController` hanya menerima Request/FormRequest, membuat DTO,
    memanggil Query/Action, menyiapkan Inertia response, dan redirect.
  - Alasan: query, persistence, dan lifecycle rule tetap berada pada Application,
    Domain, dan Infrastructure boundary.
  - Evidence: architecture test memastikan controller tidak memuat `::query()`,
    `->where()`, atau `->get()`.
- [x] Unauthorized actor menerima denial yang konsisten.
  - Kondisi awal: belum ada route UserManagement yang dapat diuji.
  - Perubahan: route diberi middleware `auth`, `verified`, dan permission gate;
    route module memakai nama Ziggy `system.users.*`.
  - Alasan: permission frontend tidak boleh menjadi security boundary.
  - Evidence: presentation test lulus untuk actor berizin dan actor tanpa izin;
    `route:list --name=system.users` menampilkan 6 route.

**Verification:** route, policy, feature, and forbidden response tests.

**Hasil implementasi:** selesai pada 2026-08-06. Route, policy, FormRequest,
resource, controller tipis, provider registration, dan feature test tersedia.
Page frontend dan browser flow diselesaikan pada Task 08.

**Open risk Task 07:** ditutup pada Task 08. Route module sebelumnya belum
memakai middleware `web`, sehingga session browser tidak terbaca dan request
diarahkan ke `/login`. Perbaikannya dicatat pada Task 08 bersama penambahan
allowlist route Ziggy.

## Task 08 — Frontend vertical slice

**Tujuan:** menyediakan UI UserManagement yang dapat ditinjau langsung.

**Files:** `resources/js/pages/System/UserManagement`, route Ziggy, shared
props, component test, dan browser test.

**Acceptance criteria:**

- [x] Page list user memakai System dashboard baseline.
    - Kondisi awal: route backend sudah merender `System/UserManagement/pages/Index`,
      tetapi page dan layout frontend belum tersedia.
    - Perubahan: ditambahkan `resources/js/pages/System/UserManagement/pages/Index.tsx`,
      `Show.tsx`, `types.ts`, dan layout bersama
      `resources/js/layouts/system-dashboard-layout.tsx`. Sidebar serta command
      palette juga diberi link `User Management` dengan permission `user.view`.
    - Alasan: module yang memiliki alur pengguna harus memiliki vertical slice
      yang dapat ditinjau dan memakai visual System dashboard yang sama.
    - Evidence: browser menampilkan heading `User Management`, daftar dua user,
      status user, dan link detail pada `GET /system/users`.
- [x] Loading, empty, error, dan unauthorized state tersedia.
    - Kondisi awal: belum ada state UI untuk query daftar user.
    - Perubahan: `Index.tsx` memiliki state loading pada pencarian, empty state,
      error alert, dan tampilan akses terbatas. Detail page juga memiliki state
      akses terbatas.
    - Alasan: pengguna harus memahami status request tanpa menjadikan frontend
      sebagai security boundary.
    - Evidence: backend negative test memastikan actor tanpa permission tetap
      menerima `403`.
- [x] Permission visibility hanya untuk UX.
    - Kondisi awal: navigasi UserManagement belum tersedia.
    - Perubahan: sidebar dan command palette hanya menampilkan link jika
      `auth.superSystem` atau `auth.permissions['user.view']` aktif; middleware
      `can:user.view` tetap berada di backend.
    - Alasan: context Inertia hanya mengatur UX, sedangkan backend tetap menjadi
      security authority.
    - Evidence: `UserManagementPresentationTest` lulus untuk actor berizin dan
      actor tanpa permission.
- [x] Route frontend seluruhnya memakai Ziggy.
    - Kondisi awal: route UserManagement belum masuk `config/ziggy.php`.
    - Perubahan: enam route `system.users.*` ditambahkan ke allowlist Ziggy;
      page, sidebar, dan command palette memakai helper `route()`.
    - Alasan: link frontend mengikuti route identity Laravel tanpa hardcoded URL.
    - Evidence: page browser berhasil membuka list dan detail tanpa console error.
- [x] Responsive, keyboard, dark/light, dan accessibility ditinjau.
    - Kondisi awal: UI belum tersedia untuk diuji pada viewport kecil dan mode
      warna berbeda.
    - Perubahan: list memakai overflow horizontal, form memiliki label
      accessible, dan layout menggunakan shared dashboard shell.
    - Alasan: daftar user harus dapat digunakan pada layar kecil dan keyboard.
    - Evidence: Chrome DevTools diuji pada `390x844`, mobile touch, dark mode;
      Lighthouse Accessibility, Best Practices, SEO, dan Agentic Browsing
      masing-masing menghasilkan `100` tanpa failure.
- [x] Browser test membuktikan request sampai response backend.
    - Kondisi awal: route browser diarahkan ke `/login` karena route module belum
      memakai middleware `web`; page frontend juga belum tersedia.
    - Perubahan: `Routes/web.php` memakai `web`, `auth`, dan `verified`; page
      frontend serta allowlist Ziggy tersedia.
    - Alasan: session browser harus diproses sebelum authorization dan response
      Inertia harus memiliki component yang dapat di-resolve Vite.
    - Evidence: Chrome DevTools mencatat `GET /system/users [200]`, memuat
      `Index.tsx`, tanpa console error. Detail route juga berhasil dibuka.

**Verification:** `npm run lint:check`, `npm run format:check`,
`npm run types:check`, `npm run build`, dan
`php artisan test tests/Feature/UserManagementPresentationTest.php` lulus.

**Hasil implementasi:** selesai pada 2026-08-06. Page list/detail, shared layout,
permission visibility, Ziggy route identity, browser session middleware, dan
focused browser evidence sudah tersedia.

**Open risk Task 08:** tidak ada untuk scope frontend list/detail. Mutation UI
seperti create, update, status, delete, dan impersonation tetap menjadi task
lanjutan karena membutuhkan flow form, audit, dan negative browser test yang
lebih luas.

## Task 08A — Adaptasi UI tabel dan modal UserManagement

**Tujuan:** mengadaptasi pola `FrontendContoh/users` ke module System tanpa
  membawa route lama atau fitur yang belum tersedia pada backend.

**Files:** `resources/js/pages/System/UserManagement/pages/Index.tsx`,
`resources/js/pages/System/UserManagement/components/*`, `types.ts`, serta
README dan specification module.

**Checklist sebelum kerja:**

- [x] Referensi dan contract backend sudah dibandingkan.
  - Kondisi awal: `FrontendContoh/users` menggunakan route `users.*`, tipe
    numerik, role, avatar, archive, dan restore; module saat ini memakai
    `system.users.*` dan data user minimal.
  - Perubahan: hanya pola tabel, search, summary, action, shortcut, dan
    impersonation yang diadaptasi.
  - Alasan: UI tidak boleh membuat contract palsu atau menampilkan kemampuan
    yang belum didukung backend.
  - Evidence: route module, `UserResource`, FormRequest, dan reference folder
    sudah dibaca sebelum coding.

**Acceptance criteria:**

- [x] Tabel dan search tersedia.
  - Kondisi awal: page hanya menampilkan tabel sederhana dan link detail.
  - Perubahan: ditambahkan `UserTable.tsx` dengan search Ziggy,
    status badge, protected indicator, avatar fallback, dan action icon.
  - Alasan: operator perlu menemukan user dan memilih aksi dari satu workspace.
  - Evidence: browser menampilkan dua user, search input, status, protected,
    dan action view/edit/impersonate.
- [x] Add, edit, dan view menggunakan modal.
  - Kondisi awal: create/edit belum tersedia pada page; detail memakai route
    page terpisah.
  - Perubahan: `UserFormDialog.tsx` memakai `system.users.store` dan
    `system.users.update`; `UserViewDialog.tsx` menampilkan detail tanpa
    Sheet; modal create, edit, dan view dapat ditutup dengan Escape.
  - Alasan: user meminta alur tetap berada dalam workspace UserManagement.
  - Evidence: Chrome DevTools membuka modal tambah, edit, dan view.
- [x] Impersonation memakai modal dengan reason wajib.
  - Kondisi awal: reason hanya tersedia pada `Show.tsx`.
  - Perubahan: `ImpersonateUserDialog.tsx` mengirim reason ke
    `system.users.impersonate` dan hanya tersedia untuk user yang tidak
    protected serta actor yang memiliki permission.
  - Alasan: alasan adalah bagian dari security contract dan audit event.
  - Evidence: browser menampilkan input reason, validasi required, dan tombol
    Login-as pada user non-protected.
- [x] Shortcut mengikuti AccessControl tanpa konflik command palette.
  - Kondisi awal: shortcut reference memakai `Ctrl/Cmd+K`, yang juga digunakan
    command palette global.
  - Perubahan: `UserShortcutBar.tsx` dan `Index.tsx` memakai `/` untuk search,
    `Shift+A` untuk tambah, `Enter` untuk detail, dan `Escape` untuk modal.
  - Alasan: mencegah dua handler membuka dua focus layer sekaligus.
  - Evidence: `Shift+A` membuka modal tambah; setelah `Ctrl+K` diuji sebagai
    command palette, console tidak lagi memiliki warning aria-hidden.
- [x] Responsive, permission visibility, dan empty/error state tetap tersedia.
  - Kondisi awal: state dasar sudah ada pada Task 08.
  - Perubahan: state dipertahankan pada page; action create/edit/impersonate
    disembunyikan berdasarkan permission dan user protected.
  - Alasan: frontend hanya membantu UX, sedangkan backend tetap menjadi
    security authority.
  - Evidence: TypeScript, ESLint, Vite build, dan browser snapshot lulus.

**Verification:** `npm run types:check`, `npm run lint:check`, `npm run build`,
dan browser test Chrome DevTools pada `/system/users`.

**Hasil implementasi:** selesai pada 2026-08-06 untuk scope UI tabel, search,
summary, modal create/edit/view, modal impersonation, dan shortcut.

**Open risk Task 08A:** mutation status/delete dan role assignment belum
  ditambahkan ke UI karena contract form dan capability terkait perlu increment
  terpisah. Fitur tersebut tidak disamarkan sebagai fitur yang sudah tersedia.

**Verification:** ESLint, Prettier, TypeScript, build, browser, dan axe-core.

**Hasil implementasi:** belum dikerjakan.

## Task 09 — Impersonation dan audit boundary

**Tujuan:** mengaktifkan impersonation secara aman setelah keputusan session dan
audit disetujui.

**Files:** action, contract, policy, session adapter, event, audit boundary,
UI control, dan security test.

**Acceptance criteria:**

- [x] Permission dan reason wajib.
    - Kondisi awal: `StartImpersonation` dan DTO sudah memeriksa permission serta
      reason, tetapi route dan FormRequest belum tersedia.
    - Perubahan: `StartImpersonationRequest` mewajibkan reason 3–500 karakter;
      route memakai `can:impersonate,user`; Application Action memeriksa
      `user.impersonate` sebelum session diubah.
    - Alasan: input dan authorization harus berhenti sebelum side effect login.
    - Evidence: test tanpa permission menerima `403`, test tanpa reason memiliki
      validation error, dan test positif berhasil memulai impersonation.
- [x] Target `SuperSystem` selalu ditolak.
    - Kondisi awal: policy `impersonate()` sudah memiliki protected guard,
      tetapi belum diuji melalui route.
    - Perubahan: policy dan action tetap melakukan penolakan ganda terhadap
      target protected.
    - Alasan: bypass `SuperSystem` tidak boleh berlaku untuk ability
      impersonation.
    - Evidence: `UserManagementImpersonationTest` menerima `403` dan actor
      tetap berada pada akun asli.
- [x] Actor asli dapat kembali dengan aman.
    - Kondisi awal: `ImpersonationSession::leave()` baru berupa contract.
    - Perubahan: `LaravelImpersonationSession` menyimpan actor asli, melakukan
      login kembali saat leave, menghapus key impersonation, dan meregenerasi
      session ID.
    - Alasan: leave path wajib tersedia bersamaan dengan start path untuk
      mencegah actor kehilangan konteks asli.
    - Evidence: feature test dan browser test kembali ke `Super System Demo`.
- [x] Session actor asli dan target tidak tertukar.
    - Kondisi awal: belum ada adapter Laravel yang menyimpan konteks session.
    - Perubahan: key `impersonation.actor_id`, `target_id`, `started_at`, dan
      `reason` dikelola oleh `LaravelImpersonationSession`.
    - Alasan: actor asli harus dapat dibedakan dari user target sepanjang flow.
    - Evidence: test memeriksa actor target setelah start, actor asli setelah
      leave, dan session key hilang setelah leave.
- [x] Sensitive event memiliki audit contract tanpa secret atau password.
    - Kondisi awal: AuditLog belum tersedia dan UserManagement belum memiliki
      event impersonation.
    - Perubahan: ditambahkan `UserImpersonationStarted` dan
      `UserImpersonationEnded` pada `Domain/Events`; event hanya membawa actor,
      target, reason, timestamp, dan correlation-safe context.
    - Alasan: AuditLog berikutnya dapat menjadi consumer tanpa membuat audit
      storage kedua atau membawa credential sensitif.
    - Evidence: event test memastikan field password, token, credential, dan
      session cookie tidak tersedia.
- [x] Negative security test dan browser flow tersedia.
    - Kondisi awal: Task 09 belum memiliki route, UI, atau security test.
    - Perubahan: ditambahkan `UserManagementImpersonationTest`, form reason pada
      `Show.tsx`, banner leave pada `system-dashboard-layout.tsx`, dan route
      Ziggy start/leave.
    - Alasan: capability sensitif harus terbukti dari backend sampai browser.
    - Evidence: 4 test dan 30 assertions lulus; browser start/leave berhasil;
      console kosong; Lighthouse desktop seluruh kategori bernilai `100`.

**Verification:** `php artisan test tests/Feature/UserManagementImpersonationTest.php`,
`npm run lint:check`, `npm run format:check`, `npm run types:check`, `npm run build`,
browser test Chrome DevTools, dan Lighthouse accessibility.

**Hasil implementasi:** selesai pada 2026-08-06. ADR-0002, session adapter,
route start/leave, event audit, redaction test, UI reason/banner, dan browser
flow sudah tersedia.

**Open risk Task 09:** dependency AuditLog sudah ditutup pada increment
System/AuditLog melalui consumer synchronous dan persistence append-only.

## Task 10 — Seeder 50 user dummy berbasis factory

**Tujuan:** menyediakan data user development agar tabel, search, status, role,
dan permission dapat diuji tanpa membuat user manual satu per satu.

**Files:** `app/Modules/System/UserManagement/Database/Seeders/UserManagementSeeder.php`,
`database/seeders/DatabaseSeeder.php`, dan
`tests/Feature/UserManagementSeederTest.php`.

**Checklist sebelum kerja:**

- [x] Owner module dan dependency sudah ditinjau.
  - Kondisi awal: `DatabaseSeeder` hanya memanggil `AccessControlSeeder`;
    UserManagement belum memiliki seeder.
  - Perubahan: seeder baru ditempatkan pada owner module dan dipanggil setelah
    AccessControl agar role `SecurityAdmin` sudah tersedia.
  - Alasan: seeder harus mengikuti ownership module dan dependency order global.
  - Evidence: `AGENTS.md`, `docs/AGENTS.md`, `DatabaseSeeder`, dan
    `AccessControlSeeder` dibaca sebelum implementasi.

**Acceptance criteria:**

- [x] Lima puluh user dummy berbasis factory dibuat dengan status bervariasi.
  - Kondisi awal: fresh database hanya memiliki `SuperSystem` dan
    `SecurityAdmin`.
  - Perubahan: `UserManagementSeeder` memakai `User::factory()` untuk membuat
    50 user dengan email deterministik `user-management-dummy-XX@example.test`
    dan status active, inactive, serta suspended.
  - Alasan: data bervariasi diperlukan untuk meninjau tabel dan filter UI.
  - Evidence: focused test memverifikasi total 52 user global; 50 dummy
    memiliki distribusi 17 active, 17 inactive, dan 16 suspended.
- [x] Role dan password mengikuti aturan keamanan development.
  - Kondisi awal: role dummy belum tersedia dari UserManagement.
  - Perubahan: semua dummy user diberi role `SecurityAdmin`; password memakai
    `config('access_control.dummy_password')` atau random runtime.
  - Alasan: user dapat dipakai untuk menguji authorization tanpa menyimpan
    credential di source.
  - Evidence: focused test memverifikasi 51 user ber-role `SecurityAdmin`,
    termasuk akun baseline SecurityAdmin.
- [x] Seeder global dan idempotency tersedia.
  - Kondisi awal: `php artisan migrate:fresh --seed` hanya membuat dua user.
  - Perubahan: `DatabaseSeeder` memanggil `UserManagementSeeder`;
    `firstOrCreate` dan `syncRoles` menjaga seed dapat diulang.
  - Alasan: bootstrap global harus menjadi satu entry point dan tidak boleh
    menggandakan user.
  - Evidence: test global dua kali tetap menghasilkan 52 user; 3 focused test
    seeder lulus dengan 8 assertion setelah seeder diubah berbasis factory.
- [x] Production guard tetap aktif.
  - Kondisi awal: dummy seeder tidak boleh mengisi database production.
  - Perubahan: seeder berhenti ketika `config('app.env') === 'production'`.
  - Alasan: data demo tidak boleh masuk environment production.
  - Evidence: test production guard menghasilkan 0 user.

**Verification:**

```bash
php artisan test tests/Feature/UserManagementSeederTest.php tests/Feature/AccessControlSeederTest.php
php artisan migrate:fresh --seed --force
```

**Hasil implementasi:** selesai pada 2026-08-06. Bootstrap development sekarang
menghasilkan 50 user dummy tambahan dan dua akun baseline.

**Open risk Task 10:** tidak ada untuk scope dummy seeder. Akun demo hanya untuk
development dan tidak boleh digunakan sebagai data production.

**Catatan penyesuaian 2026-08-07:** jumlah dummy dinaikkan dari 10 menjadi 50
untuk meninjau pagination. Nama dan atribut user dibuat oleh factory; hanya
email dibuat deterministik untuk menjaga idempotency. Test terbaru memverifikasi
52 user pada fresh database, yaitu 50 dummy dan dua akun baseline.

## Final Quality Checkpoint

- [x] Semua task dalam scope UserManagement selesai dengan evidence detail.
    - Kondisi awal: checklist checkpoint masih kosong dan evidence tersebar di
      Task 04 sampai Task 09 serta Task 08A.
    - Perubahan: seluruh task yang sudah dikerjakan ditinjau ulang; Task 08A
      mencatat adaptasi tabel, search, summary, shortcut, dan modal.
    - Alasan: status selesai harus dapat ditelusuri tanpa membaca percakapan.
    - Evidence: task memiliki kondisi awal, perubahan, alasan, acceptance,
      command, hasil, dan batasan.
- [x] Test positif dan negatif lulus.
    - Kondisi awal: test backend dan security tersebar pada beberapa focused
      test.
    - Perubahan: quality gate menjalankan seluruh Pest suite dan test
      impersonation, authorization, schema, migration, serta architecture.
    - Alasan: module harus membuktikan jalur berhasil dan jalur ditolak.
    - Evidence: `composer ci:check` lulus dengan 149 test dan 569 assertion;
      PHPStan menemukan 0 error.
- [x] Discovery, validation, dan list lulus.
    - Kondisi awal: module UserManagement sudah tersedia tetapi checkpoint
      belum memiliki evidence terbaru.
    - Perubahan: discovery, validation, dan list dijalankan ulang untuk
      `AccessControl` dan `UserManagement`.
    - Alasan: registry dan boundary module harus valid sebelum release.
    - Evidence: dua module ditemukan, dua module valid, tanpa diagnostic.
- [x] Migration fresh dan upgrade terverifikasi.
    - Kondisi awal: fresh migration dan upgrade simulation sudah diuji pada
      Task 06, tetapi belum dicatat pada checkpoint akhir.
    - Perubahan: `php artisan migrate:fresh --seed --force` dijalankan ulang;
      seluruh 7 migration dan global `AccessControlSeeder` berhasil. Status
      migration kemudian diperiksa dengan `php artisan migrate:status`.
    - Alasan: bootstrap global harus dapat dibuat ulang tanpa konflik relation.
    - Evidence: semua migration berstatus `Ran`; upgrade simulation Task 06
      mempertahankan data existing, 2FA, dan Passkey.
    - Batasan: shared/production tetap membutuhkan backup, operator, dan
      rehearsal sesuai `migration-runbook.md`.
- [x] PHPStan, Pint, Pest, ESLint, TypeScript, build, browser, dan accessibility lulus.
    - Kondisi awal: Lighthouse sempat menemukan contrast header tabel 4.46:1.
    - Perubahan: header tabel memakai `text-foreground/80`, lalu lint, type
      check, build, browser, dan Lighthouse diulang.
    - Alasan: contrast minimal 4.5:1 wajib dipenuhi agar tabel mudah dibaca.
    - Evidence: `composer ci:check` lulus; `npm run build` lulus; browser
      menampilkan list, modal view/edit/create, impersonation, dan shortcut;
      Lighthouse desktop/mobile menghasilkan Accessibility 100, Best
      Practices 100, SEO 100, Agentic Browsing 100; console kosong.
- [x] README dan execution evidence diperbarui.
    - Kondisi awal: README, specification, implementation plan, dan tasks
      belum mencatat final quality checkpoint serta konflik shortcut.
    - Perubahan: dokumen tersebut diperbarui dengan aturan modal, referensi
      `FrontendContoh`, shortcut `/`, ownership `Ctrl/Cmd+K`, dan evidence.
    - Alasan: keputusan UI dan batas contract harus dapat dipakai tim saat
      melanjutkan module.
    - Evidence: revision history dan Task 08A memuat perubahan serta command.
- [x] Open risk ditutup atau dilaporkan.
    - Kondisi awal: status/delete dan role assignment belum menjadi bagian dari
      UI increment saat ini.
    - Perubahan: risiko dicatat eksplisit sebagai scope lanjutan; tidak ada
      fitur palsu yang ditampilkan pada UI.
    - Alasan: risiko yang belum memiliki contract tidak boleh dianggap selesai.
    - Evidence: Open risk Task 08A dan batasan migration production tercatat;
      tidak ada unresolved error pada scope yang sudah diimplementasikan.

## Documentation alignment — komunikasi dan eksekusi module

- [x] Status Contract dan dependency lintas module dicatat.
  - Kondisi awal: UserManagement menggunakan `UserRepository`,
    `ImpersonationSession`, dan capability publik AccessControl, tetapi batas
    dependency belum diringkas pada checkpoint akhir.
  - Perubahan: contract internal dan public capability lintas module dicatat;
    UserManagement tidak boleh mengimpor model, repository, policy, atau
    service private AccessControl.
  - Alasan: boundary ini menjadi acuan saat evaluasi dan rollback dimulai dari
    AccessControl.
  - Evidence: `README.md`, `specification.md`, `implementation-plan.md`, dan
    `docs/03-IMPLEMENTATION/03.12-MODULE-COMMUNICATION-AND-EXECUTION.md`.
- [x] Status Event, Action, Query, dan CQRS-lite dicatat.
  - Kondisi awal: event impersonation sudah ada, tetapi belum ditegaskan
    statusnya terhadap Application Event dan Integration Event.
  - Perubahan: `UserImpersonationStarted` dan `UserImpersonationEnded`
    ditetapkan sebagai Domain Event synchronous. `CreateUser`, `UpdateUser`,
    `ChangeUserStatus`, `SoftDeleteUser`, dan `StartImpersonation` tetap
    Application Action; `ListUsers` dan `GetUser` tetap Query.
  - Alasan: hanya event dengan consumer lintas module yang boleh dipromosikan
    menjadi Integration Event.
  - Evidence: ADR-0002 UserManagement, ADR-0003, dan specification module.
- [x] Status Command Bus, Queue/Job, Facade, dan Shared Kernel ditinjau.
  - Kondisi awal: tidak ada Command Bus, queued listener, Facade module, atau
    Shared Kernel domain pada runtime UserManagement.
  - Perubahan: dokumen menyatakan semua capability tersebut belum menjadi
    dependency scope saat ini. CQRS yang berlaku adalah CQRS-lite.
  - Alasan: menjaga implementasi tetap sederhana sampai tersedia kebutuhan,
    consumer, retry/idempotency contract, dan ADR baru.
  - Evidence: `README.md`, `specification.md`, `implementation-plan.md`, serta
    dokumen authoritative 03.12.
- [x] Checklist ditinjau setelah pembaruan.
  - Kondisi awal: final checkpoint belum memiliki item khusus untuk memastikan
    pola komunikasi module konsisten dengan baseline global.
  - Perubahan: item alignment ini ditambahkan dan seluruh dokumen module
    diselaraskan tanpa mengubah source code.
  - Alasan: pekerjaan berikutnya dapat melanjutkan evaluasi AccessControl
    dengan status UserManagement yang jelas.
  - Evidence: `git diff --check` lulus; perubahan hanya berada pada folder
    `docs/`.

## Increment baseline visual module System

- [x] UserManagement mengikuti pola surface dan accent AccessControl.
  - Kondisi awal: shared `dashboard-card` sudah dipakai, tetapi summary card
    belum memiliki accent card. Header tabel dan hover row masih memakai
    `bg-muted/40` serta `hover:bg-accent/40` secara langsung.
  - Perubahan: `UserSummaryCards.tsx` memakai
    `dashboard-card--blue/emerald/violet` untuk membedakan total user, user
    aktif, dan user terlindungi. `UserTable.tsx` memakai
    `dashboard-table-header` dan `dashboard-table-row`. `app.css` menambahkan
    surface header tabel serta hover row memakai overlay transparan `--primary`
    dengan opacity rendah agar hue palette tidak bergeser.
  - Alasan: seluruh module System harus memiliki hierarki surface yang sama
    pada light/dark tanpa mengubah behavior tabel dan modal.
  - Acceptance: light card utama putih bersih, subcard sedikit lebih gelap;
    dark card mengikuti palette aktif, subcard lebih gelap; accent icon dan
    border tetap jelas; tidak ada horizontal overflow.
  - Evidence: `npm run types:check`, `npm run lint:check`,
    `npm run format:check`, dan `npm run build` lulus. Browser pada
    `/system/users` memverifikasi light dengan card
    `oklch(1 0 0)` dan header tabel `oklch(0.97 0 0)`. Mode dark
    memverifikasi card `oklch(0.1782 0 0)` dan header tabel
    `oklch(0.156816 0 0)`. Viewport desktop `1364x637` dan mobile `500x637`
    tidak memiliki horizontal overflow; console hanya berisi pesan debug HMR
    tanpa error.
- [x] Checklist ditinjau kembali setelah implementasi dan browser verification.
  - Kondisi awal: checklist visual belum dapat ditutup sebelum implementasi
    dan pemeriksaan browser selesai.
  - Perubahan: item ini ditinjau setelah component, CSS, build, dan browser
    verification selesai; status visual UserManagement sekarang konsisten
    dengan baseline AccessControl.
  - Alasan: task hanya boleh selesai setelah bukti implementasi dan runtime
    tersedia untuk direview tim.
  - Evidence: `git diff --check` lulus; snapshot accessibility menampilkan
    summary card, tabel user, shortcut, dan aksi user; browser light/dark/mobile
    lulus tanpa error console.

- [x] Warning Pest pada unit test application ditutup.
  - Kondisi awal: `composer ci:check` lulus tetapi Pest melaporkan satu warning
    tanpa detail pada `tests/Unit/UserManagementApplicationTest.php`.
  - Perubahan: menghapus `use Mockery;` dari file test tanpa namespace karena
    PHP 8.4 menganggap import tersebut tidak efektif; blank line setelah import
    ditambahkan agar Pint tetap lulus.
  - Alasan: quality gate tidak boleh menyisakan warning yang dapat menutupi
    masalah test berikutnya.
  - Evidence: focused Pest lulus 8 test/21 assertion dengan `--fail-on-warning`;
    `composer ci:check` lulus 150 test/577 assertion tanpa warning.

## Task 10 — Quality checkpoint UserManagement

- [x] Scope quality checkpoint selesai.
  - Kondisi awal: implementasi UserManagement sudah memiliki module identity,
    authorization, persistence, route, backend test, frontend vertical slice,
    impersonation, seeder dummy, dan baseline styling, tetapi status pada
    beberapa dokumen belum sama dengan hasil implementasi terakhir.
  - File ditinjau: `README.md`, `specification.md`,
    `implementation-plan.md`, `tasks.md`, `planning/execution-log.md`, serta
    source backend dan frontend UserManagement yang dirujuk oleh dokumen.
  - Perubahan: menyelaraskan status Task 10, menegaskan scope yang sudah
    diverifikasi, menambahkan execution log rinci, dan memastikan tidak ada
    karakter mojibake pada dokumen checkpoint.
  - Alasan: dokumentasi harus dapat dipakai untuk review dan rollback tanpa
    bergantung pada riwayat percakapan.
  - Acceptance: status dokumen konsisten; scope yang belum dibuat tidak
    diklaim selesai; setiap evidence menyebut path, command, dan hasil.
  - Evidence: `module:validate` dan `module:inspect` menghasilkan success;
    focused Pest lulus 30 test/163 assertion; `npm run types:check`,
    `npm run lint:check`, dan `npm run format:check` lulus; browser membuka
    `/system/users`, dialog tambah user dapat dibuka dan ditutup, console tidak
    memiliki error/warning; Lighthouse mobile menghasilkan Accessibility 100,
    Best Practices 100, SEO 100, dan Agentic Browsing 100.
  - Batasan: UI mutation umum untuk edit, status, delete, dan role assignment
    belum termasuk scope selesai. Migrasi shared/production belum dijalankan
    dari workspace karena membutuhkan database nyata dan backup environment.

## Review Checklist Task 10

- [x] Checklist ditinjau sebelum pekerjaan.
  - Scope, dependency AccessControl, acceptance criteria, command verifikasi,
    dan risiko migration sudah dicocokkan dengan dokumen baseline.
- [x] Checklist ditinjau setelah pekerjaan.
  - Semua item yang diverifikasi diberi evidence; scope lanjutan tetap
    dilaporkan sebagai belum selesai dan tidak ditutup secara paksa.
- [x] Positive dan negative path ditinjau.
  - Test module mencakup capability, lifecycle, infrastructure, presentation,
    impersonation, seeder, dan authorization boundary.
- [x] Frontend ditinjau pada browser nyata.
  - Halaman, dialog tambah user, responsive layout, shortcut, protected user,
    console, dan accessibility score sudah diperiksa.
- [x] Dokumentasi downstream diperbarui.
  - `README.md`, `specification.md`, `implementation-plan.md`, `tasks.md`,
    dan `planning/execution-log.md` sekarang menyebut status dan batasan yang
    sama.

## Task 11 — Evaluasi dan penyelarasan UserManagement

- [x] Preflight module dan boundary selesai.
  - Kondisi awal: UserManagement sudah memiliki module valid dengan dependency
    `AccessControl`, tetapi perlu diverifikasi ulang setelah binding
    `RoleAssignmentCapability` diperbaiki.
  - File ditinjau: manifest, provider, controller, policy, action, query,
    repository, migration, seeder, route, test, dan frontend UserManagement.
  - Evidence: `module:validate System/UserManagement --json` dan
    `module:inspect System/UserManagement --json` lulus; dependency hanya
    menunjuk ke public capability AccessControl.

- [x] Evaluasi authorization dan security selesai.
  - Kondisi awal: lifecycle user memiliki beberapa mutation sensitif dan flow
    impersonation yang harus tetap memakai backend sebagai security authority.
  - Perubahan: tidak ditemukan bypass valid pada controller, policy, action,
    atau session adapter. Protected `SuperSystem`, permission, reason
    impersonation, actor restore, dan event redaction sudah memiliki boundary
    serta test.
  - Alasan: evaluasi harus membedakan defect nyata dari keputusan scope yang
    memang sudah disetujui.
  - Evidence: focused UserManagement test suite lulus; contract tidak
    mengimpor private implementation AccessControl; full CI sebelumnya lulus.

- [x] Bug state dialog edit diperbaiki.
  - Kondisi awal: `UserFormDialog` menginisialisasi `useForm` saat `user` masih
    `null`, sehingga dialog edit dapat menampilkan field Nama dan Email kosong.
  - Perubahan: `resources/js/pages/System/UserManagement/pages/Index.tsx`
    memberi `key` berdasarkan mode dan ULID user pada `UserFormDialog`. Setiap
    user atau mode baru sekarang membuat state form yang sesuai.
  - Alasan: data user yang dipilih harus selalu menjadi initial value dialog dan
    tidak boleh membawa state user sebelumnya.
  - Acceptance: edit Alya menampilkan data Alya; setelah ditutup, edit Bima
    menampilkan data Bima; create tetap memiliki form kosong dan password awal.
  - Evidence: browser snapshot dan DOM evaluation mengonfirmasi nilai Alya
    serta Bima; `npm run types:check`, `npm run lint:check`, dan
    `npm run format:check` lulus.

- [x] Browser dan quality checkpoint ditutup untuk scope saat ini.
  - Kondisi awal: dokumentasi masih menyebut seluruh mutation UI umum belum
    tersedia, padahal create, edit, detail, dan impersonation sudah ada.
  - Perubahan: status README, specification, implementation plan, tasks, dan
    execution log diselaraskan. UI status, delete, dan role assignment tetap
    ditulis sebagai scope lanjutan karena belum tersedia pada UserTable.
  - Evidence: `/system/users` dapat dibuka; create/edit/detail/impersonation
    terlihat pada browser; protected user tidak menampilkan edit atau
    impersonate; console tidak memiliki error/warning; Lighthouse mobile
    Accessibility, Best Practices, SEO, dan Agentic Browsing masing-masing 100.
  - Batasan: tidak ada implementasi status, delete, atau role assignment UI
    pada Task 11.

- [x] Checklist ditinjau sebelum dan sesudah pekerjaan.
  - Sebelum: scope, dependency AccessControl, acceptance, test, browser flow,
    dan batasan UI dicocokkan dengan dokumen baseline.
  - Sesudah: setiap temuan diberi status dan evidence; fitur yang belum ada
    tidak ditandai selesai; perubahan kode hanya menyentuh bug state dialog.

## Task 12 — Penutupan Open Risk UI UserManagement

- [x] UI perubahan status selesai.
  - Kondisi awal: backend `ChangeUserStatus`, FormRequest, route, dan policy
    sudah ada, tetapi UserTable belum menyediakan action dan dialog status.
  - Perubahan: menambahkan `ChangeUserStatusDialog.tsx`, wiring permission
    `user.status.manage`, route Ziggy `system.users.status`, dan guard agar
    `SuperSystem` tidak dapat dipilih.
  - Evidence: dialog status terbuka pada browser untuk user biasa; submit
    backend memiliki test lifecycle; TypeScript, ESLint, dan Prettier lulus.

- [x] UI soft delete selesai.
  - Kondisi awal: backend soft delete sudah ada, tetapi belum ada confirmation
    dialog dan action pada tabel.
  - Perubahan: menambahkan `DeleteUserDialog.tsx`, visibility permission
    `user.delete`, route Ziggy `system.users.destroy`, dan confirmation sebelum
    mutation.
  - Evidence: dialog arsip terbuka pada browser; tombol tidak tersedia untuk
    protected user; backend test tetap lulus; tidak ada data development yang
    dihapus saat browser review.

- [x] Public role catalog dan role assignment selesai.
  - Kondisi awal: `RoleAssignmentCapability` sudah dapat melakukan assignment,
    tetapi UserManagement belum memiliki route, FormRequest, role catalog, atau
    UI assignment.
  - Perubahan: AccessControl menambahkan `RoleCatalogCapability`, `RoleOption`,
    dan `SpatieRoleCatalogAdapter`. UserManagement menambahkan
    `AssignUserRoleRequest`, route `system.users.roles`, controller orchestration,
    typed `roles` page prop, dan `RoleAssignmentDialog` dengan pencarian role.
  - Alasan: module UserManagement tetap memakai public API AccessControl tanpa
    mengimpor model atau adapter Spatie.
  - Evidence: test positive assignment, negative permission denial, dan role
    catalog lulus; role picker dan search role terbuka pada browser; actor
    biasa tidak melihat `SuperSystem` pada picker.

- [x] Security dan frontend quality checkpoint ditutup.
  - Kondisi awal: Open Risk Task 08A menyatakan status/delete/role assignment
    belum tersedia pada UI.
  - Perubahan: Open Risk ditutup setelah vertical slice backend/frontend,
    protected guard, permission visibility, dan browser evidence tersedia.
  - Evidence: `composer ci:check` lulus dengan 171 test dan 646 assertion;
    `module:validate` dan `module:inspect` lulus; console browser bersih;
    Lighthouse mobile Accessibility, Best Practices, SEO, dan Agentic Browsing
    masing-masing 100; `git diff --check` lulus.
  - Batasan: status soft delete tetap membutuhkan prosedur restore terpisah
    jika fitur restore diminta kemudian.

- [x] Checklist ditinjau sebelum dan sesudah pekerjaan.
  - Sebelum: status, delete, dan role assignment dipecah menjadi increment
    terurut dengan acceptance dan negative path.
  - Sesudah: seluruh item Open Risk memiliki file perubahan dan evidence;
    tidak ada checklist terbuka pada folder UserManagement.

## Task 13 — Pencatatan Scope Lanjutan

- [x] Scope lanjutan dicatat pada dokumen UserManagement.
  - Kondisi awal: Task 12 sudah menutup vertical slice status, soft delete, dan
    role assignment, tetapi pekerjaan lanjutan belum memiliki daftar scope,
    batasan, acceptance awal, dan bukti yang harus disiapkan.
  - Perubahan: memperbarui `README.md`, `specification.md`,
    `implementation-plan.md`, `migration-runbook.md`, dan execution log dengan
    empat backlog resmi: restore user, invitation email, role revoke atau
    multi-role management, dan migration shared/production. AuditLog consumer
    ditutup oleh increment System/AuditLog.
  - Alasan: tim perlu membedakan fitur yang sudah selesai dari fitur yang baru
    direncanakan agar implementasi, review, dan rollback dapat ditelusuri.
  - Evidence: setiap scope memiliki batasan dan bukti minimum sebelum coding;
    migration production dicatat sebagai release gate eksternal.

- [ ] Restore user belum dibuat.
  - Syarat selesai: action, policy, permission, audit event, aturan
    `SuperSystem`, positive/negative test, dan browser flow tersedia.

- [x] Invitation email selesai.
  - Kondisi awal: belum ada UI atau delivery email untuk membuat user tanpa password manual.
  - Perubahan: `InviteUser`, route Ziggy, permission `user.invite`, dialog invitation, dan setting SMTP terenkripsi ditambahkan. Password SMTP dimasking pada UI serta teredaksi pada audit.
  - Alasan: user menetapkan password sendiri melalui token password-reset Laravel yang sekali pakai dan berakhir dalam 60 menit.
  - Evidence: test notification positif/permission negatif lulus; browser SuperSystem mengirim invitation dan MailHog menerima email tujuan uji.

- [ ] Role revoke atau multi-role management belum dibuat.
  - Syarat selesai: public contract, operasi atomik, protected-role guard,
    audit, positive/negative test, dan browser flow tersedia.

- [x] AuditLog consumer synchronous sudah dibuat.
  - Kondisi awal: lifecycle UserManagement belum memiliki Integration Event dan
    persistence audit lintas module.
  - Perubahan: `UserManagementActivityOccurred` version 1 diterbitkan oleh
    mutation lifecycle, role, dan impersonation. AuditLog mengonsumsi event
    secara synchronous, idempotent, ter-redaksi, serta fail-closed.
  - Alasan: consumer nyata sudah tersedia. Retry/monitoring queue tidak berlaku
    pada mode synchronous; perubahan ke async memerlukan ADR baru.
  - Evidence: `AuditLogIntegrationEventTest` membuktikan envelope, persistence,
    correlation impersonation, unsupported version, dan rollback saat audit
    gagal.

- [ ] Migration shared/production belum dilakukan.
  - Syarat selesai: rehearsal, backup/restore test, lock/downtime check,
    approval operator, dan rollback evidence tersedia pada environment target.

- [x] Checklist ditinjau sebelum dan sesudah pencatatan scope.
  - Sebelum: lima item dipastikan berasal dari scope yang belum tersedia, bukan
    dari fitur yang sudah ditutup Task 12.
  - Sesudah: lima item tetap terbuka dengan acceptance awal; tidak ada item
    backlog yang ditandai selesai hanya karena dokumentasinya sudah dibuat.

## Revision History

| Version | Date       | Description                                             |
| ------- | ---------- | ------------------------------------------------------- |
| 1.7     | 2026-08-06 | Menyelesaikan frontend vertical slice dan browser verification Task 08 |
| 1.8     | 2026-08-06 | Menyelesaikan impersonation session, audit event, dan browser flow Task 09 |
| 1.9     | 2026-08-06 | Menutup final quality checkpoint dan memperbaiki contrast tabel |
| 2.0     | 2026-08-06 | Menambahkan seeder dummy UserManagement dan test idempotency |
| 2.1     | 2026-08-06 | Menyamakan baseline warna module dengan AccessControl |
| 2.2     | 2026-08-06 | Menutup warning Pest pada unit test application |
| 2.3     | 2026-08-06 | Menutup Task 10 quality checkpoint dan menyelaraskan evidence dokumentasi |
| 1.1     | 2026-08-06 | Menetapkan keputusan scope dan status task siap dimulai |
| 2.4     | 2026-08-06 | Mencatat lima scope lanjutan yang belum dibuat |
| 2.5     | 2026-08-06 | Menutup AuditLog consumer synchronous dan menyisakan empat scope lanjutan |
