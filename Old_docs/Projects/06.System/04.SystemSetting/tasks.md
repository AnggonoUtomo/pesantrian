# Tasks: System/SystemSetting

Setiap task dikerjakan berurutan. Task berikutnya tidak boleh dimulai sebelum
acceptance criteria dan evidence task sebelumnya ditinjau.

## Aturan Sebelum Mulai

- [x] Parent boundary, code path, namespace, owner, dependency, dan non-scope
  sudah ditulis pada specification.
  - Kondisi awal: SystemSetting hanya disebut pada baseline dan belum memiliki
    folder project khusus.
  - Perubahan: boundary `System`, path, namespace, data owner, permission,
    public contract, dependency, dan non-scope ditulis pada `specification.md`.
  - Evidence: specification memiliki bagian Project Intake, Preflight,
    Ownership, Scope, Non-scope, dan Boundaries.
- [x] Project intake dan inventory module existing sudah dicatat.
  - Kondisi awal: AccessControl, UserManagement, dan AuditLog sudah ada.
  - Perubahan: seluruh module diperiksa melalui discover, validate, list, dan
    inspect; target SystemSetting dipastikan belum ada.
  - Evidence: tiga module valid dan target menghasilkan `MODULE_NOT_FOUND`.
- [x] Prompt generator, dry-run, expected output, dan acceptance criteria sudah
  ditinjau.
  - Evidence: dry-run menghasilkan `MODULE_PREVIEWED` tanpa file module baru.
- [x] Dependency task dan checkpoint sudah jelas.
  - Evidence: task di bawah mengikuti urutan dokumentasi, ADR, generator,
    contract, persistence, mutation, presentation, runtime consumer, dan gate.
- [x] ADR-0001 dan ADR-0002 disetujui user.
  - Evidence: user menyatakan seluruh dokumen SystemSetting disetujui dan
    pekerjaan dapat diselesaikan tanpa konfirmasi tambahan.

## Task 01 — Discovery, Preflight, dan Dokumentasi

**Tujuan:** Menetapkan source, kondisi workspace, scope, keputusan, urutan kerja,
dan rollback trace sebelum code dibuat.

**Files:**

- `docs/Projects/06.System/04.SystemSetting/README.md`;
- `specification.md`;
- `implementation-plan.md`;
- `tasks.md`;
- `decisions/ADR-0001-SYSTEMSETTING-BOUNDARY-AND-CONTRACT.md`;
- `decisions/ADR-0002-RUNTIME-ACTIVATION-AND-APPEARANCE.md`;
- `planning/execution-log.md`;
- dokumen baseline langsung terkait.

**Acceptance criteria:**

- [x] Authoritative source dan golden structure tercatat.
- [x] Runtime, package, database, frontend, dan module existing diinventarisasi.
- [x] `module:inspect` target dijalankan sebelum generator.
- [x] Dry-run menghasilkan `MODULE_PREVIEWED` tanpa menulis target.
- [x] Sembilan fondasi enterprise memiliki status dan alasan.
- [x] Schema, setting catalog, route, command, frontend, security, test, dan
  rollback dijelaskan.
- [x] Dua keputusan arsitektur ditulis sebagai ADR `Proposed`.
- [x] Checklist ditinjau sebelum dan setelah dokumentasi.

**Hasil implementasi 6 Agustus 2026:**

- Paket dokumentasi pekerjaan SystemSetting dibuat dengan Bahasa Indonesia
  sederhana.
- Belum ada generator actual, migration, module provider, atau frontend code.
- Coding ditahan sampai ADR disetujui.

**Test:**

```bash
php artisan module:discover --json
php artisan module:validate --json
php artisan module:list --json
php artisan module:inspect System/SystemSetting --json
php artisan module:make SystemSetting --domain=System --profile=default-v1 --dry-run --json
git diff --check
```

**Evidence:**

- Kondisi awal: target module dan folder project belum ada.
- Perubahan: folder dokumentasi bernomor `04.SystemSetting` dibuat.
- Alasan: requirement, dependency, activation, dan cache harus jelas sebelum
  code agar tidak mengulang penyimpangan arsitektur.
- Hasil command: tiga module existing valid; target belum ada; dry-run sukses.
- Risiko/batasan: ADR belum disetujui dan upload aset tetap non-scope.

## Task 02 — Persetujuan ADR dan Penguncian Scope

**Tujuan:** Mendapat keputusan eksplisit user sebelum perubahan module.

**Files:**

- kedua file pada `decisions/`;
- `README.md`, `specification.md`, `implementation-plan.md`, dan task ini.

**Acceptance criteria:**

- [x] User menyetujui arah dependency dan public AuditRecorder.
- [x] User menyetujui DB source of truth serta request memoization.
- [x] User menyetujui global default versus user appearance override.
- [x] User menyetujui asset path lokal tanpa upload package pada increment awal.
- [x] ADR berubah dari `Proposed` menjadi `Accepted` dengan tanggal/evidence.
- [x] Scope, plan, task, dan open risk diselaraskan setelah keputusan.

**Positive test:** ADR accepted dan seluruh dokumen memakai keputusan yang sama.

**Negative test:** coding tidak dimulai saat status masih `Proposed` atau ADR
saling bertentangan.

**Hasil implementasi 6 Agustus 2026:** Kedua ADR diterima. Referensi frontend
hanya dipakai untuk teknik visual; fitur referensi diabaikan.

**Evidence yang wajib dicatat:** keputusan user, file ADR yang berubah, alasan,
dan dampak pada task berikutnya.

## Task 03 — Generator Aktual dan Golden Structure

**Tujuan:** Membuat skeleton canonical melalui generator resmi tanpa menimpa
module existing.

**Files:**

- `app/Modules/System/SystemSetting/**` hasil profile `default-v1`;
- test generator hanya bila output aktual berbeda dari golden structure.

**Command:**

```bash
php artisan module:make SystemSetting --domain=System --profile=default-v1 --force --yes --json
```

**Acceptance criteria:**

- [x] Output memiliki code `MODULE_CREATED`.
- [x] Path dan namespace sesuai specification.
- [x] Manifest, config, permissions, provider, README, route entry point, dan
  folder DDD-lite terbentuk.
- [x] AccessControl, UserManagement, serta AuditLog tidak berubah/tertimpa.
- [x] Generator ulang tanpa mode extension/overwrite aman ditolak.
- [x] Structure snapshot dan manifest test lulus.
- [x] Checklist ditinjau sebelum dan sesudah generator.

**Positive test:** actual generation menghasilkan structure valid.

**Negative test:** duplicate generation tanpa opsi yang benar gagal tanpa
partial write.

**Hasil implementasi 6 Agustus 2026:** Generator menghasilkan
`MODULE_CREATED`; discovery, validate, inspect, dan 17 test command/generator
lulus. Satu failure probe awal tidak reproducible setelah cleanup; dua run
berikutnya lulus dan dicatat untuk observasi.

**Risiko:** `--force` adalah mutasi; target dan status Git wajib diperiksa lagi
tepat sebelum command.

## Task 04 — Runtime Identity, Permission, dan Provider

**Tujuan:** Mengubah skeleton menjadi identity module valid yang dapat ditemukan
dan diinspeksi.

**Files rencana:**

- `module.json`: dependency AccessControl dan AuditLog;
- `module.php`: catalog/config aman tanpa secret;
- `permissions.php`: view/manage sensitive identity;
- `ServiceProvider.php`: binding, migration, route, command, dan policy wiring;
- `bootstrap/providers.php`: registrasi provider bila generator belum mengurusnya;
- `README.md` module: context pack internal.

**Acceptance criteria:**

- [x] Manifest schema valid dan dependency ditemukan.
- [x] Permission key unik secara global.
- [x] Config source tidak memuat secret atau environment call runtime.
- [x] Provider hanya berisi wiring, bukan business logic.
- [x] `module:discover`, `validate`, `list`, dan `inspect` lulus.
- [x] Invalid dependency/duplicate permission memiliki negative test.

**Test:** focused manifest, permission identity, provider boot, dan module
command test.

**Hasil implementasi 6 Agustus 2026:** Manifest bergantung pada AccessControl dan
AuditLog, dua permission sensitif tersedia, config runtime aman, provider
terdaftar setelah AuditLog, dan 21 test manifest/permission/command lulus.

## Task 05 — Definition Registry dan Public Contract

**Tujuan:** Membuat schema/default sebagai source of truth typed sebelum
persistence dan UI.

**Files rencana:**

- `Domain/ValueObjects/SettingKey.php`;
- `Domain/ValueObjects/SettingType.php`;
- `Application/DTO/SettingDefinitionData.php`;
- `Application/DTO/SettingValueData.php`;
- `Application/Contracts/SystemSettingReader.php`;
- `Application/Contracts/SettingDefinitionRegistrar.php`;
- `Application/Services/SettingDefinitionRegistry.php`;
- unit/contract tests.

**Acceptance criteria:**

- [x] Key memakai dot notation dan type termasuk dalam daftar yang didukung.
- [x] Registry memuat seluruh baseline setting pada specification.
- [x] Unknown key, duplicate key, duplicate contract berbeda, type salah, range
  salah, path berbahaya, dan enum tidak dikenal ditolak.
- [x] Default setiap definition selalu lolos validator miliknya.
- [x] Public contract tidak mengembalikan Eloquent model atau mixed payload
  tanpa DTO.
- [x] Test ditulis RED sebelum implementasi dan lulus setelah implementasi.

**Positive test:** baseline catalog terdaftar deterministik.

**Negative test:** duplicate key dan unsafe path menghasilkan exception typed.

**Hasil implementasi 6 Agustus 2026:** `SettingType`, definition/value DTO,
registrar, reader contract, dan registry 13 baseline key tersedia. RED gagal
karena class belum ada; GREEN lulus 9 test/24 assertion.

## Task 06 — Migration, Repository, Reader, dan Safe Default

**Tujuan:** Menyimpan setting di MySQL dan membaca nilai secara konsisten dengan
fallback aman.

**Files rencana:**

- migration `system_settings`;
- migration `idempotency_keys`;
- `Infrastructure/Persistence/Models/SystemSetting.php`;
- `Infrastructure/Persistence/Models/IdempotencyKey.php`;
- `Application/Contracts/SystemSettingRepository.php`;
- `Infrastructure/Persistence/Repositories/EloquentSystemSettingRepository.php`;
- reader implementation dan request memoization;
- persistence/integration tests.

**Acceptance criteria:**

- [x] Semua PK/FK menggunakan ULID.
  - Perubahan: `system_settings.id`, `idempotency_keys.id`, `updated_by`, dan
    `actor_id` memakai ULID.
  - Evidence: focused migration test berhasil membuat kedua tabel.
- [x] Unique/index/referential action sesuai database design.
  - Perubahan: key setting dibuat unik, idempotency memakai kombinasi unik
    actor-endpoint-key, expiry di-index, pembaru memakai `nullOnDelete`, dan
    idempotency actor memakai `cascadeOnDelete`.
  - Evidence: migration berjalan pada seluruh test berbasis `RefreshDatabase`;
    hard delete actor membuat `updated_by` menjadi null tanpa menghapus setting.
- [x] JSON scalar/object dibaca kembali dengan type yang sama.
  - Perubahan: repository melakukan encode/decode JSON secara eksplisit agar
    boolean, integer, string, null, dan object tidak berubah menjadi string.
  - Evidence: round-trip boolean dan object lulus.
- [x] Reader mengembalikan database value valid.
  - Evidence: nilai rate limit 90 dari database mengalahkan default 60.
- [x] Missing/invalid record dan storage failure memakai registry default.
  - Evidence: missing rate limit, RTO di luar range, dan exception storage
    semuanya kembali ke default registry.
- [x] Diagnostic tidak memuat value sensitif, SQL credential, atau stack trace.
  - Perubahan: log hanya memuat `setting_key` dan nama class kegagalan.
  - Evidence: test logger memastikan pesan exception tidak diteruskan.
- [x] Pembacaan berulang dalam satu request memakai memoization.
  - Evidence: dua pembacaan key yang sama hanya memanggil repository satu kali.
- [x] Fresh migration, rollback, relation, dan upgrade path diuji.
  - Evidence: 17 test/45 assertion lulus, termasuk `down()` lalu `up()` migration.
  - Batasan upgrade: ini migration awal untuk module baru sehingga belum ada
    schema SystemSetting lama yang perlu ditransformasi.

**Positive test:** valid persisted value mengalahkan default.

**Negative test:** corrupt type/storage failure memakai safe default dan tidak
menghentikan request biasa.

**Hasil implementasi 6 Agustus 2026:** Dua tabel MySQL-compatible, model ULID,
repository, typed storage DTO, request memoizer, public reader, safe default,
diagnostic aman, dan provider binding selesai. Focused suite lulus 17 test/45
assertion serta `module:validate System/SystemSetting` menghasilkan valid.

## Task 07 — Mutation, Authorization, Transaction, dan Audit

**Tujuan:** Mengubah satu setting atau satu kategori secara aman, terotorisasi,
atomik, dan traceable.

**Files rencana:**

- `Application/Actions/UpdateSystemSetting.php`;
- input DTO dan exception typed;
- authorization service bila responsibility perlu dipisah;
- AuditLog `module.php` untuk metadata allowlist;
- focused Action/audit/security tests.

**Acceptance criteria:**

- [x] Hanya actor dengan role `SuperSystem` yang dapat menjalankan Action.
  - Perubahan: Action memakai public `AuthorizationCapability` dan melakukan
    re-check sebelum membaca key/value.
- [x] Permission tanpa role SuperSystem tetap ditolak.
  - Evidence: user dengan direct permission `system_setting.manage` menerima
    `AuthorizationException`; setting dan audit tetap kosong.
- [x] Reason wajib, dibersihkan, dan dibatasi panjangnya.
  - Perubahan: DTO menghapus tag/control character, trim, menolak hasil kosong,
    dan membatasi 500 karakter.
- [x] Key/value divalidasi ulang di Application boundary.
  - Evidence: value 2000 untuk rate limit ditolak registry sebelum persistence.
- [x] Correlation ID dan event ID berupa ULID.
  - Perubahan: correlation divalidasi DTO dan event dibuat memakai `Str::ulid()`.
- [x] Setting upsert dan AuditRecorder berada dalam transaction yang sama.
  - Perubahan: keduanya dijalankan di satu `DB::transaction()`.
- [x] Audit menyimpan key, before, after, reason, result, actor, dan correlation.
  - Perubahan: AuditLog metadata allowlist ditambah tiga field SystemSetting.
  - Evidence: audit persisted memiliki actor, subject, correlation, reason, dan
    metadata 60 menjadi 90.
- [x] Audit failure menggagalkan mutation dan mempertahankan nilai lama.
  - Evidence: recorder exception mempertahankan nilai 70 dan jumlah audit satu.
- [x] Metadata secret/pola sensitif tetap ter-redact.
  - Perubahan: `mail.username` dan `mail.password` disimpan terenkripsi;
    read model dan metadata audit memakai masking/redaction.
- [x] Batch kategori memakai satu reason global tanpa mutation parsial.
  - Kondisi awal: operator harus membuka modal dan mengisi reason untuk setiap
    key, sehingga perubahan satu kategori memakan banyak langkah.
  - Perubahan: `UpdateSystemSettingCategoryData`, `SettingCategory`, dan
    `UpdateSystemSetting::executeCategory()` memvalidasi owner key, value, serta
    consistency seluruh payload sebelum `DB::transaction()` menyimpan setting
    dan audit per item dengan correlation yang sama.
  - Alasan: alasan audit tetap jelas bagi operator, sementara satu perubahan
    operasional tidak dipaksa menjadi banyak request terpisah.
  - Evidence: `SystemSettingMutationTest` membuktikan dua key Password tersimpan
    atomik; key lintas kategori, pasangan Session tidak konsisten, dan kegagalan
    AuditRecorder ditolak tanpa record atau audit parsial.

**Positive test:** SuperSystem mengubah setting valid atau beberapa key satu
kategori; setiap audit memiliki reason dan correlation yang sama.

**Negative test:** non-SuperSystem, missing reason, invalid value, key lintas
kategori, consistency batch gagal, dan failing AuditRecorder tidak mengubah data.

**Hasil implementasi 6 Agustus 2026:** DTO mutation dan Action fail-closed
selesai. Focused mutation, persistence, serta audit suite lulus 15 test/54
assertion.

## Task 08 — Seeder Global dan Console Command

**Tujuan:** Menyediakan bootstrap data dan operasi focused tanpa memanggil seeder
module satu per satu.

**Files rencana:**

- `Database/Seeders/SystemSettingSeeder.php`;
- `database/seeders/DatabaseSeeder.php`;
- command `system-setting:list`, `get`, `set`, dan `validate`;
- route console/provider registration;
- seeder dan command tests.

**Acceptance criteria:**

- [x] Seeder module dipanggil setelah AccessControl, UserManagement, dan AuditLog.
  - Perubahan: global `DatabaseSeeder` menambahkan `SystemSettingSeeder` paling
    akhir sesuai dependency order.
- [x] Bootstrap global membuat baseline setting tanpa duplikasi.
  - Evidence: `DatabaseSeeder` pada database test terisolasi membuat 13 record.
  - Batasan: `migrate:fresh --seed` MySQL kerja tidak dijalankan pada increment
    ini karena command tersebut menghapus seluruh data lokal.
- [x] Seeder ulang bersifat idempotent dan tidak menimpa value valid yang sudah
  diubah operator tanpa aturan eksplisit.
  - Evidence: dua run tetap 13 record dan override rate limit 125 tidak berubah.
- [x] Command list/get tidak mengubah data.
  - Evidence: kedua command sukses dan jumlah record tetap 13.
- [x] Command set mewajibkan actor SuperSystem dan reason.
  - Evidence: SuperSystem berhasil mengubah ke 75; actor biasa dan missing
    reason menghasilkan exit failure tanpa record baru.
- [x] Command validate mendeteksi missing, invalid, dan unknown record secara aman.
  - Evidence: typed report mendeteksi `operations.rto_hours` invalid dan
    `unknown.key` tidak terdaftar.
- [x] Output JSON/human tidak membocorkan secret atau detail internal.
  - Perubahan: command error memakai pesan generik dan tidak mencetak exception;
    output sensitive hanya membawa metadata `has_value`.
- [x] Input CLI sensitif tidak memakai argumen posisi.
  - Kondisi awal: signature lama mewajibkan `{value}` untuk semua key sehingga
    secret dapat tertinggal pada shell history dan process list.
  - Perubahan: `{value}` menjadi opsional pada parser tetapi tetap wajib untuk
    setting non-sensitive. Setting sensitive menolak argumen posisi dan memakai
    prompt tersembunyi atau `--value-stdin`.
  - Evidence: regression test membuktikan positional secret ditolak tanpa
    tersimpan/tercetak; prompt tersembunyi dan STDIN berhasil menyimpan nilai.

**Positive test:** global seeder dan command valid berjalan sukses.

**Negative test:** command set tanpa actor/reason atau actor non-SuperSystem
ditolak.

**Hasil implementasi 6 Agustus 2026:** Seeder module/global, typed list dan
validation query, serta command list/get/set/validate selesai. Focused suite
lulus 18 test/67 assertion.

## Task 09 — Presentation Backend dan Route Ziggy

**Tujuan:** Menyediakan halaman dan API internal dengan controller tipis.

**Files rencana:**

- `Presentation/Controllers/SystemSettingController.php`;
- `Presentation/Controllers/SystemSettingApiController.php`;
- FormRequest list/update;
- policy dan resource;
- `Routes/web.php`, `Routes/api.php`;
- `config/ziggy.php` dan generated/current Ziggy route list;
- presentation tests.

**Acceptance criteria:**

- [x] Controller hanya orchestration request, query/action, flash, dan response.
  - Perubahan: web/API controller hanya memanggil list query, update Action,
    resource, flash, dan response mapping.
- [x] Query Eloquent, validation rule, dan business logic tidak ada di controller.
  - Perubahan: rules berada pada FormRequest; read pada query/reader; mutation
    pada Action; persistence pada repository.
- [x] Web/API memakai auth, verified, middleware, policy, dan Action re-check.
  - Evidence: guest diarahkan login, direct permission non-SuperSystem mendapat
    403, dan Action negative test tetap menolak bypass presentation.
- [x] Route name tersedia pada Ziggy.
  - Evidence: index, update, dan `category.update` terdaftar di route dan
    `config/ziggy.php`; UI memakai helper Ziggy dengan parameter kategori
    positional.
- [x] 401/403/404/422 dan safe 500 behavior diuji.
  - Evidence: auth JSON memberi jalur 401, policy 403, unknown key 404, serta
    invalid reason/value 422. Reader storage failure memakai default aman.
- [x] API response typed dan tidak mengembalikan model.
  - Perubahan: `SystemSettingResource` hanya menerima Application DTO.
- [x] Unknown key tidak membocorkan registry internal.
  - Perubahan: exception typed dipetakan menjadi 404 tanpa daftar key.

**Positive test:** SuperSystem membuka list, mengubah satu setting, atau
mengubah beberapa key kategori dengan reason global.

**Negative test:** guest/non-SuperSystem/direct request ditolak backend.

**Hasil implementasi 6 Agustus 2026:** Web/API controller tipis, FormRequest,
resource, policy, routes, Ziggy, dan presentation tests selesai. Suite lulus 6
test/42 assertion.

## Task 10 — Frontend Vertical Slice SystemSetting

**Tujuan:** Memberikan UI yang dapat ditinjau dan diuji langsung.

**Files rencana:**

- `resources/js/pages/System/SystemSetting/pages/Index.tsx`;
- component summary, search/filter, category panel, edit dialog, dan state;
- `schemas.ts` dan `types.ts`;
- `resources/js/components/app-sidebar.tsx`;
- `resources/js/components/command-palette.tsx`;
- shared auth types bila diperlukan;
- frontend/browser tests.

**Acceptance criteria:**

- [x] Page memakai `system-dashboard-layout` dan baseline visual module.
  - Perubahan: seluruh card memakai `dashboard-card`, `dashboard-subcard`,
    `dashboard-icon`, dan `dashboard-badge`.
- [x] Category API, Password, Session, Email, Pagination, Branding, Monitoring,
  dan Operations jelas.
  - Perubahan: menu berada di kiri pada desktop dan tetap mengikuti urutan
    workspace pada mobile agar prioritas konten tetap jelas.
- [x] Istilah teknis memiliki panduan operator berbahasa awam.
  - Kondisi awal: card hanya menampilkan key dan description registry seperti
    `api.idempotency.retention_hours`, sehingga operator non-teknis tidak selalu
    mengetahui tujuan nilai maupun dampaknya.
  - Perubahan: `categories.ts` menyediakan judul, tujuan, cara mengisi, contoh,
    dan peringatan untuk seluruh 26 key aktif. Workspace serta dialog kategori
    menampilkan panduan yang sama sambil mempertahankan key teknis sebagai
    referensi.
  - Alasan: operator dapat mengambil keputusan berdasarkan tujuan konfigurasi,
    bukan sekadar mengubah angka atau opsi yang tidak dipahami.
  - Evidence: browser SuperSystem menampilkan `api.idempotency.retention_hours`
    sebagai “Masa simpan hasil request API yang sama”, menjelaskan perlindungan
    request ulang selama 24 jam, serta menegaskan bahwa setting bukan backup.
- [x] Edit kategori memakai satu modal dengan satu reason global dan error dekat input.
  - Kondisi awal: tiap card memiliki tombol Ubah serta textarea reason sendiri.
  - Perubahan: card hanya menampilkan nilai; tombol `Ubah kategori` membuka
    `EditSystemSettingCategoryDialog` untuk semua key pada kategori aktif.
    Tombol simpan menghitung perubahan aktual dan disabled bila belum ada value
    yang diubah.
  - Alasan: operator awam cukup menjelaskan satu tujuan perubahan kategori,
    tanpa mengulang alasan yang sama pada tiap input.
  - Evidence: browser SuperSystem membuka dialog Password dan Email; submit satu
    key menampilkan toast, nilai Email sensitif tetap kosong pada form, dan
    console browser tidak memiliki error.
- [x] Shortcut `/` fokus search dan `Esc` menutup modal.
  - Evidence: browser snapshot menunjukkan search focused setelah `/`; Radix
    Dialog menangani `Esc` dan focus trap.
- [x] Loading, empty, validation error, storage error, unauthorized, dan success
  toast tersedia.
- [x] Menu hanya terlihat untuk SuperSystem, tetapi backend tetap authority.
  - Perubahan: sidebar/command palette memakai `auth.superSystem`; policy/Action
    tetap menjadi authority backend.
- [x] Route frontend memakai Ziggy.
  - Evidence: browser PATCH menuju route bernama dan menghasilkan 303 lalu GET 200.
- [x] Responsive desktop/mobile, keyboard, focus, light/dark, palette, dan
  accessibility diuji.
  - Evidence: viewport 485x mobile dan 1440x900 desktop terlihat utuh; light/dark
    lulus; Lighthouse accessibility naik dari 96 menjadi 100 setelah kontras
    deskripsi kategori aktif diperbaiki.
- [x] Typecheck, lint, format, dan build lulus.
  - Evidence: ESLint, TypeScript, Prettier pada file target, serta Vite build lulus.

**Positive test:** perubahan kategori berhasil dari modal dan nilai terbaru tampil.

**Negative test:** invalid value/missing reason menampilkan error tanpa menutup
modal atau mengubah value.

**Hasil implementasi 6 Agustus 2026:** Vertical slice frontend lengkap, dapat
dibuka di browser, mutation berhasil, nilai uji dikembalikan ke baseline 60,
console kosong, dan Lighthouse 100 tanpa audit gagal.

## Task 11 — Runtime Rate Limit dan Idempotency

**Tujuan:** Menghubungkan public reader ke kontrol API tanpa direct repository.

**Files rencana:**

- adapter policy retention/rate milik SystemSetting;
- repository/model/migration idempotency milik SystemSetting;
- middleware, service lifecycle, dan repository contract generik
  `packages/StarterKit` sesuai ADR-0003;
- middleware registration;
- idempotency migration/model dari Task 06;
- integration/security tests.

**Acceptance criteria:**

- [x] Rate limit membaca `api.rate_limit.per_minute` dan tetap per actor/endpoint.
  - Perubahan: limiter `system-api` memakai public reader serta key actor+route.
  - Evidence: limit 2 menerima dua request dan menolak request ketiga dengan 429.
- [x] Invalid/storage failure memakai default 60.
  - Evidence: record 5000 menghasilkan `Limit::maxAttempts` 60.
- [x] Idempotency retention membaca setting default 24 jam.
  - Perubahan: expiry reservation dihitung oleh manager dari typed reader.
- [x] Payload hash selalu wajib dan tidak dapat dimatikan.
  - Perubahan: canonical payload selalu di-hash SHA-256 64 karakter sebelum
    reservation dibuat.
- [x] Duplicate key+payload mereplay sanitized response.
  - Evidence: response kedua identik, header `Idempotency-Replayed=true`, dan
    audit mutation hanya satu.
- [x] Duplicate key dengan payload berbeda menghasilkan conflict.
  - Evidence: request kedua 409, nilai pertama tetap, audit tetap satu.
- [x] Expired record tidak direplay.
  - Evidence: record expired dihapus dan diganti satu reservation baru.
- [x] Consumer hanya mengimpor public SystemSetting contract.
  - Perubahan: limiter/manager memakai `SystemSettingReader`, bukan repository
    setting concrete.
- [x] Request bersamaan dan failure finalisasi bersifat fail-closed.
  - Perubahan: reservation atomic status 102 mencegah mutation ganda; outer
    transaction menyatukan Action, audit, dan finalisasi response.
  - Evidence: failure completion menghasilkan safe 503 serta rollback setting/audit.

**Positive test:** custom valid setting mengubah behavior request berikutnya.

**Negative test:** bypass rate limit, hash mismatch, storage failure, dan
sensitive replay payload ditangani aman.

**Hasil implementasi 6 Agustus 2026, direvisi 14 Agustus 2026:** Limiter runtime,
atomic idempotency, payload hash, replay/conflict/expiry, sanitizer response,
safe 503, middleware alias, dan integration tests selesai. Contract, middleware,
serta lifecycle generik dipindahkan ke `packages/StarterKit`; migration, model,
repository adapter, prune, dan policy retention/rate tetap dimiliki
SystemSetting. Reader/memoizer tetap request-scoped. Focused gate boundary baru
lulus 35 test/251 assertion.

## Task 12 — Session, Branding, Appearance, dan Operational Target

**Tujuan:** Mengaktifkan setting UI/runtime lain tanpa mengganti user preference.

**Files rencana:**

- session timeout middleware/service;
- bootstrap/shared Inertia branding props;
- `useAppearance` dan `useThemePalette` untuk global fallback;
- app name/logo/favicon consumer;
- operational diagnostic/health reader;
- integration dan browser tests.

**Acceptance criteria:**

- [x] Idle timeout default 30 menit dan absolute lifetime 12 jam.
  - Evidence: storage setting hilang tetap mengakhiri session idle 31 menit
    melalui default registry.
- [x] Session timestamps membedakan idle dari absolute expiry.
  - Perubahan: middleware menyimpan timestamp mulai session dan aktivitas
    terakhir secara terpisah; kedua jalur expiry memiliki dataset test sendiri.
- [x] Custom valid value aktif pada request berikutnya.
  - Evidence: idle 5 menit/absolute 2 jam mempertahankan session 4 menit,
    menolak idle 6 menit, dan menolak umur 3 jam.
- [x] Branding global dipakai saat user belum memiliki preference.
  - Perubahan: SSR HTML, Inertia shared props, title, logo, favicon, palette,
    typography, dan appearance membaca runtime snapshot.
- [x] Cookie/localStorage user tetap mengalahkan default global.
  - Evidence: test server membuktikan cookie light mengalahkan default dark;
    browser membuktikan local dark/ruby/mono mengalahkan system/neutral/system.
- [x] Logo/favicon hanya menerima path lokal aman.
  - Evidence: registry menolak protocol/traversal dan Blade hanya memakai typed
    path yang telah lolos registry.
- [x] RTO/RPO aktif dapat dibaca diagnostic tanpa mengklaim backup sudah diuji.
  - Perubahan: `system-setting:runtime` menampilkan target runtime saja.
- [x] Monitoring flag hanya mengaktifkan capability yang memang tersedia.
  - Perubahan: runtime membedakan requested/available/enabled; adapter default
    unavailable membuat enabled false meskipun flag diminta true.
- [x] Tidak ada secret di shared props, HTML, log, atau diagnostic.
  - Evidence: props dan diagnostic hanya memuat field allowlist typed.
- [x] Runtime snapshot tidak membuat query per key.
  - Perubahan: `SystemSettingReader::many()` dan repository `findMany()` membaca
    seluruh key runtime dalam satu query serta memoization scoped per request.

**Positive test:** default/custom runtime value dan fallback frontend bekerja.

**Negative test:** unsafe path, invalid enum, session range salah, dan storage
failure memakai safe behavior.

**Hasil implementasi 6 Agustus 2026:** Session lifecycle, consistency rule,
runtime snapshot batch/scoped, SSR/Inertia branding, global fallback, preference
override, typography, logo/favicon, diagnostic, dan monitoring capability selesai.
Suite terkait lulus 33 test/177 assertion; frontend quality gate dan browser
Lighthouse 100 lulus tanpa console issue.

## Task 13 — Final Quality Checkpoint dan Dokumentasi Hasil

**Tujuan:** Menutup module hanya setelah seluruh vertical slice terbukti.

**Files:** seluruh file module, frontend, tests, baseline downstream docs,
README, ADR, task, dan execution log.

**Acceptance criteria:**

- [x] Focused unit, contract, feature, integration, architecture, seeder,
  command, API, frontend, dan browser test lulus.
  - Evidence: `php artisan test --filter=SystemSetting` lulus 52 test/231
    assertion setelah test batch reader dan retention custom ditambahkan.
- [x] Positive, negative, authorization, audit, storage failure, dan safe
  default path memiliki evidence.
  - Evidence: suite mencakup SuperSystem/non-SuperSystem, invalid key/value,
    audit rollback, storage failure 503/default, replay/conflict, dan session
    expiry.
- [x] Pint, PHPStan, ESLint, Prettier, TypeScript, build, dan full CI lulus.
  - Evidence: seluruh command quality gate selesai tanpa error; PHPStan 0 error.
- [x] Fresh migration/seed dan rollback review selesai secara aman.
  - Evidence: database test terisolasi membuktikan fresh schema, global seeder,
    relation, idempotency, `down()` dan `up()`. MySQL kerja diverifikasi secara
    non-destruktif: migration batch 3 berstatus `Ran`, seeder module idempotent,
    dan `system-setting:validate --json` menghasilkan `valid: true`.
  - Batasan: `migrate:fresh` tidak dijalankan pada database kerja karena akan
    menghapus data user. Rehearsal database shared/production tetap scope operasi.
- [x] Discovery, validate, list, dan inspect lulus.
  - Evidence: keempat command menemukan SystemSetting aktif dan valid tanpa
    diagnostic.
- [x] Browser desktop/mobile, light/dark, seluruh palette, keyboard, console,
  network, dan accessibility diperiksa.
  - Evidence: desktop 1440x900 dan mobile 485x900 dapat membuka halaman;
    shortcut `/`, modal `Esc`, light/dark, serta token 17 palette terbukti.
    Console tidak memiliki error/warning dan Lighthouse mobile 100 dengan 0
    audit gagal.
- [x] Code review correctness, readability, architecture, security,
  performance, dan dependency selesai.
  - Perubahan review: timestamp memakai `getTimestamp()`, hasil repository
    dinormalisasi sebagai list, reader `many()` tidak dapat mengembalikan null,
    dan batch runtime dibuktikan hanya satu panggilan repository.
  - Evidence: tidak ada import private infrastructure AccessControl, AuditLog,
    atau UserManagement; `starter:verify` melaporkan forbidden dependency kosong.
- [x] README module, project docs, execution log, revision history, ADR, dan open
  risk sesuai kondisi nyata.
  - Perubahan: status rencana/berjalan diganti menjadi selesai, ADR tetap
    `Accepted`, dan scope lanjutan dipisahkan dari OPEN RISK workspace.
- [x] Checklist seluruh task ditinjau ulang; task tanpa evidence tidak ditandai.
  - Evidence: Task 01 sampai Task 13 memiliki hasil command/test atau batasan
    eksplisit yang dapat ditelusuri.
- [x] Scope lanjutan dan migration shared/production dicatat terpisah.
  - Evidence: upload media, cache lintas request, provider monitoring konkret,
    dan rehearsal deployment tercatat sebagai scope lanjutan dengan owner proses.

**Verification akhir:**

```bash
php artisan module:discover --json
php artisan module:validate --json
php artisan module:list --json
php artisan module:inspect System/SystemSetting --json
# Dijalankan pada database test terisolasi, bukan database kerja berisi data.
php artisan migrate:fresh --seed
php artisan test --filter=SystemSetting
npm run lint:check
npm run format:check
npm run types:check
npm run build
composer ci:check
```

**Hasil implementasi 6 Agustus 2026:** Final quality checkpoint selesai. Focused
suite, full CI, module registry, MySQL non-destruktif, runtime diagnostic,
frontend build, browser desktop/mobile, seluruh palette, console, network, dan
Lighthouse lulus. Tidak ada OPEN RISK pada scope workspace saat ini.

## Execution Log

Execution log lengkap disimpan pada [planning/execution-log.md](planning/execution-log.md).
Setiap increment wajib mencatat source, file, alasan, command, hasil, dan risiko
agar dapat dipahami tanpa membaca percakapan agent.

## Definition of Done

- [x] Semua Task 01 sampai Task 13 memiliki status dan evidence nyata.
- [x] ADR disetujui dan implementasi sesuai keputusan.
- [x] Public contract dan dependency tidak membocorkan private implementation.
- [x] Authorization SuperSystem dan audit fail-closed terbukti.
- [x] Runtime setting valid, safe default, dan failure diagnostic terbukti.
- [x] Frontend vertical slice dapat ditinjau langsung melalui browser.
- [x] Quality gate dan module validation lulus.
- [x] Dokumentasi authoritative/downstream sudah selaras.
- [x] Open risk workspace tertutup; scope lanjutan memiliki batasan dan owner
  proses yang jelas.

## Revision History

| Versi | Tanggal | Perubahan |
| --- | --- | --- |
| 1.0 | 2026-08-06 | Menambahkan task detail SystemSetting dari discovery sampai final quality gate |
| 1.1 | 2026-08-06 | Menutup Task 13 dan Definition of Done berdasarkan evidence aktual |
