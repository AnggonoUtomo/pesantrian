# Tasks: Remediasi Kode dan Dokumentasi System

## Aturan Eksekusi

- Kerjakan satu task pada satu waktu dan jangan melewati dependency.
- Gunakan test-driven development untuk perubahan behavior atau bug.
- Setiap task dimulai dengan pemeriksaan working tree agar perubahan user tidak
  tertimpa.
- Checklist hanya diubah menjadi `[x]` setelah command verifikasi dijalankan dan
  hasilnya ditulis pada execution log.
- Dependency install, commit, branch, dan migration destructive memerlukan
  izin sesuai aturan project.

## Phase 0 - Governance dan Contract

- [x] T00.1 Selaraskan authoritative documentation yang hilang atau stale.
    - Kondisi awal: `docs/AI-PROMPT-GUIDE.md` hilang; `docs/README.md` masih
      menyebut constraint PHP lama; specification UserManagement dan README
      ModuleEvaluation tidak sama dengan implementasi/status checklist.
    - File dibaca: `AGENTS.md`, `docs/AGENTS.md`, `docs/README.md`, riwayat Git,
      specification/README UserManagement, dan dokumen ModuleEvaluation.
    - Perubahan: pertahankan penghapusan prompt guide lama dan hapus kewajibannya
      dari `AGENTS.md`; perbaiki PHP constraint serta status scope/evaluasi.
    - Alasan: coding tidak boleh dimulai dengan source authoritative yang hilang
      atau saling bertentangan.
    - Acceptance: isi tidak mengarang Open Decision; semua link relatif valid;
      status dokumentasi sesuai kode dan checklist nyata.
    - Command/test: link checker docs, `rg` istilah stale, dan `git diff --check`.
    - Dependency: tidak ada.
    - Risiko/rollback: jangan menghapus riwayat keputusan; perubahan dibatasi pada
      dokumentasi dan dapat ditinjau per file.
    - Evidence: keputusan user 14 Agustus 2026; reference `AI-PROMPT-GUIDE` aktif
      tidak tersisa; README PHP, UserManagement, dan ModuleEvaluation diselaraskan;
      link checker serta `git diff --check` lulus.

- [x] T00.2 Tulis dan setujui ADR runtime module bootstrap.
    - Kondisi awal: BL-007 mewajibkan isolation, tetapi provider module masih
      statis dan status manifest tidak mengontrol runtime.
    - File dibaca: ADR-0001/0003 global, `ModuleRegistry`, provider framework,
      `bootstrap/providers.php`, module guide, dan technical spec.
    - Perubahan: catat authority registry, graph validation, boot order,
      diagnostic, failure isolation, config/route cache, dan rollback.
    - Alasan: mengganti composition root merupakan keputusan mahal untuk dibalik.
    - Acceptance: ADR berstatus accepted sebelum T03/T04; alternatif provider
      statis dan failure mode dijelaskan.
    - Command/test: review manual serta link checker.
    - Dependency: T00.1.
    - Evidence: `decisions/ADR-0001-DYNAMIC-MODULE-RUNTIME-BOOTSTRAP.md`
      berstatus `Accepted - 14 Agustus 2026` dan mencatat failure contract,
      acceptance, cache, alternatif, serta rollback.

- [x] T00.3 Tulis dan setujui ADR consumer-owned runtime-setting port.
    - Kondisi awal: UserManagement/AuditLog mengimpor contract SystemSetting yang
      tidak tercatat pada manifest; dependency langsung akan membuat cycle.
    - File dibaca: ADR module communication, tiga manifest, public contract
      SystemSetting, controller/request/action consumer.
    - Perubahan: catat ownership port, adapter default, adapter SystemSetting,
      binding order, disabled-module behavior, dan larangan cycle.
    - Acceptance: dependency order baseline tetap utuh dan tidak ada concrete
      cross-module dependency pada keputusan accepted.
    - Command/test: diagram dependency dan review manifest.
    - Dependency: T00.1.
    - Evidence: `decisions/ADR-0002-CONSUMER-OWNED-RUNTIME-SETTING-PORT.md`
      berstatus `Accepted - 14 Agustus 2026`; arah adapter tetap
      `SystemSetting -> consumer contract` dan graph manifest acyclic.

- [x] T00.4 Lengkapi implementation specification API.
    - Kondisi awal: global API matrix ada, tetapi payload/resource/error contract
      user, role, permission, dan impersonation belum cukup untuk implementasi.
    - File dibaca: API spec global dan specification AccessControl,
      UserManagement, serta AuditLog.
    - Perubahan: tulis field wajib/opsional, ULID, validation, authorization,
      scope, pagination, idempotency, rate limit, audit, response, dan error code.
    - Acceptance: setiap endpoint baseline mempunyai contoh request/response,
      positive/negative acceptance, serta owner module.
    - Command/test: review matrix endpoint dan link checker.
    - Dependency: T00.1.
    - Evidence: `api-implementation-specification.md` menetapkan schema user,
      role, permission, assignment, impersonation, AuditLog, dan SystemSetting;
      envelope, snake_case, ULID, pagination, correlation, rate limit,
      idempotency, error mapping, redaction, positive/negative acceptance, serta
      rollback tercatat. Ownership runtime idempotency awalnya menjadi blocker
      implementasi mutation dan diselesaikan melalui T00.6/ADR-0003.

- [x] T00.5 Sinkronkan status ADR-0005 UserManagement.
    - Kondisi awal: implementation dan seluruh task identity/avatar/login sudah
      selesai, tetapi ADR-0005 masih `Proposed` dan menyimpan Open Decision disk
      avatar yang tidak sama dengan source saat ini.
    - File dibaca/diubah: ADR-0005, README/specification increment identity,
      media config/model/route, dan execution evidence.
    - Perubahan: tetapkan status serta keputusan disk/URL berdasarkan keputusan
      user; jangan mengubah Open Decision hanya dari inference source.
    - Acceptance: ADR tidak bertentangan dengan implementasi dan tidak ada Open
      Decision yang sebenarnya sudah diputuskan tanpa catatan approval.
    - Command/test: review manual, link checker, dan `git diff --check`.
    - Dependency: keputusan user tentang ADR-0005.
    - Evidence: user menerima ADR-0005 pada 14 Agustus 2026; ADR kini mencatat
      disk `local` privat, route berpolicy `view`, batas perubahan ke URL public,
      dan source implementasi yang diverifikasi. Link checker dan
      `git diff --check` lulus.

- [x] T00.6 Tetapkan dan implementasikan ADR capability idempotency framework.
    - Kondisi awal: contract, DTO, manager, exception, middleware, repository,
      model, dan migration idempotency seluruhnya berada pada namespace private
      SystemSetting. Mutation module lain tidak dapat menggunakannya tanpa hidden
      dependency ke module yang boot paling akhir.
    - File dibaca/diubah: implementation specification API, source idempotency
      SystemSetting, provider package/aplikasi/module, route dan exception
      renderer, `packages/StarterKit/composer.json`, test runtime/architecture,
      specification serta README downstream.
    - Perubahan: pindahkan contract, DTO, exception, manager, middleware, default
      policy, dan unavailable adapter ke `packages/StarterKit`; pertahankan model,
      migration, repository Eloquent, prune, scheduler, dan policy adapter pada
      SystemSetting. Named rate limiter mengambil typed policy dari container.
    - Alasan: lifecycle reservation adalah mekanisme HTTP reusable, sedangkan
      schema dan konfigurasi runtime tetap memiliki owner SystemSetting.
    - Acceptance: package tidak mengimpor `App`; mutation gagal tertutup 503 bila
      persistence tidak tersedia; retention/rate custom, replay, conflict,
      expiry, rollback, redaction, prune, dan ownership migration terbukti.
    - Command/test: focused Pest, PHPStan, Pint, Composer validate/dump-autoload,
      `module:validate --json`, `route:list --path=api/v1 --json`, link checker,
      dan `git diff --check`.
    - Dependency: persetujuan user 14 Agustus 2026 dan T00.4.
    - Evidence: ADR-0003 berstatus `Accepted`; focused gate lulus 35 test/251
      assertion, PHPStan 0 error, Pint lulus, empat module valid tanpa diagnostic,
      dan route mutation SystemSetting memakai alias `api.idempotency`. Composer
      lock path-package diperbarui tanpa install; package validate hanya membawa
      warning lama tentang field `version`.

### Checkpoint 0

- [x] Source authoritative tidak memiliki konflik terbuka.
- [x] ADR T00.2, T00.3, dan T00.6 disetujui.
- [x] API specification siap menjadi input test-first.
    - Evidence: endpoint matrix global sudah memiliki implementation schema dan
      acceptance test pada `api-implementation-specification.md`.
- [x] Working tree user tercatat dan tidak disentuh.

## Phase 1 - Security SystemSetting

- [x] T01.1 Tambah regression test redaksi output CLI.
    - Kondisi awal: `system-setting:get` dan JSON `system-setting:set` membawa raw
      result value.
    - File dibaca/diubah: test command SystemSetting; command get/set hanya dibaca
      untuk characterization.
    - Perubahan: tambah positive test nilai biasa dan negative test dummy secret
      untuk mode table serta JSON.
    - Alasan: test harus gagal pada behavior lama sebelum perbaikan.
    - Acceptance: test membuktikan dummy secret tidak muncul pada stdout/stderr;
      metadata key/source tetap dapat digunakan.
    - Command/test: focused Pest test command SystemSetting.
    - Dependency: T00.1; contract redaksi sensitive mengikuti baseline security
      dan tidak bergantung pada specification endpoint API baru T00.4.
    - Risiko/rollback: gunakan dummy value; jangan memakai credential environment.
    - Evidence: test merah gagal karena dummy secret muncul pada table get dan
      JSON set. Setelah T01.2, focused test lulus 4 test/35 assertion; fixture
      hanya memakai dummy lokal dan metadata key/source tetap tersedia.

- [x] T01.2 Implementasikan presenter aman untuk CLI.
    - Kondisi awal: command memakai `SettingValueData` internal langsung.
    - File dibaca/diubah: definition registry, presenter/DTO publik baru, command
      get, command set, dan service provider bila binding diperlukan.
    - Perubahan: resolve metadata sensitive lalu keluarkan nilai redacted atau
      metadata `has_value`; nilai non-sensitive mempertahankan contract disetujui.
    - Alasan: typed internal reader tetap boleh membawa value untuk runtime,
      sedangkan boundary output harus selalu aman.
    - Acceptance: T01.1 hijau; error tidak membawa exception message/stack trace.
    - Command/test: focused test, Pint, dan PHPStan pada file terkait.
    - Dependency: T01.1.
    - Evidence: `SystemSettingOutputPresenter` memisahkan internal typed value dari
      output publik; sensitive menghasilkan `value: null`, `sensitive`, dan
      `has_value`. PHPStan presentation, Pint, dan focused command test lulus.

- [x] T01.3 Tambah regression test dan redaksi API update.
    - Kondisi awal: `SystemSettingResource` menyalin `SettingValueData->value`
      pada response update.
    - File dibaca/diubah: presentation test, API controller, resource, public DTO
      atau presenter, dan specification SystemSetting.
    - Perubahan: test merah lebih dahulu, lalu response sensitive hanya membawa
      metadata aman. List dan update memakai kebijakan redaksi yang sama.
    - Acceptance: dummy secret tidak ada pada response body/log/audit; response
      non-sensitive mengikuti contract.
    - Command/test: focused presentation/API test dan route test.
    - Dependency: T01.2.
    - Evidence: test merah API gagal karena response update membawa dummy secret.
      Setelah resource/controller memakai presenter yang sama, suite command dan
      presentation lulus 18 test/118 assertion; PHPStan, ESLint, TypeScript, dan
      Pint lulus. Response list/update dan audit tidak memuat dummy secret.

- [x] T01.4 Amankan input CLI untuk setting sensitif.
    - Kondisi awal: `system-setting:set {key} {value}` menempatkan secret pada
      process argument dan berpotensi meninggalkannya pada shell history.
    - File dibaca/diubah: command set, command test, specification, README, dan
      implementation plan SystemSetting.
    - Perubahan: pertahankan argumen posisi untuk setting non-sensitive; tolak
      argumen posisi sensitive; gunakan prompt tersembunyi saat interaktif atau
      `--value-stdin` untuk otomasi.
    - Alasan: nilai sensitif tidak boleh memasuki channel command yang dapat
      diamati user/process lain.
    - Acceptance: positional secret gagal tanpa tersimpan/tercetak; hidden prompt
      dan STDIN sukses; output tetap teredaksi; nilai biasa tetap kompatibel.
    - Command/test: focused command test, Pint, dan PHPStan.
    - Dependency: persetujuan user 14 Agustus 2026 dan T01.2.
    - Evidence: test merah menghasilkan satu failure dan dua error pada contract
      lama; implementasi lulus dalam suite command 11 test/57 assertion dan suite
      security gabungan 32 test/162 assertion. Regression 20 Agustus 2026 juga
      membuktikan literal sensitif `123`, `true`, dan `null` dari STDIN tidak
      berubah menjadi tipe non-string; suite command lulus 14 test/87 assertion.
      Pint serta PHPStan command lulus.

- [x] T02.1 Buktikan fallback untuk ciphertext rusak.
    - Kondisi awal: test persistence belum mencakup `DecryptException`.
    - File dibaca/diubah: persistence test SystemSetting dan fixture record.
    - Perubahan: tambah test corrupted ciphertext untuk single/many read dan
      simulasi key mismatch yang terisolasi.
    - Acceptance: test merah pada implementasi lama tanpa mencetak ciphertext.
    - Command/test: focused persistence test.
    - Dependency: T01.3.
    - Evidence: dua test merah gagal dengan `The payload is invalid` dan
      `The MAC is invalid` pada implementasi lama; fixture tidak mencetak
      ciphertext atau secret pada diagnostic.

- [x] T02.2 Bungkus kegagalan decrypt dan gunakan default aman.
    - Kondisi awal: repository hanya menangkap `JsonException`; reader hanya
      menangkap exception domain/validation.
    - File dibaca/diubah: Eloquent repository, database reader, exception domain,
      dan test T02.1.
    - Perubahan: ubah exception crypto menjadi `SettingStorageUnavailable` dan
      pertahankan diagnostic allowlist.
    - Acceptance: single/many read mengembalikan `source=default`; tidak ada 500,
      raw value, ciphertext, atau stack trace.
    - Command/test: focused persistence/application test, Pint, PHPStan.
    - Dependency: T02.1.
    - Evidence: repository mengubah `DecryptException` dan JSON invalid menjadi
      `SettingStorageUnavailable`; single/batch reader memakai default. Focused
      persistence suite lulus 11 test/33 assertion, PHPStan dan Pint lulus.

### Checkpoint 1 - Security

- [x] Focused SystemSetting positive/negative suite lulus.
    - Evidence: command, presentation, dan persistence lulus 33 test/173
      assertion; mencakup redaksi, input secret, authorization, dan decrypt gagal.
- [x] `composer ci:check` lulus.
    - Evidence: ESLint, Prettier, TypeScript, Pint, PHPStan, dan Pest lulus; Pest
      terbaru pada 20 Agustus 2026 menghasilkan 376 test/2.215 assertion setelah
      regression literal sensitif dan formatting source diselesaikan.
- [x] Pencarian dummy secret pada output/log test tidak menemukan hasil.
    - Evidence: `rg` pada `storage/logs/*.log` menghasilkan
      `DUMMY_SECRET_LOG_SCAN=CLEAN`; test juga memeriksa stdout, JSON, dan audit.
- [x] Execution log memuat file, alasan, command, hasil, dan risiko.
    - Evidence: bagian INC-01, input CLI sensitif, dan INC-02 mencatat test merah,
      perubahan, focused/full command, serta batasan producer STDIN dan batch.

## Phase 2 - Runtime dan Boundary Module

- [x] T03.1 Tambah fixture/test dependency graph ModuleRegistry.
    - Kondisi awal: fixture registry belum membuktikan dependency hilang, cycle,
      disabled module, provider invalid, dan topological order secara lengkap.
    - File dibaca/diubah: `ModuleRegistryTest`, fixture module, manifest parser,
      dan contract diagnostic.
    - Perubahan: tambah test merah untuk setiap failure mode dan urutan valid.
    - Acceptance: diagnostic code serta module owner deterministik; module valid
      lain tetap ada pada hasil discovery.
    - Command/test: focused `ModuleRegistryTest`.
    - Dependency: ADR T00.2 accepted.
    - Evidence: enam test baru membuktikan missing dependency, self-dependency,
      cycle, disabled/dependent, provider invalid, peer isolation, dan urutan
      topological. Test awal gagal karena `bootPlan()` belum tersedia.

- [x] T03.2 Implementasikan graph validator tanpa side effect runtime.
    - Kondisi awal: discovery belum menjadi source urutan dependency runtime.
    - File dibaca/diubah: ModuleRegistry, manifest DTO/parser, graph validator
      baru, dan diagnostic DTO.
    - Perubahan: validasi existence, self-dependency, cycle, status, provider,
      serta topological sort.
    - Acceptance: T03.1 hijau; hasil graph reusable oleh command dan bootstrap.
    - Command/test: unit test registry, Pint, PHPStan.
    - Dependency: T03.1.
    - Evidence: `ModuleGraphValidator` menghasilkan boot plan/diagnostic stabil;
      production plan adalah AccessControl, UserManagement, AuditLog,
      SystemSetting tanpa diagnostic. Unit registry/manifest lulus 18 test/46
      assertion; PHPStan dan Pint lulus.

- [x] T03.3 Selaraskan command discovery/validate/list/inspect.
    - Kondisi awal: command dapat menghasilkan sudut pandang diagnostic yang
      berbeda jika tidak memakai graph result yang sama.
    - File dibaca/diubah: empat command module, formatter JSON, dan command test.
    - Perubahan: semua command memakai graph/diagnostic canonical.
    - Acceptance: JSON stabil, nilai sensitif/path internal tidak bocor, dan
      valid module yang tidak terkait tetap dapat diinspeksi.
    - Command/test: focused module command tests dan empat command nyata.
    - Dependency: T03.2.
    - Evidence: empat command memakai `bootPlan()` dan field diagnostic
      `code/module/phase/path/message`. Command/registry/manifest suite lulus 29
      test/108 assertion. Empat command production nyata exit 0, boot plan sama,
      dan diagnostic kosong.

- [x] T04.1 Tambah runtime bootstrap test.
    - Kondisi awal: test `isolates` hanya memeriksa array discovery, bukan Laravel
      bootstrap.
    - File dibaca/diubah: feature test bootstrap baru, fixture provider valid,
      disabled, invalid, dan throwing provider.
    - Perubahan: test merah membuktikan registration/boot sebenarnya.
    - Acceptance: invalid/disabled tidak terdaftar; valid peer tetap boot;
      diagnostic dapat dibaca tanpa menjatuhkan aplikasi.
    - Command/test: focused bootstrap feature test.
    - Dependency: T03.3.
    - Evidence: `tests/Unit/ModuleRuntimeBootstrapTest.php` dan fixture canonical
      `Tests\\Fixtures\\RuntimeModules\\{Module}\\ServiceProvider` membuktikan
      enabled/disabled/invalid provider, urutan register/boot, register failure,
      boot failure, dependent isolation, peer survival, dan redaksi exception.
      Focused suite lulus 3 test/18 assertion; Pint dan PHPStan scope runtime
      lulus tanpa ignore atau baseline.

- [x] T04.2 Implementasikan composition bootstrap provider.
    - Kondisi awal: module provider hardcoded pada `bootstrap/providers.php`.
    - File dibaca/diubah: `StarterKitServiceProvider`, bootstrapper baru,
      `bootstrap/providers.php`, config package bila perlu, dan test T04.1.
    - Perubahan: register provider valid/enabled dalam topological order.
    - Acceptance: test T04.1 hijau; empat module production tetap ditemukan;
      `config:cache` dan `route:cache` berhasil.
    - Command/test: bootstrap test, module command, cache command, full suite.
    - Dependency: T04.1.
    - Risiko/rollback: composition root rawan membuat Artisan gagal boot; hentikan
      increment bila valid peer tidak dapat boot, jangan lanjut ke T05.
    - Evidence: `ModuleRuntimeServiceProvider` memakai boot plan canonical dan
      `ModuleRuntimeState` menyimpan status/diagnostic allowlist. Provider module
      statis dihapus dari `bootstrap/providers.php`. Focused graph/runtime/command
      suite lulus 29 test/164 assertion dan full `composer ci:check` lulus 325
      test/1.606 assertion. Empat command module exit 0 dengan boot plan
      `AccessControl -> UserManagement -> AuditLog -> SystemSetting` serta
      diagnostic kosong. `config:cache`, `route:cache`, focused `route:list`, dan
      `migrate:status` berhasil; cache verifikasi sudah dibersihkan.

- [x] T05.1 Buat port runtime setting milik UserManagement.
    - Kondisi awal: invitation, pagination, dan controller UserManagement
      mengimpor contract SystemSetting.
    - File dibaca/diubah: contract baru, default adapter, InviteUser,
      ListUsersRequest, UserController, serta focused test dalam task terpisah jika
      jumlah file melewati batas.
    - Perubahan: consumer memakai vocabulary dan safe default miliknya sendiri.
    - Acceptance: tidak ada import UserManagement -> SystemSetting; invitation
      dan pagination tetap sama ketika adapter runtime tersedia.
    - Command/test: UserManagement application/presentation test.
    - Dependency: ADR T00.3 accepted dan T04.2.
    - Evidence: `UserRuntimeSettings`, `UserPaginationSettings`, dan
      `InvitationMailSettings` menjadi vocabulary public consumer.
      `DefaultUserRuntimeSettings` membaca config milik UserManagement/Laravel;
      InviteUser, request, dan controller tidak lagi mengimpor SystemSetting.
      Default contract test lulus dan invitation expiry sekarang diterapkan
      eksplisit ke password broker saat undangan dikirim.

- [x] T05.2 Buat port runtime setting milik AuditLog.
    - Kondisi awal: controller/request AuditLog mengimpor
      `SystemRuntimeSettings`.
    - File dibaca/diubah: contract baru, default adapter, controller, request,
      query/filter test.
    - Perubahan: AuditLog memiliki default pagination/retention yang aman.
    - Acceptance: tidak ada import AuditLog -> SystemSetting; filter dan
      pagination tetap tervalidasi.
    - Command/test: focused AuditLog presentation/application test.
    - Dependency: ADR T00.3 accepted dan T04.2.
    - Evidence: `AuditRuntimeSettings` dan `AuditPaginationSettings` hanya
      membawa pagination yang benar-benar dipakai. Provider AuditLog memasang
      `DefaultAuditRuntimeSettings`; request/controller tidak lagi mengimpor
      SystemSetting. Retention tidak ditambahkan karena belum memiliki consumer
      runtime pada scope ini.

- [x] T05.3 Tambah adapter SystemSetting dan parity test.
    - Kondisi awal: SystemSetting belum mengimplementasikan dua consumer-owned
      port.
    - File dibaca/diubah: adapter UserManagement, adapter AuditLog, provider
      SystemSetting, manifest/contract test, dan runtime setting test.
    - Perubahan: bind adapter hanya ketika SystemSetting enabled; default consumer
      tetap bekerja ketika disabled.
    - Acceptance: graph acyclic; nilai adapter sama dengan registry; safe default
      aktif saat SystemSetting tidak tersedia.
    - Command/test: focused cross-module contract test dan module validation.
    - Dependency: T05.1 dan T05.2.
    - Evidence: `SystemSettingUserRuntimeSettings` dan
      `SystemSettingAuditRuntimeSettings` mengimplementasikan public port
      consumer. Provider SystemSetting mengganti default binding setelah seluruh
      dependency tersedia. Pagination custom `[10, 20]`/default `10`, mailer,
      port SMTP, dan storage pagination rusak diuji. SystemSetting menambah direct
      dependency UserManagement tanpa cycle; urutan boot tidak berubah.

- [x] T05.4 Tambah architecture test import-versus-manifest dan cycle.
    - Kondisi awal: hidden dependency lolos dari quality gate saat ini.
    - File dibaca/diubah: architecture test baru, scanner dependency, fixture,
      dan manifest.
    - Perubahan: scan namespace lintas module dan bandingkan dengan public
      boundary serta dependency graph.
    - Acceptance: hidden import, private import, undeclared dependency, dan cycle
      membuat test gagal dengan diagnostic jelas.
    - Command/test: focused architecture test dan full module validation.
    - Dependency: T05.3.
    - Evidence: `ModuleDependencyValidator` menolak import undeclared dan private,
      hanya menerima public Application contract/DTO/event, serta menghasilkan
      diagnostic terstruktur. Test pertama menemukan private import adapter ke
      default Infrastructure consumer; implementasi diperbaiki dengan binding
      fallback DTO public, bukan melonggarkan rule. Suite architecture/runtime,
      consumer, dan presentation lulus 74 test/632 assertion; PHPStan, Pint, dan
      `module:validate --json` lulus dengan diagnostic kosong.

- [x] T06.1 Characterization test dan contract repository AccessControl.
    - Kondisi awal: behavior berjalan tetapi Application memakai Eloquent model.
    - File dibaca/diubah: action/query test, role repository contract, permission
      read contract, dan typed DTO.
    - Perubahan: kunci behavior create/delete/sync/dashboard sebelum refactor.
    - Acceptance: test mencakup duplikasi, protected role, invalid permission,
      actor permission, dan dashboard grouping.
    - Command/test: focused AccessControl unit/feature test.
    - Dependency: Checkpoint 1 dan T05.4.
    - Evidence: existing route/policy tests dikunci bersama gate baru untuk
      protected role, invalid permission, binding contract, dashboard shape, dan
      larangan import Infrastructure pada seluruh Application.

- [x] T06.2 Implementasikan Eloquent adapter AccessControl.
    - Kondisi awal: contract T06.1 belum memiliki adapter Infrastructure.
    - File dibaca/diubah: repository role, catalog permission, mapping DTO,
      provider binding, dan integration test.
    - Perubahan: pindahkan query/persistence Eloquent ke Infrastructure.
    - Acceptance: adapter memenuhi contract dan memakai transaction/guard yang
      sama dengan behavior lama.
    - Command/test: focused integration test, Pint, PHPStan.
    - Dependency: T06.1.
    - Evidence: `EloquentRoleRepository`, `EloquentPermissionCatalog`, dan
      `EloquentAccessControlReadRepository` mengisolasi Eloquent/Spatie pada
      Infrastructure. Provider memasang ketiga binding dan focused resolution
      test lulus. Mutation tetap berada di transaction activity publisher.

- [x] T06.3 Refactor mutation dan query Application AccessControl.
    - Kondisi awal: CreateRole/DeleteRole/SyncRolePermissions/dashboard query
      mengimpor model Infrastructure.
    - File dibaca/diubah: pecah mutation dan read query menjadi task kecil bila
      lebih dari lima file; gunakan contract T06.1.
    - Perubahan: dependency injection repository/read contract.
    - Acceptance: tidak ada import Infrastructure pada folder Application;
      characterization dan authorization test hijau.
    - Command/test: focused AccessControl suite dan architecture test.
    - Dependency: T06.2.
    - Evidence: create/delete/sync memakai repository serta typed `RoleData`;
      dashboard memakai read contract dan typed role/permission group DTO.
      Route binding/policy tetap di Presentation dan hanya meneruskan ID/nama ke
      Application. Focused suite lulus 34 test/136 assertion; PHPStan serta Pint
      scope AccessControl lulus tanpa suppression.

### Checkpoint 2 - Arsitektur

- [x] Empat module discover/validate/list/inspect tanpa diagnostic.
    - Evidence: empat command memakai graph canonical; command production exit 0
      dan focused command test mencakup diagnostic graph/runtime yang sama.
- [x] Disabled dan invalid module terbukti terisolasi saat runtime.
    - Evidence: runtime bootstrap fixture mencakup disabled, invalid provider,
      register/boot failure, dependent isolation, dan valid peer survival.
- [x] Dependency graph acyclic dan import lintas module sesuai manifest.
    - Evidence: graph validator dan `ModuleDependencyValidator` lulus pada empat
      module production; boot plan deterministik tanpa diagnostic.
- [x] Application AccessControl bebas dari model Infrastructure.
    - Evidence: recursive architecture test memeriksa semua source Application;
      source dan PHPStan scope AccessControl lulus.
- [x] `composer ci:check` dan `npm run build` lulus.
    - Kondisi awal: gate penuh sempat berhenti pada Pint karena empat source belum
      mengikuti style canonical; build produksi belum termasuk script
      `composer ci:check`.
    - Perubahan: empat source diformat dengan Pint tanpa mengubah behavior, lalu
      gate backend/frontend dan build produksi dijalankan ulang.
    - Alasan: checkpoint arsitektur tidak boleh ditutup dengan source yang gagal
      quality gate atau bundle frontend yang belum terbukti dapat dibangun.
    - Evidence: `composer ci:check` lulus dengan 376 test/2.215 assertion dan
      `npm run build` lulus setelah mentransformasi 2.744 module.

## Phase 3 - API Baseline

- [x] T07.1 Buat contract test canonical response envelope.
    - Kondisi awal: API AuditLog/SystemSetting hanya membawa sebagian field
      canonical.
    - File dibaca/diubah: API spec, test response contract, error renderer, dan
      controller existing sebagai characterization.
    - Perubahan: test success/error/pagination/correlation/redaction.
    - Acceptance: test merah membuktikan drift lama tanpa bergantung pada urutan
      field JSON.
    - Command/test: focused API contract test.
    - Dependency: T00.4.
    - Evidence: `tests/Feature/ApiEnvelopeContractTest.php` memeriksa success,
      error, pagination, correlation header/body, 401/404/422, dan redaksi detail
      AuditLog. Contract awal menemukan nested pagination dan envelope/error yang
      belum canonical.

- [x] T07.2 Implementasikan response factory dan selaraskan API existing.
    - Kondisi awal: controller merakit array response masing-masing.
    - File dibaca/diubah: response factory, error renderer, AuditLog controller,
      SystemSetting controller, dan focused test.
    - Perubahan: canonical `success/message/data/meta` serta error
      `success/message/errors/code/meta`.
    - Acceptance: T07.1 hijau dan redaksi T01 tetap lulus.
    - Command/test: API contract/presentation test.
    - Dependency: T07.1.
    - Evidence: `ApiResponseFactory`, renderer `bootstrap/app.php`, controller API
      AuditLog/SystemSetting, resource aman AuditLog, serta replay correlation
      diselaraskan. Gabungan API/presentation lulus 34 test/338 assertion; Pint
      dan PHPStan scope terkait lulus. Setelah T00.6, focused gate envelope dan
      idempotency lulus 35 test/251 assertion.

- [x] T08.1 Implementasikan API read UserManagement.
    - Kondisi awal: `Routes/api.php` UserManagement kosong.
    - File dibaca/diubah: route, API controller read, resource, request/filter,
      dan feature test.
    - Perubahan: GET list/detail memakai query/read contract existing.
    - Acceptance: scope, filter, pagination, ULID, 401/403/404, dan envelope
      canonical terbukti.
    - Command/test: focused user API read test dan route list.
    - Dependency: T07.2.
    - Evidence: `UserApiController`, `ListUsersApiRequest`, dan
      `UserApiResource` menyediakan GET list/detail. `UserListFilter` serta
      repository mendukung sort allowlist `created_at`/`name`; response memakai
      snake_case dan pagination meta canonical. Focused API/envelope/regression
      web lulus 29 test/277 assertion. PHPStan source produksi 0 error, Pint dan
      `git diff --check` lulus; route list menampilkan dua route dan target module
      valid tanpa diagnostic.

- [x] T08.2 Implementasikan API create UserManagement.
    - Kondisi awal: pembuatan user hanya tersedia melalui presentation web.
    - File dibaca/diubah: route create, API controller, FormRequest/mapper,
      resource, dan focused feature test.
    - Perubahan: gunakan Application Action pembuatan user; controller hanya
      menangani authorization, mapping request, dan response canonical.
    - Acceptance: success, validation, duplicate email, 401/403, rate limit,
      idempotency, serta audit terbukti tanpa mengekspos password.
    - Command/test: focused user API create test dan route list.
    - Dependency: T08.1.
    - Evidence: POST `/api/v1/users` memakai `StoreUserApiRequest`, `CreateUser`,
      resource snake_case, correlation audit yang sama, limiter, dan
      `api.idempotency`. Duplicate constraint diterjemahkan ke `409 CONFLICT`.
      Gabungan read/create/regression lulus 41 test/356 assertion; password hanya
      masuk DTO/action, tersimpan hashed, dan tidak ada pada response,
      reservation replay, maupun audit. PHPStan 0 error, Pint, route list, target
      module validation, dan `git diff --check` lulus.

- [x] T08.3 Implementasikan API update profile dan status UserManagement.
    - Kondisi awal: update profile dan lifecycle status hanya tersedia melalui
      presentation web.
    - File dibaca/diubah: satu route PATCH authoritative, API controller,
      FormRequest conditional, mapper, Application orchestrator, dan focused
      feature test.
    - Perubahan: gunakan Application Action existing dan pertahankan controller
      sebagai orchestration layer.
    - Acceptance: update valid, transisi status invalid, protected user, 401/403,
      idempotency, scope, dan audit terbukti.
    - Command/test: focused user API update/status test dan route list.
    - Dependency: T08.2.
    - Evidence: PATCH `/api/v1/users/{user}` menerima kombinasi parsial
      `name/email/status`; policy coarse memakai permission OR dan action tetap
      memeriksa permission per field. `UpdateUserProfileAndStatus` menjaga
      transaksi gabungan sebelum exception dirender. Update profile/status,
      invalid transition, rollback gabungan, duplicate, protected/not-found,
      validation, 401/403, replay/mismatch, dan audit lulus dalam gabungan 56
      test/410 assertion. PHPStan 0 error dan Pint lulus.

- [x] T08.4 Implementasikan API delete UserManagement.
    - Kondisi awal: archive/force-delete user hanya tersedia melalui web.
    - File dibaca/diubah: route delete, API controller, request reason bila
      diperlukan, Application Action mapper, dan focused feature test.
    - Perubahan: gunakan lifecycle action existing dan response canonical tanpa
      mengembalikan data sensitif user.
    - Acceptance: archive/delete valid, protected/self target, target tidak ada,
      401/403, idempotency, dan audit terbukti.
    - Command/test: focused user API delete test dan route list.
    - Dependency: T08.3.
    - Evidence: DELETE `/api/v1/users/{user}` memakai `DeleteUserApiRequest`,
      `SoftDeleteUser`, reason audit tersanitasi, correlation canonical,
      `api.idempotency`, dan response `data: null`. Success/replay, 401/403,
      self/protected/archived/not-found, reason panjang/sensitif, rollback, serta
      audit lulus. Gabungan seluruh API user/regression lulus 63 test/507
      assertion; PHPStan 0 error, Pint, route list lima endpoint, target module
      validation, dan `git diff --check` lulus.

- [x] T09.1 Implementasikan API read role dan permission catalog.
    - Kondisi awal: `Routes/api.php` AccessControl kosong.
    - File dibaca/diubah: route read, API controller, read contract/resource,
      request filter, dan focused feature test.
    - Perubahan: baca catalog melalui public read contract, bukan model private.
    - Acceptance: list/detail role dan permission catalog membuktikan filter,
      scope, pagination, 401/403/404, serta envelope canonical.
    - Command/test: focused AccessControl API read test dan route list.
    - Dependency: T06.3 dan T07.2.
    - Evidence: GET role list/detail dan permission catalog memakai typed query,
      DTO/filter, read repository, serta envelope canonical. Filter/search/sort,
      pagination, module identity, 401/403/404/422 lulus 21 test/120 assertion.
      PHPStan 0 error, Pint, route list tiga endpoint, target module validation,
      dan `git diff --check` lulus. Sort direction dikunci literal `asc|desc`
      setelah pemeriksaan dokumentasi PHPStan `argument.type` melalui MCP
      `chrome-devtools`.

- [x] T09.2 Implementasikan API create/update/delete role.
    - Kondisi awal: mutation role hanya tersedia melalui presentation web.
    - File dibaca/diubah: route mutation role, API controller, FormRequest,
      Application Action mapper, dan focused feature test.
    - Perubahan: gunakan repository/capability public dan response canonical.
    - Acceptance: create/update/delete, duplicate, protected role, 401/403,
      idempotency, validation, dan audit terbukti.
    - Command/test: focused role API mutation test dan route list.
    - Dependency: T09.1.
    - Evidence: route create/update/delete memakai authentication, verification,
      rate limit, idempotency, dan policy middleware. Application memakai typed
      repository/capability; controller hanya melakukan orkestrasi. Focused
      `AccessControlApiMutationTest` lulus 5 test/47 assertion untuk success,
      duplicate, protected/missing role, authorization, validation, replay,
      rollback atomik, dan audit. Matrix authoritative direvisi untuk mencakup
      detail serta delete role berdasarkan persetujuan user 20 Agustus 2026.

- [x] T09.3 Implementasikan API assignment dan revoke role user.
    - Kondisi awal: assignment role user belum tersedia pada API.
    - File dibaca/diubah: route assign/revoke, API controller, FormRequest,
      public capability, dan focused feature test.
    - Perubahan: mutasi role melalui capability AccessControl tanpa mengimpor
      model private dari module lain.
    - Acceptance: assign/revoke valid, protected role/user, duplicate request,
      401/403/404, idempotency, dan audit terbukti.
    - Command/test: focused user-role API test dan route list.
    - Dependency: T09.2 dan T08.4.
    - Evidence: `MutateUserRole` memakai `RoleAssignmentCapability` dan
      `RoleCatalogCapability`, bukan model private AccessControl. Route POST dan
      DELETE memakai auth, verification, limiter, policy, idempotency, ULID, dan
      envelope canonical. Focused/regression suite lulus 46 test/274 assertion;
      PHPStan 0 error, Pint lulus, dua module valid, dan route list membuktikan
      middleware lengkap. Test mencakup replay, audit tunggal, protected user,
      SuperSystem role, missing role, 401/403/404/409/422, serta response aman.

- [x] T09.4 Implementasikan API direct permission user.
    - Kondisi awal: assignment direct permission belum tersedia pada API.
    - File dibaca/diubah: route assign/revoke, API controller, FormRequest,
      public capability, dan focused feature test.
    - Perubahan: mutasi direct permission melalui authority AccessControl.
    - Acceptance: assign/revoke valid, permission invalid, protected target,
      401/403/404, idempotency, dan audit terbukti.
    - Command/test: focused direct-permission API test dan route list.
    - Dependency: T09.3.
    - Evidence: public `DirectPermissionAssignmentCapability` memiliki adapter
      Spatie pada AccessControl; UserManagement hanya mengorkestrasi invariant
      target, transaction audit, dan response typed melalui
      `MutateUserPermission`. Focused/regression suite lulus 51 test/307
      assertion; PHPStan 0 error, Pint lulus, dua module valid, dan route list
      membuktikan auth, verified, throttle, policy, idempotency, serta ULID.
      Negative test mencakup 401/403/404/409/422 dan target protected.

- [x] T09.5 Implementasikan API impersonation.
    - Kondisi awal: flow impersonation tersedia pada web, belum pada API matrix.
    - File dibaca/diubah: route, controller, request, Application Action mapper,
      dan feature test.
    - Perubahan: start/end melalui authority yang sama dengan web.
    - Acceptance: reason wajib; SuperSystem/non-active target ditolak; actor
      restore dan audit terbukti; response tidak memuat session secret.
    - Command/test: focused impersonation API test.
    - Dependency: T08.4 dan T07.2.
    - Evidence: API start/end memakai action serta session adapter yang sama
      dengan web. `EnforceIdempotency` memakai actor asli saat session
      impersonation aktif sehingga replay tetap konsisten setelah guard pindah.
      Focused/regression suite lulus 58 test/396 assertion; PHPStan 0 error, Pint
      lulus, module valid, dan route list lengkap. Test mencakup reason khusus,
      self/SuperSystem/inactive target, actor restore, audit start/end tunggal,
      replay, 401/403/409/422, dan larangan field session/token/ID/reason.

- [x] T09.6 Tutup route matrix dan forbidden-controller logic.
    - Kondisi awal: route matrix global belum memiliki automated parity test.
    - File dibaca/diubah: route contract test, architecture test controller,
      Ziggy/API documentation, dan focused test.
    - Perubahan: cocokkan method/path/name/authorization dengan spec; larang query
      Eloquent/business rule langsung pada API controller.
    - Acceptance: seluruh endpoint baseline hadir tepat satu kali dan controller
      tetap orchestration layer.
    - Command/test: `route:list --path=api/v1 --json`, contract test, PHPStan.
    - Dependency: T09.1 sampai T09.5.
    - Evidence: `ApiRouteMatrixTest` membaca method/path langsung dari
      `docs/02-DESIGN/02.01-API-SPEC.md` dan cocok tepat dengan 21 route runtime.
      Test yang sama membuktikan middleware `web`, `auth`, `verified`, limiter
      `system-api`, idempotency mutation, dan larangan query/validation/business
      rule langsung pada seluruh API controller. Focused gate lulus 3 test/156
      assertion. Full gate menemukan satu import exception private, lalu failure
      contract role/permission dipindahkan ke public `Application/Contracts`;
      regression arsitektur lulus dan `composer ci:check` akhirnya lulus 394
      test/2.525 assertion dengan PHPStan 0 error.

### Checkpoint 3 - API

- [x] Route matrix cocok dengan API specification.
    - Evidence: parity test membandingkan 21 pasangan method/path authoritative
      dengan router dan menolak route ganda atau route yang tidak terdokumentasi.
- [x] Success/error envelope canonical dan secret-safe.
    - Evidence: API contract/regression suite lulus; impersonation dan setting
      sensitif tidak mengembalikan token, session, identifier internal, reason,
      maupun raw secret.
- [x] Positive/negative authorization, validation, scope, rate, idempotency,
      serta audit test lulus.
    - Evidence: suite API mencakup guest/forbidden/not-found/validation/conflict,
      protected target, limiter, replay idempotent, rollback audit, dan response
      redaction.
- [x] Full backend suite lulus.
    - Evidence: `composer ci:check` lulus 394 test/2.525 assertion; ESLint,
      Prettier, TypeScript, Pint, dan PHPStan 0 error.

## Phase 4 - CI dan Open Risk

- [x] T10.1 Perkuat install deterministik, static analysis, dan coverage.
    - Kondisi awal: workflow memakai `coverage: none`; source package framework
      belum seluruhnya masuk PHPStan; frontend unit test belum tersedia.
    - File dibaca/diubah: workflow, composer script, PHPStan config, test config,
      dan CI documentation.
    - Perubahan: lock install, cache aman, coverage driver/threshold, dan analyze
      `packages/StarterKit`.
    - Acceptance: CI gagal pada coverage/static error dan menghasilkan report.
    - Command/test: validasi workflow, local CI tanpa coverage, dan CI run.
    - Dependency: Checkpoint 3.
    - Evidence: workflow memakai lockfile, cache berbasis hash lockfile, PHP 8.4,
      Node.js `24.12.0`, dan PCOV. `phpstan.neon` menganalisis
      `packages/StarterKit/src` serta `tools/ci`. Verifier coverage memiliki
      positive/negative test; Vitest menerapkan threshold 80%; workflow YAML
      lulus parser Symfony dan Prettier.

- [x] T10.2 Jalankan rehearsal migration pada MySQL lokal terisolasi.
    - Kondisi awal: fresh migration/global seeder belum diverifikasi ulang karena
      database default tidak boleh dihancurkan.
    - File dibaca/diubah: migration, global/module seeder, konfigurasi database,
      serta execution log. Source database tidak diubah.
    - Perubahan: buat database bernama unik, jalankan fresh/seed, ulangi seeder
      untuk idempotency, rollback, migrate/seed ulang, lalu hapus database test.
    - Acceptance: target berbeda dari database `.env`; schema, relation, dan
      seeder lulus; cleanup terverifikasi meski command gagal.
    - Command/test: Artisan migration pada database sementara dan query count
      allowlist tanpa menampilkan credential.
    - Dependency: inventory konfigurasi database dan backup tidak diperlukan
      karena target baru serta disposable.
    - Evidence: database unik dibuat pada MySQL lokal; `migrate:fresh --seed`,
      seed kedua, rollback, dan `migrate --seed` ulang lulus dalam 107,3 detik.
      Count allowlist stabil dan database sementara berhasil dihapus.
    - Batasan awal upgrade dari snapshot release lama sudah ditutup pada T10.3.

- [x] T10.3 Tambah migration fresh dan upgrade lane pada CI MySQL terisolasi.
    - Kondisi awal: rehearsal lokal T10.2 belum menjadi quality gate otomatis dan
      belum ada fixture upgrade dari versi release sebelumnya.
    - File dibaca/diubah: workflow DB job, migration test, seeder test, upgrade
      fixture/runbook, dan CI docs.
    - Perubahan: service MySQL khusus test menjalankan fresh, seed, relation,
      idempotency, rollback, dan upgrade path.
    - Acceptance: job selalu memakai service database disposable; fresh dan
      upgrade path lulus serta artifact schema tidak membawa data sensitif.
    - Command/test: CI MySQL job dan schema assertion.
    - Dependency: T10.1 dan T10.2.
    - Evidence: `tests.yml` memiliki job `mysql-fresh` dan `mysql-upgrade`.
      Fixture `mysql-legacy-bigint.sql`, migration forward-only, dan verifier
      upgrade tersedia. Rehearsal lokal MySQL mempertahankan dua user, satu
      session, dan satu Passkey sebagai ULID; seed kedua lulus dan database
      sementara dihapus. Artifact hanya berisi schema tanpa data selama 14 hari.
    - Risiko/rollback: upgrade identifier mengikuti ADR-0006. Restore backup atau
      forward-fix wajib dipakai; ULID tidak dikonversi kembali menjadi angka.

- [x] T11.1 Tambah Vitest untuk frontend critical logic/component.
    - Kondisi awal: package scripts belum memiliki frontend unit test.
    - File dibaca/diubah: package manifest/lock setelah izin dependency, Vitest
      config, setup test, dan test component/utility prioritas.
    - Perubahan: test permission visibility, loading/disabled mutation, filter,
      dan response/error rendering.
    - Acceptance: `npm test` atau script canonical lulus dan masuk CI.
    - Command/test: Vitest, lint, type check, build.
    - Dependency: T10.1 dan izin dependency.
    - Evidence: empat file dengan sembilan test menguji authorization, input
      error, loading button, dan role control. `npm run test:coverage` lulus dengan
      statement 90,69%, branch 97,36%, function 89,47%, dan line 90,24%.
      ESLint, TypeScript, Prettier, dan production build lulus.

- [x] T11.2 Tambah fondasi Playwright dan axe-core.
    - Kondisi awal: tidak ada browser/a11y test otomatis pada CI.
    - File dibaca/diubah: package manifest/lock setelah izin, Playwright config,
      auth fixture aman, satu smoke test, dan setup axe.
    - Perubahan: sediakan project desktop/mobile, server lifecycle, redaction
      artifact, serta login fixture yang tidak menyimpan credential.
    - Acceptance: smoke publik/login dan satu axe scan lulus pada dua viewport;
      trace/video tidak disimpan; screenshot hanya dibuat saat gagal dan masuk
      allowlist aman.
    - Command/test: focused Playwright smoke desktop/mobile.
    - Dependency: T11.1 dan izin dependency.
    - Evidence: `playwright.config.ts`, auth helper, axe helper, dan smoke test
      tersedia. Login smoke lulus pada Chromium desktop `1440x1000` dan Pixel 5.
      Credential hanya berasal dari environment runtime dan tidak masuk source.

- [x] T11.3 Tambah critical flow empat module pada Playwright/axe.
    - Kondisi awal: fondasi T11.2 belum membuktikan authorization dan behavior
      UI AccessControl, UserManagement, AuditLog, serta SystemSetting.
    - File dibaca/diubah: fixture role, test per module, page helper bila perlu,
      dan allowlist accessibility exception.
    - Perubahan: uji navigation, permission visibility, mutation loading, toast,
      keyboard/focus, empty/error, light/dark, responsive, dan axe.
    - Acceptance: positive/negative flow empat module lulus tanpa secret/session
      artifact dan tanpa accessibility violation high-impact yang tidak disetujui.
    - Command/test: Playwright per module desktop/mobile dan axe report.
    - Dependency: T11.2 dan Checkpoint 3.
    - Evidence: critical flow membuat role, mengirim invitation melalui mail sink,
      mengubah SystemSetting, membaca detail/empty AuditLog, memeriksa shortcut,
      loading, toast, responsive identity, light/dark, dan menolak SecurityAdmin
      dengan 403. Desktop dan mobile lulus tanpa high-impact axe violation.
    - Temuan yang diperbaiki: kontras tombol destructive, kontras toast sukses,
      collision badge dark mode, akses keyboard show-password, positive tab
      index, dan identity table yang tersembunyi pada mobile.

- [x] T11.4 Integrasikan browser/accessibility gate ke CI.
    - Kondisi awal: critical flow lokal belum otomatis memblokir merge.
    - File dibaca/diubah: workflow browser job, service/app bootstrap, artifact
      allowlist, retention, dan CI docs.
    - Perubahan: jalankan Playwright/axe setelah backend/database siap serta
      unggah report aman hanya ketika diperlukan.
    - Acceptance: kegagalan flow/a11y memblokir merge; artifact tidak memuat
      password, cookie, token, `.env`, local storage, atau database dump.
    - Command/test: workflow run dan inspeksi artifact.
    - Dependency: T10.3 dan T11.3.
    - Evidence: `.github/workflows/browser.yml` membuat key/password acak dan
      melakukan masking sebelum menulis environment, memakai SQLite disposable,
      mailer `log` ke channel `null`, serta menjalankan seluruh Playwright/axe.
      Artifact allowlist hanya report JSON dan screenshot kegagalan selama 14
      hari. YAML lulus parser dan alur yang sama lulus lokal.
    - Batasan: first hosted GitHub Actions run tetap wajib sebelum required check
      diberlakukan pada branch protection.

- [x] T11.5 Tambah CodeQL, dependency scan, dan artifacts.
    - Kondisi awal: workflow belum menjalankan security analysis canonical atau
      mempublikasikan report yang diwajibkan docs.
    - File dibaca/diubah: workflow CodeQL/security, dependency scan config,
      artifact allowlist, dan CI documentation.
    - Perubahan: job least privilege dengan redaction dan retention yang jelas.
    - Acceptance: high/critical finding memblokir merge; artifact tidak membawa
      `.env`, secret, session, database dump, atau raw browser trace sensitif.
    - Command/test: workflow run dan inspection daftar artifact.
    - Dependency: T10.1.
    - Evidence: `security.yml` menjalankan Composer/npm audit, CodeQL
      JavaScript/TypeScript `security-extended`, verifier SARIF severity 7, serta
      OWASP Dependency-Check CVSS 7 pada image yang dipin digest. Tiga test Node
      membuktikan SARIF aman diterima dan finding high/error ditolak. Report
      security disimpan 30 hari; YAML lulus parser.
    - Batasan: CodeQL tidak mendukung PHP. PHP diperiksa dengan PHPStan/Larastan,
      Pest, coverage, Composer audit, dan OWASP. First hosted scan tetap menjadi
      release evidence sebelum merge/deploy.

- [x] T11.6 Pulihkan koneksi MCP Chrome dan jalankan verifikasi read-only.
    - Kondisi awal: percobaan lama memakai adapter browser yang tidak tersedia;
      extension `chrome-devtools` kemudian dipasang oleh user.
    - Perubahan: MCP `chrome-devtools` dipakai pada context terisolasi untuk
      halaman publik/login dan empat module dengan role SecurityAdmin serta
      SuperSystem; sesi ditutup melalui UI setelah pemeriksaan.
    - Alasan: menutup open risk koneksi dan memperoleh evidence browser nyata
      tanpa mengubah data aplikasi.
    - Acceptance: desktop `1440x1000`, mobile `390x844`, light/dark, status
      request, console, accessibility tree, dan negative authorization diperiksa.
    - Evidence: halaman utama serta module memuat sukses; console bersih kecuali
      403 yang memang diharapkan pada negative test; control utama memiliki
      accessible name; CLS `0`; detail ada pada execution log.
    - Batasan: mutation, empty/error state, focus order lengkap, dan threshold
      performa belum ditutup oleh task ini.

- [x] T11.7 Tutup temuan performa SSR pada initial page.
    - Kondisi awal: trace UserManagement memberi LCP sekitar 3,2 detik dengan
      TTFB sekitar 2,75 detik; diagnosis menunjukkan SSR aktif tanpa proses SSR.
    - File dibaca/diubah: `config/inertia.php`, `.env.example`,
      `tests/Feature/InertiaSsrConfigurationTest.php`, dan execution log.
    - Perubahan: jadikan SSR opt-in melalui `INERTIA_SSR_ENABLED`; URL endpoint
      juga dapat dikonfigurasi melalui environment.
    - Acceptance: initial page tidak menunggu endpoint SSR yang mati; TTFB/LCP
      membaik secara terukur dan regression test konfigurasi lulus.
    - Evidence: focused test lulus 2 test/4 assertion; curl TTFB menjadi
      0,71-1,02 detik; trace login menghasilkan LCP 1,053 detik, TTFB 575 ms,
      CLS 0, console bersih, dan seluruh request 200/304.
    - Dependency: T11.6.
    - Risiko/rollback: jangan memakai inspeksi detail request karena dapat
      memunculkan header sesi; gunakan daftar URL/status saja.

- [x] T11.8 Verifikasi flow interaktif browser manual.
    - Kondisi awal: halaman/read-only state sudah diperiksa, tetapi mutation,
      toast, keyboard/focus lengkap, serta empty/error state belum diuji penuh.
    - File dibaca/diubah: UI dan route yang selesai pada increment feature,
      fixture demo disposable, serta execution log. Source hanya diubah jika
      browser menemukan defect yang sudah memiliki regression test.
    - Perubahan: uji flow mutation per module, disabled/loading button, toast
      global, focus return, keyboard navigation, empty state, dan error state.
    - Acceptance: positive/negative authorization terbukti; data dummy dapat
      dipulihkan; console/network bersih; focus serta accessible name benar.
    - Command/test: DevTools click/fill/keyboard, console, network list,
      accessibility tree, dan screenshot aman.
    - Dependency: increment behavior module terkait selesai dan fixture test
      disposable tersedia.
    - Evidence: MCP `chrome-devtools` membuat lalu menghapus role dummy
      AccessControl; memeriksa search/focus serta empty state UserManagement;
      membuktikan tombol undangan `Mengirim...` disabled; dan menemukan failure
      SMTP yang semula meninggalkan user serta membuka overlay 500. Regression
      backend kemudian membersihkan user pada status gagal maupun exception dan
      mengembalikan validation error aman. Dialog merender error sebagai
      `role="alert"` dengan `aria-invalid` dan `aria-describedby`.
    - Evidence lanjutan: query exact membuktikan dua email dummy berjumlah nol.
      SystemSetting dan AuditLog menerima shortcut `/`, menampilkan empty state,
      AuditLog menampilkan loading disabled, request filter menghasilkan 200,
      dan console browser tidak memiliki error/warning. Automated flow melengkapi
      mutation sukses/toast SystemSetting serta negative authorization pada
      runtime disposable.

- [x] T12.1 Putuskan dan perbaiki exact-version constraint Composer.
    - Kondisi awal: `composer validate --strict` memberi warning untuk beberapa
      exact-version constraint.
    - File dibaca/diubah: composer manifest/lock, package changelog/security
      advisory, dependency policy docs, dan ADR bila pengecualian diperlukan.
    - Perubahan: gunakan range kompatibel dengan lock file setelah review dan izin
      dependency, atau dokumentasikan exact pin yang benar-benar wajib.
    - Acceptance: strict validation bersih atau seluruh pengecualian memiliki
      alasan, owner, review date, dan test evidence.
    - Command/test: `composer validate --strict`, audit, update dry-run bila
      tersedia, dan full CI.
    - Dependency: review sumber resmi Composer dan inventory lock file; perubahan
      hanya pada constraint/lock metadata, tanpa menginstal versi package baru.
    - Evidence: empat constraint memakai caret range. Command
      `composer update --lock --no-install --no-audit --no-scripts` tidak
      mengubah package; lock hanya berubah pada `content-hash`; dan
      `composer validate --strict` lulus.

### Checkpoint 4 - Release Gate

- [x] Workflow backend, frontend, MySQL migration, browser/a11y, security, dan
      artifact tervalidasi; lane lokal yang setara lulus.
    - Evidence: YAML lulus parser/Prettier; backend 398 test/2.547 assertion;
      frontend coverage 10 test; MySQL fresh/upgrade terisolasi lulus; Playwright
      4/4; SARIF verifier 3/3. First hosted run tetap release evidence eksternal.
- [x] Browser manual serta Playwright mempunyai evidence.
    - Evidence: MCP flow empat module dan Playwright Chromium desktop/mobile
      lulus; console bersih dan request manual utama 200.
- [x] Composer/npm audit bersih pada severity yang diwajibkan.
    - Evidence: Composer tidak menemukan advisory; npm menemukan 0 vulnerability;
      540 package memiliki registry signature dan 186 memiliki attestation.
- [x] Build production lulus.
    - Evidence: Vite mentransformasi 2.745 module dan guard memastikan bundle
      tidak memuat entry/dependency test.

## Phase 5 - Penutupan dan Evidence

- [x] T12.2 Jalankan final verification dan sinkronkan downstream docs.
    - Kondisi awal: task hanya boleh ditutup berdasarkan hasil nyata setelah
      seluruh increment selesai.
    - File dibaca/diubah: README/specification/ADR/task/execution log/changelog
      yang terdampak, tanpa mengubah dokumen tidak terkait.
    - Perubahan: catat source, file, alasan, command, hasil, risiko, dan owner.
    - Acceptance: seluruh checklist program sesuai kondisi nyata; tidak ada
      broken link, stale status, forbidden dependency, atau claim browser palsu.
    - Command/test: seluruh command pada implementation plan, link checker,
      `git diff --check`, dan review working tree.
    - Dependency: Checkpoint 4.
    - Evidence: README, implementation plan, task, execution log, specification,
      CI/CD, security design, ADR-0006, migration runbook, dan module README
      diselaraskan. Full gate, module discovery/validation/list/inspect, route
      matrix 21 endpoint, link checker, serta `git diff --check` lulus.

## Definition of Done

- [x] Scope security selesai.
    - Kondisi awal: raw sensitive value dan decrypt failure belum aman.
    - Perubahan yang harus terbukti: seluruh public output redacted dan reader
      memakai safe default.
    - Evidence wajib: positive/negative CLI/API/persistence test.
    - Evidence: redaction CLI/API, safe default ciphertext, sensitive input
      prompt/STDIN, audit sanitization, dependency audit, dan security workflow
      memiliki positive/negative test yang lulus.
- [x] Scope arsitektur selesai.
    - Kondisi awal: provider statis, hidden dependency, cycle risk, dan
      Application-to-Infrastructure import masih ada.
    - Perubahan yang harus terbukti: runtime isolation, graph acyclic,
      consumer-owned port, dan repository boundary.
    - Evidence wajib: bootstrap, graph, architecture, dan module command test.
    - Evidence: boot plan canonical empat module, isolation invalid/disabled,
      consumer-owned port, public capability, dan Application boundary lulus pada
      test serta command module.
- [x] Scope API selesai.
    - Kondisi awal: baseline endpoint matrix belum lengkap.
    - Perubahan yang harus terbukti: endpoint, envelope, authorization,
      idempotency, rate limit, audit, dan redaction sesuai specification.
    - Evidence wajib: route matrix dan API contract suite.
- [x] Scope CI dan open risk selesai.
    - Kondisi awal: coverage, migration rehearsal, frontend/browser/a11y,
      security scan, artifact, koneksi Chrome, dan dependency constraint masih
      terbuka.
    - Perubahan yang harus terbukti: seluruh job/evidence tersedia dan browser
      nyata dapat dikendalikan melalui extension yang benar.
    - Evidence wajib: CI run, MySQL isolated run, Playwright/axe, Chrome summary,
      audit, serta strict validation.
    - Evidence: workflow siap, lane lokal setara lulus, MySQL rehearsal lulus,
      Playwright/axe 4/4, MCP manual lengkap, audit bersih, dan strict validation
      lulus. Hosted run dimiliki release maintainer sebelum merge/deploy.
- [x] Scope dokumentasi selesai.
    - Kondisi awal: source hilang dan beberapa status stale.
    - Perubahan yang harus terbukti: authoritative/downstream docs sinkron.
    - Evidence wajib: link checker, terminology scan, execution log, dan review
      checklist akhir.
    - Evidence: downstream docs disinkronkan, status stale diperbarui, link
      checker serta terminology scan lulus, dan execution log mencatat bukti
      lengkap tanpa data sensitif.
