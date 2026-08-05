# Tasks: System/UserManagement

Status pekerjaan: `Ready for Task 05`. Checklist belum boleh ditandai selesai sebelum
ada perubahan file dan evidence verifikasi.

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
dependency manifest, public role-assignment contract, dan focused test sudah
tersedia. Adapter/implementasi role assignment belum dibuat dan menjadi bagian
Task 05. `composer types:check` lulus dengan PHPStan tanpa error.

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

- [ ] List/detail query memakai DTO typed.
- [ ] Create/update/status/soft delete action tervalidasi.
- [ ] Role assignment hanya melalui public AccessControl contract.
- [ ] Impersonation membutuhkan permission dan reason.
- [ ] Actor asli dan target dipisahkan pada session contract.

**Verification:** application feature test dan authorization negative test.

**Hasil implementasi:** belum dikerjakan.

## Task 06 — Infrastructure dan migration

**Tujuan:** menyediakan persistence yang kompatibel dengan tabel `users`
starter kit.

**Files:** migration additive, model/repository, factory, seeder, dan
integration test.

**Acceptance criteria:**

- [ ] Semua identifier tetap ULID.
- [ ] Migration tidak merusak authentication, Passkey, atau 2FA.
- [ ] `deleted_at` dan status hanya ditambahkan setelah keputusan disetujui.
- [ ] Fresh migration dan upgrade verification lulus.
- [ ] Password, token, dan secret tidak masuk output atau log.

**Verification:** migration test, repository test, dan `php artisan migrate:fresh`.

**Hasil implementasi:** belum dikerjakan.

## Task 07 — Presentation dan authorization

**Tujuan:** menyediakan route backend yang aman dan controller tipis.

**Files:** `Presentation/Controllers`, `Policies`, `Requests`, `Resources`,
  `Routes`, dan feature test.

**Acceptance criteria:**

- [ ] Middleware coarse-grained diterapkan.
- [ ] Policy menangani resource, scope, state, dan protected `SuperSystem`.
- [ ] FormRequest memvalidasi input sebelum side effect.
- [ ] Controller tidak memiliki query Eloquent atau business rule.
- [ ] Unauthorized actor menerima denial yang konsisten.

**Verification:** route, policy, feature, and forbidden response tests.

**Hasil implementasi:** belum dikerjakan.

## Task 08 — Frontend vertical slice

**Tujuan:** menyediakan UI UserManagement yang dapat ditinjau langsung.

**Files:** `resources/js/pages/System/UserManagement`, route Ziggy, shared
  props, component test, dan browser test.

**Acceptance criteria:**

- [ ] Page list user memakai System dashboard baseline.
- [ ] Loading, empty, error, dan unauthorized state tersedia.
- [ ] Permission visibility hanya untuk UX.
- [ ] Route frontend seluruhnya memakai Ziggy.
- [ ] Responsive, keyboard, dark/light, dan accessibility ditinjau.
- [ ] Browser test membuktikan request sampai response backend.

**Verification:** ESLint, Prettier, TypeScript, build, browser, dan axe-core.

**Hasil implementasi:** belum dikerjakan.

## Task 09 — Impersonation dan audit boundary

**Tujuan:** mengaktifkan impersonation secara aman setelah keputusan session dan
  audit disetujui.

**Files:** action, contract, policy, session adapter, event, audit boundary,
  UI control, dan security test.

**Acceptance criteria:**

- [ ] Permission dan reason wajib.
- [ ] Target `SuperSystem` selalu ditolak.
- [ ] Actor asli dapat kembali dengan aman.
- [ ] Session actor asli dan target tidak tertukar.
- [ ] Sensitive event memiliki audit contract tanpa secret atau password.
- [ ] Negative security test dan browser flow tersedia.

**Verification:** security test, session test, browser test, dan audit contract test.

**Hasil implementasi:** ditunda sampai Task 09 memiliki ADR session, audit,
route leave, dan redaction yang disetujui.

## Final Quality Checkpoint

- [ ] Semua task selesai dengan evidence detail.
- [ ] Test positif dan negatif lulus.
- [ ] Discovery, validation, dan list lulus.
- [ ] Migration fresh dan upgrade terverifikasi.
- [ ] PHPStan, Pint, Pest, ESLint, TypeScript, build, browser, dan accessibility lulus.
- [ ] README dan execution evidence diperbarui.
- [ ] Open risk ditutup atau dilaporkan.

## Revision History

| Version | Date | Description |
| --- | --- | --- |
| 1.1 | 2026-08-06 | Menetapkan keputusan scope dan status task siap dimulai |
