# Tasks: AccessControl Module

Setiap task harus kecil, dapat diverifikasi, dan tidak mencampur capability
UserManagement, AuditLog, atau SystemSetting.

## Task 01 — Namespace dan boundary module

**Tujuan:** menetapkan `System/AccessControl` sebagai module authorization
baseline dengan namespace yang konsisten.

**Files:** folder `docs/Projects/06.System/AccessControl/`, ADR namespace,
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

**Hasil implementasi:** Selesai pada 2026-08-05. `permissions.php` berisi empat
permission AccessControl dengan owner, format key, dan metadata `sensitive`.

- [x] Scope task selesai.
  - Kondisi awal: `permissions.php` masih kosong dari generator.
  - Perubahan: menambahkan empat permission dan focused permission test.
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
  - Batasan: eksekusi pada database shared environment tetap menunggu environment,
    backup, downtime, dan approval release.

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
- [x] Discovery, validation, list, dan test lulus.
- [x] Forbidden dependency dan sensitive output scan bersih.
- [ ] Frontend role/permission page, state UI, dan browser/accessibility test
  tersedia.

Rincian frontend ada di [Frontend AccessControl](frontend/README.md). Task
frontend dikerjakan setelah dokumentasi frontend disetujui dan sebelum module
AccessControl dinyatakan selesai.

**Hasil implementasi:** Backend integration dan quality gate selesai pada
2026-08-05. Frontend role/permission belum selesai.

- [x] Scope task selesai.
  - Kondisi awal: middleware, policy, use case re-check, dan shared Inertia
    authorization context belum tersedia.
  - Perubahan: menambahkan `AccessControlPolicy`, `RoleController` dengan
    `can:*` middleware, `AuthorizeRoleMutation`, helper authorization pada
    `User`, dan props `auth.roles`, `auth.permissions`, `auth.superSystem`.
  - Alasan: backend harus tetap menjadi security authority; context frontend
    hanya dipakai untuk visibility dan UX.
  - Evidence: policy/context test lulus dengan 8 test dan 24 assertion; full
    suite lulus dengan 97 test dan 305 assertion; Pint, discovery, validation,
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

## Final quality checkpoint

- [x] Inventory sebelum perubahan tersedia.
- [x] Positive dan negative test tersedia untuk identity dan schema dasar.
- [x] Authorization, security, audit, dan dependency impact ditinjau.
- [x] Module discovery/validation/list lulus sebelum perubahan runtime.
- [x] Documentation dan execution evidence diperbarui.
- [x] Open risk migration upgrade baseline lokal ditutup dan risiko deployment
  existing dikendalikan melalui runbook release.
