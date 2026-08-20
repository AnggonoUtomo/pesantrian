# Execution Log: Perencanaan Remediasi Kode dan Dokumentasi System

## 13 Agustus 2026 - Intake dan Penyusunan Rencana

- Source dibaca: `AGENTS.md`, `docs/AGENTS.md`, `docs/README.md`, baseline
  requirement, system/API/security design, coding standard, module guide,
  technical spec, test plan, CI/CD, ADR module communication, specification
  empat module, evaluasi module, serta kode yang menjadi evidence temuan.
- Kondisi awal: quality gate lokal lulus 300 test/1.400 assertion, build lulus,
  empat module valid, empat route `/api/v1` tersedia, dan working tree memiliki
  perubahan user pada `tests/Feature/UserManagementPresentationTest.php`.
- Perubahan dokumentasi: menambah README, implementation plan, task checklist,
  dan execution log awal untuk program remediasi. Tidak ada source code,
  dependency, migration, workflow, atau test yang diubah.
- Alasan: temuan memiliki dependency lintas security, framework bootstrap,
  module boundary, public API, CI, browser, dan dokumentasi. Pekerjaan harus
  dipisah menjadi increment yang dapat diverifikasi dan di-rollback.
- Acceptance perencanaan: setiap task mempunyai kondisi awal, file/boundary,
  perubahan, alasan, acceptance, command/test, dependency, dan risiko.
- Risiko: keputusan bootstrap, dependency inversion, dan API contract belum
  berstatus accepted. Implementasi tidak boleh dimulai sebelum Decision Gate
  pada Phase 0 ditutup.

## 13 Agustus 2026 - Retry Browser Chrome

- Pemeriksaan: Chrome terpasang dan sedang berjalan. Native-host manifest
  tersedia, origin sesuai, dan registry menunjuk manifest yang benar.
- Hasil: extension ChatGPT Browser tidak terdeteksi pada profil Chrome yang
  dipilih, sehingga koneksi browser tetap unavailable. Extension
  `chrome-devtools` yang sudah dipasang tidak menggantikan extension Browser
  yang dibutuhkan adapter sesi ini.
- Dampak rencana: browser verification tetap menjadi T11.4 dan tidak ditandai
  selesai. Prasyaratnya adalah memasang atau mengaktifkan extension melalui
  `Settings -> Computer use` pada profil test, lalu mengulang koneksi.
- Batasan keamanan: tidak ada cookie, local storage, password, token, atau tab
  pribadi yang dibaca.

Catatan: status di atas merupakan hasil percobaan historis dengan adapter yang
berbeda. Hasil tersebut digantikan oleh koneksi MCP `chrome-devtools` pada
14 Agustus 2026.

## 14 Agustus 2026 - Verifikasi MCP Chrome Read-only

- Kondisi awal: user telah memasang extension `chrome-devtools` dan meminta
  seluruh pekerjaan browser memakai server MCP bernama `chrome-devtools`.
- Halaman diperiksa: public home, login, dashboard, AccessControl,
  UserManagement, AuditLog, dan SystemSetting pada context terisolasi.
- Role diperiksa: SecurityAdmin berhasil membuka tiga module operasional dan
  menerima 403 yang diharapkan pada SystemSetting. SuperSystem berhasil membuka
  SystemSetting. Hal ini membuktikan positive dan negative visibility dasar.
- Viewport/theme: desktop `1440x1000`, mobile `390x844`, serta light/dark pada
  dashboard dan halaman module. Layout utama tetap dapat dibaca dan control
  utama mempunyai accessible name pada accessibility tree.
- Console/network: console bersih pada halaman sukses. Satu resource 403 hanya
  muncul pada negative authorization test yang diharapkan. Request halaman
  sukses berstatus 200/304.
- Batasan: pemeriksaan ini read-only. Mutation, toast, disabled/loading button,
  keyboard/focus lengkap, empty/error state, Playwright, dan axe belum diuji.
- Insiden diagnostic: satu pemanggilan detail network MCP menampilkan header
  sesi pada output tool. Nilainya tidak disalin ke dokumen. Inspeksi detail
  dihentikan, kedua sesi demo segera diakhiri melalui UI, dan pemeriksaan
  berikutnya dibatasi pada daftar URL/status.

## 14 Agustus 2026 - Perbaikan Latensi SSR Inertia

- Kondisi awal: dua trace UserManagement menghasilkan LCP sekitar 3,2-3,6
  detik; TTFB sekitar 2,75-3,14 detik dan CLS 0.
- Diagnosis: request `/up` cepat dan query database hanya mengambil sebagian
  kecil durasi. Initial Inertia HTML tetap sekitar 2,3 detik. Konfigurasi
  `config/inertia.php` mengaktifkan SSR permanen ke `127.0.0.1:13714`, padahal
  proses SSR tidak berjalan. SSR yang dinonaktifkan di memori menurunkan waktu
  `/login` menjadi sekitar 207 ms pada kernel test.
- Test merah: `php artisan test
tests/Feature/InertiaSsrConfigurationTest.php --no-coverage` gagal 2 test
  karena SSR masih aktif dan `.env.example` belum memiliki flag opt-in.
- Perubahan: `config/inertia.php` memakai `INERTIA_SSR_ENABLED=false` sebagai
  default serta `INERTIA_SSR_URL`; `.env.example` mendokumentasikan kedua flag;
  focused regression test ditambahkan.
- Evidence: focused test lulus 2 test/4 assertion. Curl TTFB home/login turun
  menjadi sekitar 0,71-1,02 detik. Trace MCP login menghasilkan LCP 1,053 detik,
  TTFB 575 ms, CLS 0, console bersih, dan seluruh request 200/304.
- Risiko: HTTP/1.1, cache asset, forced reflow kecil, dan build plugin timing
  masih dapat dioptimalkan, tetapi bukan blocker correctness pada increment ini.

## 14 Agustus 2026 - Rehearsal MySQL Disposable

- Kondisi awal: fresh migration/global seeder belum dibuktikan pada MySQL tanpa
  menyentuh database developer.
- Guard: nama database baru memakai prefix exact `starter13_verify_`, berbeda
  dari `DB_DATABASE`, diperiksa tidak sudah ada, dan dihapus dalam cleanup.
- Percobaan awal: command pertama gagal parse sebelum koneksi. Percobaan kedua
  melewati timeout setelah membuat database sementara; schema sisa ditemukan
  dengan query prefix exact dan segera dihapus setelah nama tervalidasi.
- Evidence final: run 107,3 detik lulus `migrate:fresh --seed`, seed kedua,
  rollback, serta `migrate --seed` ulang. Count allowlist fresh adalah 11
  migration, 52 user, 17 permission, 2 role, 52 assignment role, 3 audit log,
  dan 26 system setting. Seed kedua stabil dan hasil migrate ulang identik.
- Cleanup: database sementara final berhasil dihapus. Data yang dihapus hanya
  data dummy pada database disposable dan tidak dapat dipulihkan.
- Risiko: belum ada fixture snapshot release lama, sehingga upgrade path
  historis belum terbukti dan tetap menjadi T10.3.

## 14 Agustus 2026 - Composer Constraint dan Quality Gate

- Source resmi: dokumentasi Composer menjelaskan caret sebagai range update
  non-breaking berbatas major dan `update --lock` sebagai pembaruan hash lock
  tanpa mengubah versi package.
- Perubahan: `predis/predis`, `spatie/laravel-medialibrary`,
  `spatie/laravel-permission`, dan `starterkit/framework` memakai caret range.
  `composer.lock` hanya berubah pada `content-hash`.
- Evidence dependency: command
  `composer update --lock --no-install --no-audit --no-scripts --no-interaction`
  melaporkan tidak ada package yang berubah.
  Empat versi terkunci tetap 3.5.1, 11.23.4, 8.3.0, dan 1.0.0.
- Evidence quality: `composer validate --strict --no-check-publish` lulus;
  `composer audit --locked` dan `npm audit --omit=dev --audit-level=high`
  tidak menemukan vulnerability.
- Evidence aplikasi: `composer ci:check` lulus ESLint, Prettier, TypeScript,
  Pint, PHPStan, serta 302 test/1.404 assertion. `npm run build` lulus dengan
  2.744 module ditransformasi dalam sekitar 79 detik.
- Evidence module: discover, validate, list, dan inspect empat module lulus
  tanpa diagnostic. Route `/api/v1` tetap empat endpoint existing: dua AuditLog
  serta dua SystemSetting.
- Batasan: build memberi catatan waktu plugin CSS, Inertia, Laravel, dan React.
  Automated coverage, browser/a11y, CodeQL, serta API matrix lengkap tetap task
  program, bukan bagian open risk yang ditutup pada increment ini.

## Decision Gate Sebelum Jawaban User

Catatan berikut adalah kondisi sebelum keputusan 14 Agustus 2026 dan sudah
digantikan oleh bagian penutupan decision gate di bawahnya.

- `docs/AI-PROMPT-GUIDE.md` diwajibkan root `AGENTS.md`, tetapi riwayat Git
  menunjukkan file itu sengaja dihapus dalam commit `e71a8f7`. Pemulihan file
  atau perubahan aturan authoritative tidak dilakukan tanpa arahan user.
- ADR runtime module bootstrap dan consumer-owned runtime-setting port belum
  dibuat/ditandai accepted. Implementasi yang bergantung pada dua keputusan ini
  tidak dimulai.
- Perubahan user pada `tests/Feature/UserManagementPresentationTest.php` tetap
  dipertahankan dan tidak disentuh.

## 14 Agustus 2026 - Penutupan Decision Gate Governance

- Keputusan user: panduan prompt lama tetap dihapus. Root `AGENTS.md` diperbarui
  agar authoritative first-read hanya mewajibkan `docs/AGENTS.md` dan
  `docs/README.md`.
- Perubahan downstream: constraint PHP pada `docs/README.md`, status evaluasi
  empat module, serta status scope UserManagement diselaraskan dengan source
  dan checklist aktual. Reference prompt guide pada plan SystemSetting dihapus.
- ADR accepted: `ADR-0001-DYNAMIC-MODULE-RUNTIME-BOOTSTRAP.md` menetapkan graph
  authority, topological boot, failure isolation, cache behavior, acceptance,
  dan rollback. `ADR-0002-CONSUMER-OWNED-RUNTIME-SETTING-PORT.md` menetapkan port
  milik consumer, default adapter, adapter SystemSetting, binding order, safe
  fallback, serta graph acyclic.
- Evidence source: provider module masih statis pada `bootstrap/providers.php`;
  manifest tetap berurutan AccessControl, UserManagement, AuditLog,
  SystemSetting; import langsung UserManagement/AuditLog ke SystemSetting masih
  ada dan menjadi target T05.
- Risiko tersisa: ADR-0005 UserManagement masih `Proposed` dengan Open Decision
  disk avatar walaupun implementation identity/avatar/login telah selesai.
  Status ADR tersebut tidak diubah tanpa keputusan user terpisah.

## 14 Agustus 2026 - Penutupan ADR-0005 UserManagement

- Keputusan user: ADR-0005 diterima (`Accepted`).
- Kondisi source: Media Library memakai default `MEDIA_DISK=local`, disk local
  berada di `storage/app/private`, dan URL avatar berasal dari route module
  yang dilindungi policy `view`.
- Perubahan dokumentasi: status ADR, keputusan disk/URL, README UserManagement,
  execution log increment identity/avatar, dan checklist T00.5 diselaraskan.
- Evidence: review source terkait, link checker dokumentasi, dan
  `git diff --check` lulus.
- Risiko tersisa: perpindahan ke public disk atau URL public memerlukan ADR
  pengganti; risiko ini bukan blocker implementasi saat ini.

## 14 Agustus 2026 - INC-01 Redaksi Nilai Sensitif SystemSetting

- Kondisi awal: `GetSystemSettingCommand` menyalin raw typed value ke table dan
  JSON; `SetSystemSettingCommand` menyalin result value ke JSON;
  `SystemSettingResource` juga menyalin value hasil update API.
- Test merah: dua test CLI gagal karena dummy secret muncul pada output get dan
  set. Test API gagal karena `data.value` berisi dummy secret, bukan `null`.
- Perubahan: ditambahkan `SystemSettingOutputPresenter`. Presenter membaca
  metadata registry, mempertahankan typed value non-sensitive, dan mengubah
  output sensitive menjadi `value: null`, `sensitive: true`, serta
  `has_value`. Command get/set, resource, controller API/web, type TypeScript,
  dan tampilan status secret memakai policy yang sama.
- Perubahan dokumentasi: specification SystemSetting menjelaskan storage
  encrypted, perbedaan typed reader internal dengan boundary publik, contract
  redaksi list/update, dan status UI berdasarkan `has_value`.
- Evidence test: `php artisan test tests/Feature/SystemSettingSeederAndCommandTest.php
tests/Feature/SystemSettingPresentationTest.php` lulus 18 test/118 assertion.
  Test memeriksa response update, list, dan audit tidak memuat dummy secret.
- Evidence quality: PHPStan pada folder Presentation, ESLint, TypeScript, dan
  Pint pada file terkait lulus tanpa ignore/baseline.
- Risiko tersisa: argumen posisi `system-setting:set {key} {value}` masih dapat
  meninggalkan secret di shell history/process list. Perubahan input sensitif
  menunggu persetujuan contract user; output sudah aman.

## 14 Agustus 2026 - Penutupan Input CLI Sensitif

- Keputusan user: argumen posisi dipertahankan untuk nilai biasa. Setting
  sensitif wajib menolak argumen posisi dan memakai prompt tersembunyi atau
  `--value-stdin` untuk otomasi.
- Test merah: positional secret masih diterima; prompt tanpa value gagal karena
  argument wajib; opsi `--value-stdin` belum dikenal.
- Perubahan: signature membuat `{value}` opsional pada parser. Command tetap
  mewajibkannya untuk definition non-sensitive, menolak positional sensitive,
  memakai `secret()` pada mode interaktif, dan membaca stream hanya ketika
  `--value-stdin` diminta.
- Evidence: suite command lulus 11 test/57 assertion. Positional dummy secret
  gagal tanpa tersimpan/tercetak; hidden prompt dan STDIN berhasil; JSON output
  tetap teredaksi. PHPStan dan Pint pada scope terkait lulus.
- Risiko/batasan: operator otomasi tetap wajib memastikan producer STDIN atau
  secret manager tidak mencatat nilai. Project tidak dapat menjamin keamanan
  tool eksternal di luar process Artisan.

## 14 Agustus 2026 - INC-02 Safe Default Ciphertext Rusak

- Kondisi awal: repository hanya menangkap `JsonException`; ciphertext rusak
  atau hasil enkripsi dengan key lain melempar `DecryptException` melewati
  boundary reader.
- Test merah: single read gagal dengan `The payload is invalid`; batch read key
  mismatch gagal dengan `The MAC is invalid`.
- Perubahan: repository memvalidasi envelope sensitive berbentuk string lalu
  membungkus `DecryptException` dan `JsonException` menjadi
  `SettingStorageUnavailable`. Reader yang sudah ada menangkap exception domain,
  mencatat allowlist `setting_key`/`failure_type`, dan memakai default registry.
- Evidence: persistence suite lulus 11 test/33 assertion; suite security gabungan
  lulus 32 test/162 assertion; PHPStan dan Pint lulus.
- Risiko/batasan: batch repository bersifat fail-safe per panggilan. Satu
  ciphertext rusak membuat semua key pada batch tersebut memakai default agar
  tidak mengembalikan hasil parsial yang sulit diaudit.

## 14 Agustus 2026 - Checkpoint 1 Security

- Focused evidence: command, presentation, dan persistence SystemSetting lulus
  32 test/162 assertion.
- Full evidence: `composer ci:check` lulus ESLint, Prettier, TypeScript, Pint,
  PHPStan, serta Pest 310 test/1.469 assertion. Run pertama dihentikan timeout
  120 detik tanpa hasil; run ulang dengan batas 300 detik selesai dalam 246,3
  detik dan menjadi evidence authoritative.
- Secret scan: pencarian seluruh dummy secret pada `storage/logs/*.log`
  menghasilkan `DUMMY_SECRET_LOG_SCAN=CLEAN`; stdout, JSON response, dan audit
  diperiksa oleh regression test.
- Status: Checkpoint 1 ditutup. Phase runtime/boundary boleh dimulai setelah
  browser smoke test memastikan contract `has_value` tidak merusak UI.

## 14 Agustus 2026 - Browser Smoke dan Koreksi `has_value`

- Metode: hanya MCP `chrome-devtools`; tidak memakai Browser, @Chrome,
  computer use, atau node_repl. Akun SuperSystem fixture lokal dibuat untuk
  smoke test, dipakai login, lalu logout dan dihapus dengan verifikasi
  `exists=false`.
- Temuan browser pertama: setelah bundle baru dimuat, nilai `null` untuk
  `mail.password` dan `mail.username` tampil sebagai `Rahasia terisi`.
  Penyebabnya adalah query list mengganti setiap sensitive database value dengan
  mask sebelum presenter menghitung `has_value`.
- Test merah: API list mengembalikan `has_value=true` untuk `mail.password`
  yang belum diatur. Perubahan: `SystemSettingItemData` membawa `hasValue`
  eksplisit dari typed value asli; query tidak lagi membuat mask; presenter
  tetap memaksa value/default sensitive menjadi `null`.
- Evidence focused: presentation suite lulus 11 test/83 assertion; PHPStan,
  Pint, TypeScript, dan build Vite 2.744 module lulus.
- Evidence browser: desktop/light dan mobile 390x844/dark menampilkan
  `Rahasia belum diatur`; dialog Email membuat input Password/Username kosong;
  console tidak memiliki error/warning/issue; 42 document/XHR/fetch/script/CSS
  request yang terdaftar berstatus 200.
- Full evidence terbaru: `composer ci:check` lulus ESLint, Prettier, TypeScript,
  Pint, PHPStan, serta Pest 311 test/1.480 assertion dalam 65,5 detik.
- Status: Checkpoint 1 Security selesai dan browser smoke lulus.

## 14 Agustus 2026 - INC-03 Graph Dependency Module Canonical

- Kondisi awal: `ModuleRegistry::discover()` hanya memvalidasi manifest,
  identity, permission, dan config source. Status disabled, dependency hilang,
  self-dependency, cycle, provider invalid, serta urutan boot belum diproses.
- Test merah: lima skenario awal gagal karena `bootPlan()` belum ada; test
  self-dependency ditambahkan sebelum checkpoint ditutup.
- Perubahan: `ModuleGraphValidator` memvalidasi status/provider/dependency,
  mendeteksi cycle via DFS, memblokir dependent secara transitif, dan membuat
  topological order deterministik. Diagnostic hanya membawa
  `code/module/phase/path/message` dengan pesan allowlist.
- Perubahan command: discover, validate, list, dan inspect memakai hasil graph
  yang sama. JSON menyertakan `boot_plan`; list menandai `bootable`; inspect
  target isolated gagal dengan diagnostic target tanpa absolute path.
- Evidence test: registry/manifest lulus 18 test/46 assertion; gabungan command,
  registry, dan manifest lulus 29 test/108 assertion. PHPStan dan Pint lulus.
- Evidence production: empat command nyata exit 0; boot plan berurutan
  AccessControl, UserManagement, AuditLog, SystemSetting; diagnostic kosong.
- Batasan: graph masih read-only. `bootstrap/providers.php` masih statis dan
  belum memakai boot plan sampai test register/boot isolation T04.1 tersedia.

## 14 Agustus 2026 - INC-04 Dynamic Module Runtime Bootstrap

- Kondisi awal: provider AccessControl, UserManagement, AuditLog, dan
  SystemSetting masih hardcoded pada `bootstrap/providers.php`. Graph canonical
  belum mengendalikan lifecycle Laravel dan test isolation hanya memeriksa
  hasil discovery.
- Test merah: runtime test awal gagal karena `ModuleRuntimeServiceProvider`
  belum tersedia. Setelah class awal dibuat, fixture masih tidak ditemukan
  karena provider tidak mengikuti short name canonical `ServiceProvider`;
  fixture diperbaiki menjadi namespace per module.
- Perubahan framework: `ModuleRuntimeServiceProvider` memakai boot plan
  tervalidasi, menjalankan `register` dan `boot` sesuai urutan dependency,
  mengisolasi dependent bila dependency gagal, serta membiarkan peer valid
  berjalan. `ModuleRuntimeState` menyimpan status dan diagnostic allowlist tanpa
  exception mentah.
- Perubahan composition root: empat provider module statis dihapus dari
  `bootstrap/providers.php` dan diganti satu composition provider. Empat command
  module menggabungkan diagnostic fase register/boot dari runtime state dengan
  diagnostic graph canonical.
- Evidence test: runtime isolation lulus 3 test/18 assertion. Suite command,
  registry, manifest, bootstrap fixture, dan production bootstrap lulus 29
  test/164 assertion. Full `composer ci:check` lulus ESLint, Prettier,
  TypeScript, Pint, PHPStan, dan Pest 325 test/1.606 assertion dalam 155,3
  detik.
- Evidence operasional: `module:discover`, `module:validate`, `module:list`, dan
  `module:inspect System/SystemSetting` exit 0; urutan boot plan adalah
  AccessControl, UserManagement, AuditLog, SystemSetting; diagnostic kosong.
  `config:cache` dan `route:cache` berhasil. Percobaan awal `route:list` memakai
  filter path yang salah; verifikasi ulang pada `system/system-settings`
  menemukan tiga route module. `migrate:status` menunjukkan seluruh migration
  module `Ran`; cache verifikasi kemudian dibersihkan dengan `optimize:clear`.
- Static analysis package: file runtime dan command terkait lulus PHPStan.
  Scan seluruh `packages/StarterKit/src` menemukan tiga type issue lama pada
  generator yang belum masuk path gate default; perbaikannya tetap dimiliki
  T10.1. `ModuleMakeCommand` sudah diperketat dengan `JSON_THROW_ON_ERROR` agar
  output console tidak menerima `false` dari `json_encode`.
- Risiko/batasan: lifecycle provider sengaja dijalankan oleh composition
  provider agar failure dapat diisolasi. Perubahan manifest/status tetap wajib
  diikuti invalidasi dan pembuatan ulang cache sesuai ADR-0001.
- Status: T04.1 dan T04.2 selesai. Checkpoint berikutnya adalah dependency
  inversion runtime setting pada T05.

## 14 Agustus 2026 - INC-05 Consumer-owned Runtime-setting Port

- Kondisi awal: UserManagement mengimpor `SystemSettingReader` untuk SMTP dan
  `SystemRuntimeSettings` untuk pagination. AuditLog mengimpor DTO runtime
  SystemSetting untuk pagination. Coupling tidak tercatat pada manifest dan
  dependency balik akan membuat cycle.
- Test merah: setelah consumer dialihkan ke default port, characterization
  AuditLog mengembalikan per-page `25`, bukan nilai registry `10`. Ini
  membuktikan adapter override diperlukan. Adapter test awal juga gagal karena
  class adapter belum tersedia.
- Perubahan UserManagement: menambah `UserRuntimeSettings`, DTO pagination/mail,
  serta `DefaultUserRuntimeSettings`. InviteUser memakai typed mail settings dan
  menerapkan expiry reset password; request/controller memakai typed pagination.
- Perubahan AuditLog: menambah `AuditRuntimeSettings`, DTO pagination, serta
  `DefaultAuditRuntimeSettings`. Retention tidak dimasukkan karena belum ada
  consumer runtime nyata.
- Perubahan SystemSetting: menambah adapter untuk kedua public port dan override
  scoped binding setelah dependency consumer terdaftar. Manifest SystemSetting
  mendeklarasikan direct dependency UserManagement; graph tetap acyclic dengan
  urutan yang sama.
- Temuan adversarial: architecture scanner pertama menolak adapter karena
  mengimpor concrete default Infrastructure milik consumer. Default fallback
  kemudian diekspos sebagai binding DTO Application public; scanner tidak
  dilonggarkan.
- Evidence: default, parity, invalid storage, manifest/import, graph/runtime,
  serta presentation UserManagement/AuditLog lulus 74 test/632 assertion.
  PHPStan dan Pint scope terkait lulus. `module:validate --json` exit 0 dengan
  empat module valid, boot plan canonical, dan diagnostic kosong.
- Batasan: password SMTP tetap berada pada typed port internal UserManagement
  karena InviteUser benar-benar memerlukannya. Nilai tersebut tidak masuk
  response, diagnostic, log, atau port AuditLog. Keamanan output tetap dijaga
  oleh test redaksi SystemSetting pada INC-01.
- Status: T05.1 sampai T05.4 selesai. Application AccessControl masih mengimpor
  model Infrastructure dan menjadi scope INC-06 berikutnya.

## 14 Agustus 2026 - INC-06 Boundary Application AccessControl

- Kondisi awal: CreateRole, DeleteRole, SyncRolePermissions,
  BuildAccessControlDashboard, dan AuthorizeRoleMutation mengimpor model Role
  atau Permission dari Infrastructure.
- Test merah: architecture gate menemukan import Infrastructure pada folder
  Application; resolution test gagal karena role repository, permission
  catalog, dan read repository belum tersedia.
- Perubahan contract: menambah `RoleRepository`, `PermissionCatalog`,
  `AccessControlReadRepository`, `RoleData`, `PermissionData`, serta
  `PermissionGroupData`. `AccessControlDashboardData` sekarang memegang list
  DTO typed dan mempertahankan shape JSON lama melalui `toArray()`.
- Perubahan Infrastructure: adapter Eloquent menangani persistence, validasi
  catalog, relation permission, mapping DTO, dan query dashboard. Provider
  memasang binding tanpa mengekspos model lewat Application.
- Perubahan Application/Presentation: action menerima role ID dan memakai
  repository; authorization menerima nama role. Controller dan policy tetap
  memiliki route model untuk coarse/resource guard lalu meneruskan scalar ke
  use case. Activity publisher tetap membungkus mutation dan audit dalam
  transaction yang sama.
- Evidence: create, delete, sync, duplicate/protected/invalid permission,
  actor permission, dashboard grouping, policy, dan capability lulus 34
  test/136 assertion. PHPStan serta Pint scope AccessControl lulus.
- Status: T06.1 sampai T06.3 selesai. API implementation specification T00.4
  menjadi dependency berikutnya sebelum response contract atau route baru.

## 14 Agustus 2026 - T00.4 Implementation Specification API

- Kondisi awal: matrix API global sudah approved, tetapi payload user, role,
  permission, assignment, impersonation, envelope error, correlation, dan
  acceptance per endpoint belum cukup rinci untuk test-first.
- Perubahan: `api-implementation-specification.md` mengunci request/resource,
  snake_case, ULID, pagination, authorization, scope, idempotency, rate limit,
  audit, redaction, error mapping, contoh envelope, serta matrix test.
- Sumber: `docs/02-DESIGN/02.01-API-SPEC.md`, baseline requirement/security,
  specification empat module, route/controller/resource existing, dan
  permission identity module.
- Temuan blocker: middleware serta reservation idempotency saat ini private
  milik SystemSetting. Memakainya langsung pada route AccessControl atau
  UserManagement akan membuat hidden dependency ke module yang boot paling
  akhir. Pertanyaan ownership ADR sudah diajukan kepada user; mutation API tidak
  akan dibuat dengan import private sebagai workaround.
- Status: T00.4 selesai untuk contract payload. T07 envelope dan endpoint read
  dapat dimulai; mutation T08/T09 menunggu keputusan ownership idempotency.

## 14 Agustus 2026 - INC-07 Canonical API Envelope

- Kondisi awal: API AuditLog dan SystemSetting merakit response sendiri.
  Pagination AuditLog masih menjadi nested envelope, error tidak memiliki
  contract tunggal, dan identifier/metadata internal AuditLog dapat melewati
  boundary resource.
- Test contract: `tests/Feature/ApiEnvelopeContractTest.php` mengunci envelope
  success/error, correlation ULID pada header dan body, pagination flat,
  authentication, not-found, validation, serta redaksi AuditLog.
- Perubahan: menambah `app/Http/ApiResponseFactory.php`; menyelaraskan controller
  API AuditLog/SystemSetting, resource `AuditLogApiResource`, middleware replay,
  serta exception renderer pada `bootstrap/app.php`. Response sekarang memakai
  `success/message/data/meta` atau `success/message/errors/code/meta`.
- Evidence behavior: suite AuditLog/SystemSetting/API contract lulus 34
  test/338 assertion. Pint dan PHPStan pada response factory, bootstrap, serta
  dua module lulus tanpa error.
- Risiko/batasan: T07 hanya menyelaraskan endpoint existing. Route UserManagement
  dan AccessControl masih mengikuti vertical slice T08/T09.
- Status: T07.1 dan T07.2 selesai.

## 14 Agustus 2026 - ADR-0003 dan Framework Idempotency Capability

- Keputusan user: contract, middleware, dan reservation lifecycle idempotency
  generik dipindahkan ke `packages/StarterKit`; SystemSetting tetap memiliki
  policy retention/rate serta migration/table. ADR-0003 ditetapkan `Accepted`.
- Kondisi awal: seluruh namespace idempotency private SystemSetting. Menggunakannya
  pada mutation UserManagement/AccessControl akan membuat hidden dependency dan
  melanggar boot order baseline.
- Test merah: contract package pada `StarterKitPackageTest` gagal karena
  interface/manager/middleware framework belum ada. Percobaan suite awal hang
  akibat warning import non-compound pada file test dan meninggalkan empat
  child Pest; PID test saja dihentikan, warning diperbaiki, lalu test dijalankan
  ulang per-case secara deterministik. Proses PHP language server user tidak
  dihentikan.
- Perubahan framework: menambah `IdempotencyRepository`, `RuntimeApiPolicy`, DTO,
  exception, `IdempotencyManager`, `EnforceIdempotency`, default policy 24/60,
  dan unavailable repository fail-closed. `StarterKitServiceProvider` memasang
  default binding; package tidak mengimpor namespace `App`.
- Perubahan SystemSetting: generic class private dihapus. Model, migration,
  repository Eloquent, prune command/scheduler tetap pada owner SystemSetting.
  `SystemSettingRuntimeApiPolicy` membaca retention/rate registry dan provider
  mengganti binding framework. Repository menerjemahkan storage failure menjadi
  exception framework yang dirender sebagai `503 SERVICE_UNAVAILABLE`.
- Perubahan composition/API: `AppServiceProvider` mendaftarkan limiter
  `system-api` melalui typed policy. Alias mutation menjadi `api.idempotency`.
  Renderer canonical menangani conflict 409 dan storage unavailable 503 tanpa
  exception mentah. Mutation tidak berjalan bila reservation gagal.
- Evidence test: unit contract, replay/hash canonical, retention, redaction,
  unavailable adapter, rate custom/default, conflict, expiry, rollback, prune,
  architecture, envelope, serta ownership schema lulus 35 test/251 assertion.
  PHPStan 0 error dan Pint lulus pada framework/module/bootstrap terkait.
- Evidence operasional: `module:validate --json` melaporkan empat module valid,
  boot plan canonical, dan diagnostic kosong. `route:list --path=api/v1 --json`
  menunjukkan route PATCH SystemSetting memakai `api.idempotency`.
- Evidence Composer: percobaan
  `composer update starterkit/framework --lock --no-install` ditolak karena
  opsi tidak kompatibel. Command sempit
  `composer update starterkit/framework --no-install` kemudian memperbarui satu
  reference path-package tanpa install/removal. `composer dump-autoload`
  menyegarkan classmap setelah deletion; warning PSR-4 hanya berasal dari
  fixture test lama. Root Composer valid; package valid dengan warning lama
  bahwa field `version` sebaiknya tidak ditulis untuk publikasi Packagist.
- Rollback/risk: migration dan data tidak berpindah sehingga rollback boundary
  tidak memerlukan migrasi data. Mutation module lain tetap harus gagal 503
  bila adapter persistence tidak aktif; tidak ada mode bypass idempotency.
- Status: T00.6 selesai dan blocker mutation T08/T09 ditutup.

## 14 Agustus 2026 - T08.1 API Read UserManagement

- Preflight: `module:discover`, `module:validate`, `module:list`, dan
  `module:inspect System/UserManagement` exit 0. Empat module valid, boot plan
  canonical, dan target memiliki dependency langsung AccessControl tanpa
  diagnostic.
- Kondisi awal: `Routes/api.php` UserManagement hanya berisi tag PHP. Query
  `ListUsers`/`GetUser` dan repository typed sudah tersedia untuk web, tetapi
  resource memakai camelCase dan filter belum mendukung sort field API.
- Test merah: tiga test API read gagal karena route `api.v1.users.index/show`
  belum terdefinisi. Test mengunci filter nested, pagination, sort allowlist,
  resource snake_case, correlation, 401, 403, 404, dan 422.
- Perubahan Application/Infrastructure: `UserListFilter` menambah sort field
  allowlist `created_at` atau `name`; repository hanya meneruskan field yang
  telah tervalidasi. Default web tetap `created_at desc` sehingga behavior lama
  tidak berubah.
- Perubahan Presentation: menambah `ListUsersApiRequest`, `UserApiResource`,
  `UserApiController`, serta GET list/detail pada `/api/v1/users`. Controller
  memakai query existing, runtime pagination consumer-owned, policy
  `viewAny`, limiter `system-api`, dan `ApiResponseFactory`.
- Temuan regression T07: denial route middleware berupa HTTP exception 403 dan
  awalnya dirender `HTTP_ERROR`. Renderer generik diperbaiki agar status 403
  selalu menjadi `FORBIDDEN` dengan message aman.
- Evidence: API read, canonical envelope, dan regression presentation web lulus
  29 test/277 assertion. Route list menampilkan dua route dengan auth, verified,
  throttle, dan policy middleware. Target module validate exit 0.
- Static analysis: command ad-hoc pertama memasukkan file Pest dan menghasilkan
  tujuh `PendingCalls\\TestCall` false positive karena tests berada di luar
  scope konfigurasi PHPStan. Tidak ada suppression atau perubahan test.
  Pengulangan pada source produksi UserManagement/bootstrap lulus 0 error; Pint
  dan `git diff --check` lulus.
- Risiko/batasan: slice ini belum membuat mutation. Scope policy saat ini sama
  dengan capability `user.view`; aturan scope resource yang lebih sempit harus
  ditambahkan pada contract/policy sebelum kebutuhan tenant/ownership muncul.
- Status: T08.1 selesai; T08.2 create menjadi increment berikutnya.

## 14 Agustus 2026 - T08.2 API Create UserManagement

- Kondisi awal: create user hanya tersedia melalui controller web. Action
  `CreateUser` sudah mengatur authorization, role assignment, transaction, dan
  audit, tetapi belum menerima correlation API dan duplicate database dapat
  keluar sebagai exception persistence mentah.
- Test merah: empat test gagal karena route `api.v1.users.store` belum ada.
  Contract test mengunci 201, resource aman, password hashing/redaction,
  correlation audit, replay tanpa duplikasi, duplicate 409, validation 422,
  401/403, status permission, idempotency header, dan rate limit 429.
- Perubahan request/controller: `StoreUserApiRequest` memvalidasi field, enum,
  password, dan role catalog lalu memetakan `CreateUserData`. Controller
  menghasilkan correlation ULID melalui response factory, meneruskannya ke
  action, dan mengembalikan resource canonical status 201.
- Perubahan Application/Infrastructure: `CreateUser` menerima correlation
  optional tanpa memutus caller web. Repository menerjemahkan SQLSTATE unique
  email `23000/23505` menjadi `DuplicateUserEmail`; renderer API memetakan
  exception ke `409 CONFLICT` tanpa pesan database.
- Security: password hanya melewati FormRequest dan DTO internal, lalu di-hash
  cast model. Response, stored replay body, audit metadata, dan error tidak
  membawa dummy password. Role/status permission tetap diperiksa di action;
  mutation gagal di-rollback bila izin tambahan tidak ada.
- Evidence: gabungan API read/create, presentation web, idempotency
  SystemSetting, dan envelope lulus 41 test/356 assertion. PHPStan source
  produksi 0 error. Pint, `git diff --check`, target module validation, serta
  route list lulus; POST route memiliki auth, verified, throttle,
  `api.idempotency`, dan policy create.
- Status: T08.2 selesai; T08.3 update profile/status menjadi increment berikutnya.

## 14 Agustus 2026 - T08.3 API Update Profile dan Status

- Authority reconciliation: global API spec dan implementation specification
  menetapkan satu `PATCH /api/v1/users/{user}`, sedangkan task downstream masih
  menyebut dua FormRequest. Implementasi mengikuti authority dengan satu
  FormRequest conditional; task diperbarui tanpa menambah endpoint non-canonical.
- Test merah: enam test awal gagal karena route update belum ada. Contract
  mengunci partial profile, status permission khusus, combined update,
  unchanged status, protected/not-found, duplicate, validation, replay,
  payload mismatch, correlation, audit, dan field-specific authorization.
- Perubahan domain/application: `InvalidUserStatusTransition` mempertahankan
  kompatibilitas `InvalidArgumentException`; `ChangeUserStatus` memakai
  `UserLifecycle` dan mengembalikan DTO terbaru. Update profile/status menerima
  correlation optional untuk audit API.
- Perubahan Presentation: `UpdateUserApiRequest` menolak payload kosong dan
  memetakan profile parsial/status typed. Policy `mutate` hanya menjadi coarse
  OR; `UpdateUser` dan `ChangeUserStatus` tetap menjadi security authority per
  field. Renderer memetakan protected/invalid transition ke 409 aman.
- Temuan persistence: duplicate email pada update awalnya menghasilkan 500 dan
  stack SQL pada test. Repository update sekarang menerjemahkan SQLSTATE unique
  ke `DuplicateUserEmail`, sama seperti create, sehingga response menjadi
  `409 CONFLICT` tanpa detail database.
- Temuan transaksi adversarial: dugaan bahwa transaction middleware membuat
  kombinasi profile/status atomik terbukti salah. Laravel merender exception
  controller menjadi response 409 sebelum middleware selesai, sehingga profile
  sempat commit saat status gagal. `UpdateUserProfileAndStatus` kemudian
  ditambahkan pada Application boundary untuk membungkus kedua action dalam
  repository transaction. Test membuktikan email dan audit profile ikut
  rollback ketika status gabungan invalid.
- Evidence: API read/create/update, lifecycle/application unit, dan regression
  web lulus 56 test/410 assertion. PHPStan source UserManagement/bootstrap 0
  error dan Pint lulus.
- Status: T08.3 selesai; T08.4 delete menjadi increment berikutnya.

## 14 Agustus 2026 - T08.4 API Soft Delete UserManagement

- Kondisi awal: soft delete hanya tersedia pada web. Action belum menerima
  reason/correlation, belum menolak self, dan target yang sudah terarsip dapat
  mencapai repository mutation non-canonical.
- Test merah: empat test gagal karena route destroy belum ada. Contract mengunci
  success `data: null`, audit reason/correlation, replay, 401/403,
  self/protected/archived/not-found, reason invalid/sensitif, rollback, serta
  tidak adanya audit duplikat.
- Perubahan domain/application: `SelfUserMutation` menjadi guard eksplisit.
  `SoftDeleteUser` menolak target self, protected, atau sudah terarsip dan
  meneruskan reason/correlation ke activity publisher tanpa mengubah caller web.
- Perubahan Presentation: `DeleteUserApiRequest` membatasi reason 500 karakter;
  policy `deleteAny` menjadi coarse permission dan action tetap memegang state
  rule. DELETE route memakai limiter/idempotency dan controller mengembalikan
  envelope null canonical. Sensitive reason ditolak oleh redactor AuditLog di
  dalam transaction sehingga delete ikut rollback.
- Evidence: seluruh API read/create/update/delete, unit lifecycle/application,
  presentation web, dan envelope lulus 63 test/507 assertion. PHPStan source
  produksi 0 error; Pint, target module validation, route list, dan
  `git diff --check` lulus. Route list kini cocok dengan lima endpoint user pada
  baseline API matrix.
- Batasan: API baseline hanya menyediakan soft delete. Restore dan force-delete
  tetap operasi web/internal dan tidak ditambahkan tanpa perubahan API spec.
- Status: T08.1 sampai T08.4 selesai; vertical slice API UserManagement lengkap.

## 14 Agustus 2026 - T09.1 API Read AccessControl

- Preflight: discover/validate/list/inspect AccessControl exit 0; empat module
  valid, boot plan canonical, dependency AccessControl kosong, dan diagnostic
  tidak ada.
- Kondisi awal: route API AccessControl kosong. Typed DTO/read repository T06
  hanya menyediakan dashboard penuh dan belum memiliki pagination/filter API.
- Test merah: tiga test gagal karena route role/permission belum terdefinisi.
  Contract mengunci role list/detail, permission catalog, filter module,
  pagination/sort, resource snake_case, 401/403/404/422, dan correlation.
- Perubahan Application: menambah filter serta paginated DTO typed dan query
  `ListRoles`, `GetRole`, `ListPermissions`. Contract read repository diperluas
  tanpa mengekspos model Infrastructure.
- Perubahan Infrastructure/Presentation: adapter Eloquent menerapkan query
  allowlist; controller hanya memanggil query dan memetakan DTO. Permission
  `module` diturunkan dari identity name. Policy view juga mengakui
  `access_control.permission.manage` selain manage role/assign permission.
- Evidence: API read, repository boundary, dan page regression lulus 21
  test/120 assertion. Tiga route memiliki auth, verified, throttle, dan policy.
  Target module validation dan `git diff --check` lulus.
- Static analysis: PHPStan menemukan `sortDirection` masih bertipe `string`
  sedangkan Eloquent menerima literal `asc|desc`. Dokumentasi resmi identifier
  `argument.type` dibuka melalui MCP `chrome-devtools`; DTO dipersempit dengan
  literal union, tanpa suppression/cast. PHPStan kemudian lulus 0 error dan
  Pint lulus.
- Status: T09.1 selesai; T09.2 mutation role menjadi increment berikutnya.

## 20 Agustus 2026 - Regression Literal Sensitif pada CLI SystemSetting

- Kondisi awal: jalur prompt tersembunyi dan `--value-stdin` sudah menolak
  argumen posisi sensitif, tetapi `normalizeInput()` masih mengubah string
  `123`, `true`, dan `null` menjadi integer, boolean, atau `null` sebelum
  definition sensitif memvalidasinya.
- Test merah: focused dataset menghasilkan dua error untuk literal angka dan
  boolean serta satu failure untuk literal `null` pada implementasi lama.
- Perubahan: `SetSystemSettingCommand` meneruskan input sensitif sebagai string
  ke `SettingDefinitionData`; normalisasi CLI lama tetap dipakai untuk setting
  non-sensitive. Output JSON tetap memakai `SystemSettingOutputPresenter`.
- Alasan: secret merupakan input tekstual opaque. Nilai yang bentuknya mirip
  tipe JSON tidak boleh berubah makna sebelum disimpan terenkripsi.
- Evidence: focused regression lulus 3 test/30 assertion; seluruh
  `SystemSettingSeederAndCommandTest` lulus 14 test/87 assertion; Pint lulus;
  PHPStan pada command lulus tanpa error; `git diff --check` lulus.
- Risiko/batasan: command tetap menghapus satu line ending terminal dari STDIN.
  Tidak ada secret yang dicatat pada output atau dokumen evidence.

## 20 Agustus 2026 - Quality Gate Checkpoint Arsitektur

- Kondisi awal: `composer ci:check` sempat gagal pada Pint untuk
  `CreateRole.php`, `DeleteRole.php`, `SyncRolePermissions.php`, dan
  `bootstrap/app.php`. Build produksi belum dijalankan terpisah karena tidak
  termasuk script `composer ci:check`.
- Perubahan: empat source tersebut diformat memakai Pint. Tidak ada contract
  atau behavior yang diubah oleh langkah formatting.
- Alasan: checkpoint arsitektur harus melewati lint, format, type check, seluruh
  backend test, dan frontend production build sebelum perubahan di-commit.
- Evidence: `composer ci:check` lulus; ESLint, Prettier, TypeScript, Pint,
  PHPStan, dan 376 Pest test/2.215 assertion selesai tanpa kegagalan.
  `npm run build` lulus setelah mentransformasi 2.744 module dalam 1 menit 1
  detik. `git diff --check` dijalankan kembali sebelum staging.
- Risiko/batasan: hasil ini menutup gate checkpoint arsitektur, bukan seluruh
  release gate. API AccessControl lanjutan, CI MySQL, Vitest, Playwright/axe,
  dan security workflow masih mengikuti task terbuka masing-masing.

## 20 Agustus 2026 - T09.2 API Mutation Role dan Sinkronisasi Contract

- Kondisi awal: create, update, dan delete role beserta test sudah tersedia,
  tetapi T09.2 masih terbuka. Matrix global juga belum mencantumkan endpoint
  detail serta delete role yang sudah ada pada implementation specification.
- Perubahan: `02.01-API-SPEC.md` menambahkan `GET /api/v1/roles/{role}` dan
  `DELETE /api/v1/roles/{role}` setelah user menyetujui keduanya. T09.2 dan
  Definition of Ready implementation specification disinkronkan dengan kondisi
  source aktual.
- Alasan: global API spec adalah authority. Route dan test tidak boleh menjadi
  contract tersembunyi yang berbeda dari matrix authoritative.
- Evidence: `php artisan test tests/Feature/AccessControlApiMutationTest.php`
  lulus 5 test/47 assertion. Source membuktikan middleware authentication,
  verification, limiter, idempotency, policy, typed Application Action, error
  canonical, rollback atomik, dan audit.
- Risiko/batasan: assignment role/direct permission serta impersonation belum
  termasuk increment ini dan tetap ditutup melalui T09.3 sampai T09.5.

## 20 Agustus 2026 - T09.3 API Assign dan Revoke Role User

- Kondisi awal: web memiliki sinkronisasi multi-role, tetapi API belum memiliki
  operasi additive assign/revoke. `RoleAssignmentCapability` hanya menerima
  nama role, sedangkan path revoke wajib memakai identifier ULID.
- Test merah: lima test gagal karena route `api.v1.users.roles.store` dan
  `api.v1.users.roles.destroy` belum tersedia.
- Perubahan: `RoleCatalogCapability` ditambah lookup typed berdasarkan ULID;
  `MutateUserRole` mengorkestrasi invariant target, public assignment
  capability, transaction audit, dan readback DTO. FormRequest, policy coarse
  permission, route idempotent, response resource, error `RoleNotFound`, serta
  label audit revoke ditambahkan pada boundary pemiliknya.
- Alasan: UserManagement memiliki target user dan workflow, sedangkan
  AccessControl tetap menjadi authority role. Tidak ada import model,
  repository, policy, atau service private lintas module.
- Evidence: focused test hijau 5 test/37 assertion. Regression application,
  presentation, dan authorization lulus total 46 test/274 assertion. PHPStan
  lulus 0 error; Pint lulus; target AccessControl/UserManagement valid tanpa
  diagnostic; route list menunjukkan auth, verified, throttle, policy, dan
  `api.idempotency` pada kedua route.
- Temuan static analysis: arrow function `void` sempat mengembalikan `null` dan
  menghasilkan `return.void`. Dokumentasi resmi PHPStan dibaca melalui MCP
  `chrome-devtools`, lalu callback diubah menjadi block closure side-effect
  tanpa suppression.
- Risiko/batasan: direct permission belum memiliki capability assignment dan
  tetap menjadi scope T09.4.

## 20 Agustus 2026 - T09.4 API Direct Permission User

- Kondisi awal: AccessControl belum memiliki public capability khusus direct
  permission. UserManagement dilarang memanggil model atau service Spatie
  private untuk memenuhi endpoint baseline.
- Test merah: lima test gagal karena route assign/revoke direct permission belum
  tersedia.
- Perubahan: AccessControl menambah `DirectPermissionAssignmentCapability`,
  `SpatieDirectPermissionAssignmentAdapter`, dan error typed
  `PermissionNotFound`. UserManagement menambah `MutateUserPermission`,
  FormRequest, policy coarse permission, controller orchestration, route ULID,
  serta event audit assign/revoke.
- Alasan: ownership permission identity serta adapter Spatie tetap pada
  AccessControl. Ownership target user, invariant protected, transaction audit,
  dan resource response tetap pada UserManagement.
- Evidence: focused test hijau 5 test/33 assertion. Regression role,
  application, presentation, dan authorization lulus total 51 test/307
  assertion. PHPStan lulus 0 error; Pint lulus; AccessControl/UserManagement
  valid tanpa diagnostic; route list menunjukkan middleware lengkap.
- Risiko/batasan: resource user canonical tidak mengekspos daftar direct
  permission agar response tetap minimal. Test memastikan state persistence
  melalui `hasDirectPermission()` dan response tidak membawa field sensitif.

## 20 Agustus 2026 - T09.5 API Impersonation

- Kondisi awal: start/end impersonation hanya tersedia pada web. Session adapter
  sudah memisahkan actor dan target, tetapi start API memiliki risiko replay
  berubah owner setelah guard berpindah ke target.
- Test merah: lima test gagal karena route API start/end belum tersedia.
- Perubahan: API menambah FormRequest reason 10-500 karakter, error typed,
  `ImpersonationStateData`, `EndImpersonation`, route idempotent, serta response
  display-only. `StartImpersonation` kini menolak self dan mengembalikan state
  typed. Session contract menerima correlation ID dan melaporkan active state.
  Middleware idempotency memilih actor asli dari session impersonation sebelum
  fallback ke user guard saat ini.
- Alasan: perubahan identity tidak boleh memutus ownership reservation atau
  membuat retry melakukan mutation kedua. Response API tidak membutuhkan
  identifier/session context internal untuk menyatakan keberhasilan.
- Evidence: focused test hijau 5 test/53 assertion. Regression impersonation,
  direct permission, role, application, presentation, dan framework
  idempotency lulus total 58 test/396 assertion. PHPStan lulus 0 error; Pint
  lulus; target UserManagement valid; route list menunjukkan start/end dengan
  auth, verified, throttle, policy start, dan idempotency.
- Risiko/batasan: session impersonation tetap stateful dan hanya berlaku pada
  guard web internal sesuai ADR-0002. Public token-based API tetap di luar
  baseline.

## 20 Agustus 2026 - T09.6 Route Matrix dan Checkpoint API

- Kondisi awal: matriks API belum memuat dua endpoint SystemSetting, route
  AuditLog belum memakai limiter `system-api`, dan belum ada automated parity
  maupun controller-boundary test.
- Test merah: `ApiRouteMatrixTest` gagal pada dua selisih SystemSetting dan
  middleware limiter AuditLog. Setelah focused test hijau, full suite menemukan
  `MutateUserRole` mengimpor `RoleNotFound` dari private Domain AccessControl.
- Perubahan: matriks authoritative kini memuat 21 route dan OD-API-001 sampai
  OD-API-003 dinyatakan resolved sesuai implementation specification yang telah
  disetujui. AuditLog memakai limiter runtime. Test membandingkan matriks dengan
  router, memeriksa security/idempotency middleware, serta melarang Eloquent,
  validasi, dan business mutation langsung pada API controller. Failure role
  dan permission dipindahkan ke public `Application/Contracts/Exceptions`.
- Alasan: contract dokumentasi dan runtime harus identik, seluruh API wajib
  memperoleh limiter yang sama, dan consumer tidak boleh mengetahui private
  domain exception module lain.
- Evidence: focused route/architecture gate lulus 3 test/156 assertion; focused
  boundary dan role/permission regression lulus 13 test/85 assertion.
  `composer ci:check` lulus 394 test/2.525 assertion; ESLint, Prettier,
  TypeScript, Pint, serta PHPStan lulus tanpa error.
- Risiko/batasan: Checkpoint API selesai. Coverage threshold, CI MySQL upgrade,
  Vitest, Playwright/axe, security workflow, dan manual interactive browser flow
  tetap dilanjutkan pada Phase 4.

## 20 Agustus 2026 - T10 Quality Gate dan Upgrade MySQL

- Kondisi awal: PHPStan belum menganalisis seluruh package/tool, coverage
  backend tidak memiliki threshold, workflow MySQL hanya membuktikan fresh
  install, dan tidak ada fixture release lama.
- File diubah: `composer.json`, `phpstan.neon`, `phpunit.xml`, workflow test,
  verifier coverage, migration legacy, fixture SQL, verifier upgrade,
  ADR-0006, migration runbook, CI/CD docs, serta test verifier.
- Perubahan: PCOV menghasilkan Clover/JUnit dan threshold 80%; PHPStan mencakup
  `packages/StarterKit/src` serta `tools/ci`; MySQL memiliki job fresh dan
  upgrade. Migration forward-only memetakan users/sessions/passkeys BIGINT ke
  ULID, memeriksa count/orphan, dan menolak rollback numerik.
- Alasan: fresh install tidak membuktikan keselamatan data release lama, dan
  quality gate harus gagal secara deterministik saat coverage menurun.
- Evidence: `composer ci:check` lulus 398 test/2.547 assertion dengan PHPStan 0.
  Verifier threshold memiliki positive/negative test. Rehearsal MySQL
  terisolasi mempertahankan dua user, satu session, dan satu Passkey; seed kedua
  idempotent; database disposable dihapus.
- Risiko/batasan: backend coverage aktual dijalankan oleh PCOV pada hosted CI
  karena driver coverage lokal tidak tersedia. First hosted run adalah release
  evidence milik release maintainer sebelum merge/deploy.

## 20 Agustus 2026 - T11 Frontend, Browser, Accessibility, dan Security

- Kondisi awal: tidak ada Vitest, Playwright/axe, browser workflow, CodeQL,
  Dependency-Check, verifier SARIF severity, atau artifact retention eksplisit.
- File diubah: package manifest/lock, Vitest/Playwright config, browser tests,
  helper runtime/accessibility, frontend tests, CSS/component UI, workflow
  browser/security, Dependabot, build contamination guard, SARIF verifier, serta
  CI/security docs.
- Perubahan: critical flow empat module berjalan pada Chromium desktop
  `1440x1000` dan Pixel 5. Axe menolak serious/critical violation setelah
  transition dinonaktifkan hanya saat audit. Browser runtime memakai SQLite
  disposable, credential acak bermask, mail sink `null`, screenshot failure
  allowlist, tanpa trace/video/session/database artifact.
- Temuan yang diperbaiki: contrast destructive/toast, badge dark mode,
  positive tab index, keyboard show-password, identity mobile, race transition
  dark mode, dan sinkronisasi empty state AuditLog. Build guard juga menolak
  test yang sempat masuk page discovery; test dipindahkan ke `tests/Frontend`.
- Evidence: Vitest lulus 5 file/10 test dengan statement 90,69%, branch 97,36%,
  function 89,47%, dan line 90,24%. Node SARIF verifier lulus 3/3. Production
  build mentransformasi 2.745 module dan bebas entry/dependency test. Playwright
  final lulus 4/4 pada database baru. Composer/npm audit bersih; 540 package
  memiliki registry signature dan 186 memiliki attestation.
- Risiko/batasan: CodeQL hanya mendukung JavaScript/TypeScript. PHP dicakup
  PHPStan/Larastan, Pest, coverage, Composer audit, dan OWASP. Hosted scan tetap
  wajib dijalankan sebelum merge/deploy.

## 20 Agustus 2026 - T11.8 Browser Manual dan Failure Invitation

- Kondisi awal: flow read-only sudah lulus, tetapi mutation, loading, toast,
  focus, empty/error, dan cleanup failure belum terbukti lengkap melalui MCP.
- MCP yang dipakai: hanya server `chrome-devtools`; tidak memakai Browser,
  @Chrome, computer use, atau `node_repl`.
- Flow manual: AccessControl membuat lalu menghapus role dummy; UserManagement
  menguji shortcut/search/empty state dan invitation; SystemSetting menguji
  shortcut `/` serta empty state; AuditLog menguji shortcut `/`, loading
  disabled, empty state, reset filter, console, dan daftar URL/status request.
- Temuan kritis: SMTP yang tidak tersedia melempar exception sesudah user
  dibuat, sehingga satu dummy tertinggal dan diagnostic 500 tampil. Sesi lama
  langsung di-logout melalui UI. User dummy exact dihapus setelah target
  diverifikasi, lalu backend memperoleh cleanup serta safe validation contract.
- Temuan UI: dialog tidak merender error server dan page menganggap validation
  field sebagai load failure. Keduanya diperbaiki dengan regression backend,
  frontend, dan source test sebelum flow diulang.
- Evidence akhir: tombol `Mengirim...` disabled; error email aman tampil sebagai
  alert dan tidak ada overlay 500; dua email dummy count 0; SystemSetting dan
  AuditLog empty state lulus; request AuditLog 200; console tanpa error/warning;
  filter dikembalikan ke state awal. Mutation sukses/toast dan 403 role negatif
  dilengkapi oleh Playwright disposable.
- Risiko/batasan: entry AuditLog append-only dari mutation manual tetap
  dipertahankan sebagai histori; tidak memuat credential atau raw secret.

## 20 Agustus 2026 - T12 Release Gate Lokal dan Sinkronisasi

- Kondisi awal: README, implementation plan, task, dan log masih menyebut
  upgrade, automation, browser flow, serta final gate sebagai pekerjaan terbuka.
- File diubah: README remediation, implementation plan, task checklist,
  execution log, UserManagement specification/log, CI/CD, security design,
  ADR-0006, migration runbook, dan README module.
- Perubahan: status disinkronkan dengan source serta evidence aktual. Hosted
  GitHub Actions dipisahkan sebagai release evidence eksternal dengan owner
  release maintainer; tidak diklaim telah berjalan dari workspace lokal.
- Evidence: `composer ci:check` lulus 398 test/2.547 assertion; PHPStan 0;
  frontend coverage 10/10 di atas 80%; build 2.745 module; Playwright 4/4;
  module discover/validate/list/inspect empat target exit 0 dengan boot plan
  canonical; route matrix memuat 21 endpoint; Composer/npm audit bersih.
- Evidence dokumentasi: link checker memeriksa 687 link lokal pada 220 file;
  terminology scan tidak menemukan status stale atau checklist terbuka pada
  program remediation; Prettier Markdown, parser YAML empat konfigurasi, dan
  `git diff --check` lulus.
- Risiko/batasan: perubahan terakhir belum di-commit/push karena user tidak
  memberi perintah Git baru setelah checkpoint sebelumnya. First hosted run,
  required check, dan production deploy berada di luar mutation lokal ini.
