# Implementation Plan: Remediasi Kode dan Dokumentasi System

## Status

Status: `Partially Executed`.

Open risk operasional yang tidak bergantung pada keputusan arsitektur sudah
dikerjakan. Keputusan dynamic module bootstrap dan consumer-owned
runtime-setting port diterima pada 14 Agustus 2026. Increment berikutnya tetap
mengikuti prerequisite dan checkpoint pada task checklist.

## Kondisi Awal

- Project merupakan existing starter kit sekaligus module extension berbasis
  Laravel 13, PHP 8.4, React, Inertia, TypeScript, Vite, Tailwind, Ziggy, Spatie
  Permission, MySQL, dan Redis.
- Module aktif: AccessControl, UserManagement, AuditLog, dan SystemSetting.
- `composer ci:check` terbaru lulus dengan 302 test dan 1.404 assertion.
- `npm run build`, module discovery, module validation, dependency audit, dan
  pemeriksaan link dokumentasi lulus.
- Route `/api/v1` yang tersedia baru empat: dua AuditLog dan dua SystemSetting.
- MCP `chrome-devtools` sudah dapat mengendalikan context test terisolasi.
  Verifikasi read-only empat module, desktop/mobile, light/dark, role positif
  dan negatif, console, network list, accessibility tree, serta trace awal
  sudah dilakukan.
- Rehearsal MySQL disposable dan perbaikan exact constraint Composer sudah
  lulus. Upgrade path historis dan automated browser/security gate belum ada.
- Working tree sudah memiliki perubahan user pada
  `tests/Feature/UserManagementPresentationTest.php`. Perubahan tersebut bukan
  bagian dari rencana dan wajib dipertahankan.

## Preflight dan Traceability

| Item | Acuan dan kondisi |
|---|---|
| Authoritative source | `AGENTS.md`, `docs/AGENTS.md`, `docs/README.md`, baseline requirement, system design, API spec, security design, coding standard, module guide, technical spec, test plan, CI/CD, dan specification setiap module |
| Lifecycle panduan lama | User memutuskan panduan prompt lama tetap dihapus; root `AGENTS.md` tidak lagi mewajibkan `docs/AI-PROMPT-GUIDE.md` |
| Downstream docs | README utama, specification UserManagement/SystemSetting, API implementation spec, ADR module bootstrap, ADR dependency inversion, ModuleEvaluation, CI/CD, task, dan execution log |
| Existing code | Output SystemSetting, repository encrypted setting, ModuleRegistry, `bootstrap/providers.php`, manifest empat module, public contract SystemSetting, Application AccessControl, route API, dan workflow GitHub Actions |
| Golden structure | DDD-lite Modular Monolith; `packages/StarterKit` untuk framework; module di `app/Modules/{Domain}/{SubModule}`; ULID; dependency di `module.json`; API melalui contract/DTO/resource; frontend melalui Ziggy |
| Dependency order | Framework -> AccessControl -> UserManagement -> AuditLog -> SystemSetting |
| Acceptance global | Tidak ada secret pada output; module invalid terisolasi; graph dependency acyclic; API baseline tersedia; gate CI dan browser lulus; dokumentasi sinkron |
| Rollback trace | Setiap increment mencatat file sebelum/sesudah, command, hasil, dan risiko. Commit hanya dibuat jika user meminta secara eksplisit |

## Keputusan Arsitektur yang Diterima

Keputusan bootstrap dan runtime-setting port sudah diterima user dan dicatat
pada [ADR-0001](decisions/ADR-0001-DYNAMIC-MODULE-RUNTIME-BOOTSTRAP.md) serta
[ADR-0002](decisions/ADR-0002-CONSUMER-OWNED-RUNTIME-SETTING-PORT.md).

1. `bootstrap/providers.php` hanya mendaftarkan provider framework yang stabil.
   Provider tersebut memakai ModuleRegistry untuk mendaftarkan module valid dan
   enabled berdasarkan urutan dependency.
2. UserManagement dan AuditLog memiliki runtime-setting port sendiri. Adapter
   SystemSetting mengimplementasikan port tersebut karena SystemSetting berada
   setelah kedua consumer pada dependency order.
3. Nilai sensitif tidak pernah menjadi bagian response publik atau output CLI.
   Response hanya membawa status seperti `has_value`, `source`, dan timestamp
   bila diperlukan.
4. Application AccessControl memakai repository/read contract. Eloquent model
   tetap berada pada Infrastructure.
5. Endpoint API dibangun per vertical slice setelah payload, authorization,
   idempotency, error envelope, dan resource transformer tertulis pada spec.
6. Quality gate CI dipisahkan menjadi job yang dapat diaudit: backend,
   frontend, database, browser/accessibility, security, dan artifact.

## Dependency Graph Pekerjaan

```text
INC-00 Governance dan ADR
  |-- INC-01 Redaksi secret
  |     `-- INC-02 Safe default ciphertext
  |
  |-- INC-03 Validasi graph module
  |     `-- INC-04 Runtime bootstrap
  |           `-- INC-05 Dependency inversion
  |                 `-- INC-06 Boundary AccessControl
  |
  `-- INC-07 Contract API
        |-- INC-08 API UserManagement
        `-- INC-09 API AccessControl dan impersonation

INC-01..09 stabil
  `-- INC-10 Gate CI backend dan database
        `-- INC-11 Gate frontend, browser, accessibility, dan security
              `-- INC-12 Sinkronisasi akhir dan release evidence
```

## Urutan Increment

### INC-00 - Kunci Baseline, Specification, dan ADR

**Kondisi awal:** Ada dokumentasi yang hilang atau stale. Keputusan bootstrap,
dependency inversion, dan schema API belum dicatat secara authoritative.

**Perubahan yang direncanakan:**

- pertahankan penghapusan panduan prompt lama dan hapus kewajibannya dari
  root `AGENTS.md`;
- selaraskan constraint PHP, status scope UserManagement, dan status evaluasi;
- tulis ADR accepted untuk runtime module bootstrap dan consumer-owned port;
- tulis payload serta error contract API pada specification module;
- buat matriks traceability temuan -> task -> test -> dokumen.

**Acceptance:** Tidak ada konflik authoritative yang belum diputuskan sebelum
coding. Semua public contract yang akan berubah memiliki specification dan
rollback trace.

**Verifikasi:** link checker dokumentasi, pencarian istilah stale, dan review
manual ADR/specification.

### INC-01 - Tutup Kebocoran Nilai Sensitif

**Kondisi awal:** CLI get, CLI set JSON, dan API update dapat membawa nilai
hasil decrypt sampai ke output.

**Perubahan yang direncanakan:**

- buat test merah untuk secret pada output CLI dan API;
- tambahkan presenter/DTO publik yang mengetahui metadata `sensitive`;
- redaksi output get/set serta response update tanpa mengubah typed reader
  internal;
- pertahankan output nilai non-sensitive untuk kompatibilitas yang disetujui;
- untuk setting sensitif, tolak process argument dan gunakan prompt tersembunyi
  atau `--value-stdin`; contract ini disetujui user pada 14 Agustus 2026.

**Acceptance:** Dummy secret tidak muncul pada stdout, stderr, JSON response,
validation error, audit metadata, dan log. Test non-sensitive tetap lulus.

**Rollback:** Revert file presenter/resource/command dalam scope increment.
Jangan mengembalikan serialisasi raw value sebagai fallback.

### INC-02 - Safe Default untuk Ciphertext Rusak

**Kondisi awal:** `DecryptException` tidak dibungkus sebagai
`SettingStorageUnavailable`, sehingga reader tidak selalu memakai default aman.

**Perubahan yang direncanakan:**

- test corrupted ciphertext dan key mismatch tanpa mencetak data terenkripsi;
- tangkap exception enkripsi pada repository dan bungkus sebagai diagnostic
  domain yang aman;
- pastikan single read serta many read menghasilkan default registry;
- log hanya `setting_key` dan tipe failure.

**Acceptance:** Ciphertext invalid tidak menghasilkan 500 atau secret leak.
Reader mengembalikan `source=default` dan diagnostic aman.

### Checkpoint A - Security

- focused positive dan negative test SystemSetting lulus;
- `composer ci:check` tetap hijau;
- audit manual memastikan dummy secret tidak ada pada output test/log;
- INC-03 belum dimulai sebelum checkpoint ini lulus.

### INC-03 - Validasi Dependency Graph Module

**Kondisi awal:** ModuleRegistry memvalidasi manifest dasar, tetapi belum
menjadi authority dependency existence, cycle, enabled state, dan urutan boot.

**Perubahan yang direncanakan:**

- tambah fixture serta test untuk dependency hilang, cycle, disabled module,
  provider invalid, dan topological order;
- pisahkan diagnostic dari registration side effect;
- pastikan command discovery/validate/inspect memakai hasil graph yang sama.

**Acceptance:** Graph valid menghasilkan urutan deterministik. Module invalid
atau cycle menghasilkan diagnostic stabil tanpa menghentikan pemeriksaan module
valid lain.

### INC-04 - Hubungkan Registry ke Runtime Bootstrap

**Kondisi awal:** Empat provider module didaftarkan statis pada
`bootstrap/providers.php`; status manifest tidak mengontrol runtime.

**Perubahan yang direncanakan:**

- test bootstrap untuk module enabled, disabled, invalid, dan provider gagal;
- buat bootstrapper framework yang hanya mendaftarkan provider valid/enabled;
- ganti provider module statis dengan satu composition provider;
- verifikasi `config:cache`, `route:cache`, console, HTTP, dan seeder.

**Acceptance:** Module invalid/disabled tidak boot. Module valid lain tetap
tersedia dan diagnostic tidak membocorkan path atau data sensitif.

### INC-05 - Hilangkan Dependency Tersembunyi dan Siklus

**Kondisi awal:** UserManagement dan AuditLog mengimpor contract SystemSetting,
tetapi manifest keduanya tidak mencatat dependency tersebut. Menambah dependency
langsung akan membuat arah terbalik atau cycle.

**Perubahan yang direncanakan:**

- buat `UserRuntimeSettings` pada public boundary UserManagement;
- buat `AuditRuntimeSettings` pada public boundary AuditLog;
- sediakan default adapter di module consumer;
- sediakan adapter SystemSetting sesuai dependency order;
- tambah architecture test yang membandingkan import lintas module dengan
  `module.json` dan menolak cycle.

**Acceptance:** Tidak ada import UserManagement/AuditLog ke SystemSetting.
Manifest tetap acyclic dan aplikasi masih memakai runtime setting yang sama
ketika SystemSetting enabled, serta default aman ketika tidak tersedia.

### INC-06 - Pulihkan Boundary Application AccessControl

**Kondisi awal:** Action dan Query Application memakai Eloquent `Role` serta
`Permission` secara langsung.

**Perubahan yang direncanakan:**

- definisikan role repository, permission catalog, dan typed read DTO;
- implementasikan Eloquent adapter pada Infrastructure;
- refactor mutation serta dashboard query secara terpisah;
- tambah architecture test yang melarang import Infrastructure dari
  Application.

**Acceptance:** Behavior create/delete/sync/dashboard tidak berubah. Application
AccessControl tidak mengimpor Eloquent model dan positive/negative authorization
test tetap lulus.

### Checkpoint B - Arsitektur Module

- discovery, validate, list, dan inspect empat module lulus;
- test invalid/disabled bootstrap lulus;
- dependency graph tidak memiliki cycle atau hidden import;
- focused suite AccessControl, UserManagement, AuditLog, dan SystemSetting lulus.

### INC-07 - Fondasi Contract API

**Kondisi awal:** Response yang tersedia belum konsisten dengan envelope
`success`, `message`, `data`, `meta`; implementation spec user/role/permission
belum lengkap.

**Perubahan yang direncanakan:**

- kunci schema request, resource, pagination, error code, authorization,
  idempotency, dan rate limit di specification;
- buat response factory/transformer reusable tanpa menaruh business rule pada
  controller;
- gunakan capability idempotency public `packages/StarterKit` sesuai ADR-0003;
  SystemSetting hanya memasang adapter persistence dan policy retention/rate;
- buat contract test success dan error envelope;
- pertahankan web session sebagai authentication authority sesuai baseline.

**Acceptance:** Semua API baru dan lama memakai envelope canonical. Error tidak
membocorkan exception, resource existence, atau secret.

### INC-08 - Vertical Slice API UserManagement

Implementasi dipecah menjadi read slice dan mutation slice:

1. list serta detail user dengan scope, filter, pagination, dan resource;
2. create, update/status, serta soft delete dengan FormRequest dan Action yang
   sudah ada;
3. negative test untuk guest, permission, scope, protected user, validation,
   rate limit, dan idempotency.

**Acceptance:** Lima endpoint user baseline tersedia dan memakai ULID,
authorization backend, canonical envelope, serta audit untuk mutation sensitif.

### INC-09 - Vertical Slice API AccessControl dan Impersonation

Implementasi dipecah menjadi:

1. list/create/update role dan list permission;
2. assign/revoke role serta assign/revoke direct permission;
3. start/end impersonation dengan reason, protected-target rule, actor restore,
   dan audit;
4. contract test route matrix lengkap.

**Acceptance:** Seluruh endpoint pada baseline API matrix tersedia. Tidak ada
controller yang menjalankan query Eloquent atau business rule langsung.

### Checkpoint C - Contract API

- `php artisan route:list --path=api/v1 --json` cocok dengan baseline matrix;
- positive/negative API suite lulus;
- response schema snapshot/contract test lulus;
- browser network inspection tidak menemukan 4xx/5xx yang tidak diharapkan.

### INC-10 - Quality Gate CI Backend dan Database

**Perubahan yang direncanakan:**

- gunakan Composer/npm lock file secara deterministik;
- aktifkan coverage backend dan threshold sesuai test plan;
- masukkan source `packages/StarterKit` ke static analysis;
- tambah job MySQL fresh migration, global seeder, rollback, dan upgrade path;
- simpan report test, coverage, dan schema sebagai artifact aman.

**Acceptance:** Workflow gagal bila coverage, static analysis, fresh install,
relation, idempotency, global seeder, atau upgrade migration gagal. Rehearsal
tidak pernah memakai database default developer.

**Evidence parsial:** Rehearsal lokal memakai database baru bernama unik dan
lulus fresh/seed, seed ulang idempotent, rollback, serta migrate/seed ulang.
Database test dihapus setelah selesai. Upgrade dari snapshot release lama dan
job CI tetap terbuka.

### INC-11 - Gate Frontend, Browser, Accessibility, dan Security

**Perubahan yang direncanakan:**

- tambah Vitest untuk logic/component frontend yang penting;
- tambah Playwright dan axe-core untuk critical flow empat module;
- tambah CodeQL serta dependency/security scan;
- gunakan MCP `chrome-devtools` pada context Chrome test terisolasi;
- uji light/dark, desktop/mobile, keyboard, focus, loading, empty, error,
  mutation loading, toast, console, network, dan accessibility tree.

**Acceptance:** Browser nyata memiliki console bersih, request utama sukses,
interactive control memiliki accessible name, dan critical flow lulus pada CI.

**Evidence parsial:** Koneksi MCP dan pemeriksaan read-only sudah lulus. Temuan
SSR aktif tanpa server diperbaiki menjadi opt-in; LCP login menjadi 1,053 detik,
TTFB 575 ms, dan CLS 0. Mutation, empty/error, focus lengkap, Playwright/axe,
dan CI browser tetap menjadi task terpisah.

### INC-12 - Dependency Policy, Dokumentasi, dan Release Evidence

**Perubahan yang direncanakan:**

- review exact-version constraint Composer; gunakan compatible range dengan
  lock file bila disetujui, atau catat pengecualian melalui ADR;
- jalankan seluruh gate release;
- sinkronkan README/specification/task/execution log berdasarkan hasil nyata;
- tutup checklist hanya dengan evidence command dan hasil;
- catat risiko production yang tidak dapat dibuktikan dari workspace.

**Acceptance:** `composer validate --strict` bersih atau pengecualian memiliki
ADR yang disetujui. Tidak ada broken link, stale status, forbidden dependency,
atau open risk tanpa owner.

**Evidence parsial:** Empat exact pin sudah menjadi caret range berbatas major.
`composer update --lock --no-install --no-scripts` tidak mengubah versi package,
strict validation bersih, dan audit dependency tidak menemukan advisory.

## Strategi Verifikasi Global

```text
php artisan module:discover --json
php artisan module:validate --json
php artisan module:list --json
php artisan module:inspect System/AccessControl --json
php artisan module:inspect System/UserManagement --json
php artisan module:inspect System/AuditLog --json
php artisan module:inspect System/SystemSetting --json
php artisan route:list --path=api/v1 --json
composer validate --strict
composer audit --locked
composer ci:check
npm audit --omit=dev --audit-level=high
npm run build
git diff --check
```

Migration verification dijalankan pada service MySQL CI atau database lokal
yang nama serta ownership-nya sudah dipastikan khusus untuk test. Command
destruktif tidak dijalankan pada database default.

## Risiko dan Mitigasi

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Redaksi mengubah contract consumer | Client membaca value lama dan gagal | Kunci contract dahulu, tambah contract test, sediakan metadata pengganti tanpa raw secret |
| Dynamic bootstrap gagal terlalu awal | Aplikasi atau Artisan tidak dapat boot | Buat bootstrap test sebelum mengganti provider statis; registrasi framework provider tetap minimal |
| Dependency inversion mengubah nilai runtime | Pagination/mail memakai default yang salah | Consumer-owned default, adapter parity test, dan test saat SystemSetting disabled |
| Refactor repository mengubah authorization | Mutation role menjadi terlalu longgar/ketat | Characterization test sebelum refactor dan negative permission test |
| API matrix memperluas attack surface | Data atau mutation terpapar | Policy/capability backend, FormRequest, rate limit, idempotency, audit, dan scope test |
| Migration rehearsal merusak data lokal | Kehilangan data | Hanya service container/dedicated test database; verifikasi target sebelum fresh migration |
| Flow browser belum lengkap | Mutation, empty/error, dan focus belum terbukti penuh | Koneksi MCP sudah pulih; lanjutkan fixture disposable, manual flow, lalu Playwright/axe |
| SSR aktif tanpa proses SSR | Initial page menunggu sekitar dua detik | SSR dibuat opt-in dan diuji; aktifkan hanya bersama proses SSR yang dikelola |
| Dependency range terlalu lebar | Update tidak terkontrol | Batas major/minor yang disetujui, lock file, audit, full CI, dan changelog |

## Definition of Ready untuk Implementasi

- [x] User menyetujui dynamic bootstrap dan consumer-owned runtime-setting
  port pada 14 Agustus 2026.
- [x] ADR bootstrap dan dependency inversion berstatus accepted.
- [ ] API implementation specification telah memiliki payload dan error schema.
- [x] Working tree serta perubahan user telah diinventarisasi ulang.
- [x] Test fokus dan rollback trace untuk increment pertama sudah ditentukan.
- [x] Tidak ada blocker authoritative document yang belum diputuskan.

## Definition of Done Program

- [ ] Seluruh temuan critical, high, dan medium memiliki test pencegah regresi.
- [ ] Positive dan negative test tiap increment lulus.
- [ ] Module discovery, validation, inspect, dan runtime isolation lulus.
- [ ] API route serta response contract cocok dengan specification.
- [ ] Fresh/upgrade migration lulus pada MySQL terisolasi.
- [ ] CI backend, frontend, browser, accessibility, dan security lulus.
- [ ] Browser nyata diverifikasi dengan console dan network bersih.
- [ ] Dokumentasi authoritative dan downstream sinkron.
- [ ] Checklist serta execution log mencatat evidence nyata, bukan rencana.
- [ ] Risiko tersisa memiliki owner, batasan, dan keputusan eksplisit.
