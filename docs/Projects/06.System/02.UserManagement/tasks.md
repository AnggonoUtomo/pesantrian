# Tasks: System/UserManagement

Status pekerjaan: `Discovery`. Checklist belum boleh ditandai selesai sebelum
ada perubahan file dan evidence verifikasi.

## Task 01 — Prompt generator, project intake, dan dry-run

**Tujuan:** memastikan target UserManagement berada pada boundary `System`,
belum dimiliki module valid, dan generator menghasilkan preview target yang
sesuai tanpa menulis file.

**Files:** `app/Modules`, `module.json` existing, command module, dan dokumen
baseline generator.

**Acceptance criteria:**

- [ ] Inventory module existing mencatat AccessControl sebagai module valid.
- [ ] `module:inspect System/AccessControl --json` menampilkan detail manifest,
  permission source, runtime config, dependency, dan diagnostic tanpa side effect.
- [ ] Parent boundary target tercatat sebagai `app/Modules/System/`.
- [ ] Target `System/UserManagement` belum duplicate pada name, path, namespace,
  provider, atau permission key.
- [ ] Dry-run tidak menulis file.
- [ ] Output JSON memiliki code `MODULE_PREVIEWED` dan diagnostics yang dapat
  ditindaklanjuti.

**Prompt kerja:**

```text
Lakukan Project Intake dan Existing Module Inventory terlebih dahulu.
Verifikasi module yang sudah ada dengan module:discover, module:validate, dan
module:list. Jangan membuat duplicate module.

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

**Hasil implementasi:** belum dikerjakan.

## Task 02 — Pembuatan skeleton melalui generator

**Tujuan:** membuat skeleton module setelah hasil dry-run disetujui.

**Files:** `module.json`, `module.php`, `ServiceProvider.php`, `README.md`,
  directory canonical, dan route entry point.

**Acceptance criteria:**

- [ ] Command aktual dijalankan dengan `--force --yes --json` setelah dry-run
  disetujui.
- [ ] Namespace `App\\Modules\\System\\UserManagement` valid.
- [ ] Manifest memiliki field wajib dan dependency AccessControl tercatat sesuai
  contract publik.
- [ ] Provider hanya melakukan wiring dan tidak memuat business logic.
- [ ] Discovery, validation, dan list tetap lulus untuk AccessControl dan
  UserManagement.
- [ ] Output memiliki code `MODULE_CREATED`.
- [ ] Generator tidak membuat business logic palsu atau mengambil private
  implementation AccessControl.

**Command:**

```bash
php artisan module:make UserManagement --domain=System --profile=default-v1 --force --yes --json
```

**Hasil yang diharapkan:** module skeleton tersedia pada
`app/Modules/System/UserManagement`, manifest dan structure valid, serta
AccessControl tetap tidak berubah.

**Verification:** `php artisan module:discover --json`,
`php artisan module:validate --json`, dan `php artisan module:list --json`.

**Hasil implementasi:** belum dikerjakan.

## Task 03 — Permission identity dan public contract

**Tujuan:** menetapkan vocabulary permission UserManagement dan boundary
  komunikasi dengan AccessControl.

**Files:** `permissions.php`, `Application/Contracts`, DTO, dan contract test.

**Acceptance criteria:**

- [ ] Permission key unik dan owner-nya UserManagement.
- [ ] Permission `user.impersonate` ditandai sensitive.
- [ ] UserManagement tidak mengimpor private class AccessControl.
- [ ] Contract role assignment memakai public capability atau contract yang
  disetujui.
- [ ] Test positive dan negative untuk permission identity tersedia.

**Verification:** `php artisan module:validate --json` dan focused contract test.

**Hasil implementasi:** menunggu keputusan permission dan contract.

## Task 04 — Domain lifecycle user

**Tujuan:** membuat aturan status, active/inactive, soft delete, dan protected
  user tanpa ketergantungan Eloquent di Domain.

**Files:** `Domain/Contracts`, `Domain/Entities`, `Domain/ValueObjects`,
  `Domain/Exceptions`, dan unit test.

**Acceptance criteria:**

- [ ] Status user memiliki vocabulary dan transisi yang disetujui.
- [ ] `SuperSystem` tidak dapat dinonaktifkan atau dihapus.
- [ ] Domain tidak bergantung pada HTTP, Eloquent, atau UI.
- [ ] Positive, negative, dan boundary test tersedia.

**Verification:** focused domain unit test dan architecture test.

**Hasil implementasi:** menunggu keputusan field status dan soft delete.

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

**Hasil implementasi:** menunggu keputusan impersonation.

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
| 1.0 | 2026-08-06 | Discovery task plan UserManagement |
