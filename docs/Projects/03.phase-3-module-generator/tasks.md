# Task Plan Phase 3 Module Generator

Setiap task memiliki acceptance criteria, focused test, verification command,
execution evidence, dan review checklist sebelum serta sesudah dikerjakan.

| ID       | Increment | Task                            | Depends On | Acceptance Criteria                        | Verification     | Status  |
| -------- | --------- | ------------------------------- | ---------- | ------------------------------------------ | ---------------- | ------- |
| TASK-001 | INC-001   | Finalisasi contract/profile     | Phase 2    | Open Decision blocking selesai             | Review spec/ADR  | Selesai |
| TASK-002 | INC-002   | Buat input contract             | TASK-001   | Valid/invalid input stabil                 | Unit test        | Selesai |
| TASK-003 | INC-003   | Buat stub/profile engine        | TASK-002   | Plan deterministic                         | Contract test    | Selesai |
| TASK-004 | INC-004   | Buat conflict detection/dry-run | TASK-003   | Tidak ada side effect saat invalid/dry-run | Feature test     | Selesai |
| TASK-005 | INC-005   | Buat staging dan promotion      | TASK-004   | Promote sukses; cleanup saat gagal         | Integration test | Selesai |
| TASK-006 | INC-006   | Buat command `module:make`      | TASK-005   | JSON/human output dan exit code stabil     | Command test     | Selesai |
| TASK-007 | INC-007   | Hardening dan quality gate      | TASK-006   | Full verification lulus                    | Full suite       | Selesai |

## Detail TASK-001

- [x] Scope task selesai.
    - Kondisi awal: specification dan ADR Phase 3 sudah dibuat, tetapi status
      increment belum ditutup secara eksplisit.
    - File acuan: `specification.md`, `implementation-plan.md`, dan
      `decisions/ADR-0001-MODULE-GENERATOR-BOUNDARY.md`.
    - Perubahan: profile `default-v1`, mode module baru, staging ULID, rename
      atomic, dan cleanup ditetapkan.
    - Evidence: ADR berstatus `Diterima` dan discovery checklist lengkap.
    - Risiko: perubahan boundary berikutnya harus memakai ADR baru.

## Detail TASK-002

- [x] Scope task selesai.
    - Kondisi awal: generator belum memiliki input contract; command Phase 3
      belum boleh membaca array input bebas.
    - File dibuat: `packages/StarterKit/src/Generator/Contracts/ModuleGenerationRequest.php`
      dan `tests/Unit/ModuleGenerationRequestTest.php`.
    - Perubahan: readonly DTO memvalidasi module/domain PascalCase, profile
      kebab-case aman, boolean `dry_run`, `force`, `yes`, default profile
      `default-v1`, dan aturan `yes` membutuhkan `force`.
    - Alasan: input generator harus typed, aman dari path traversal dasar, dan
      dapat digunakan ulang oleh plan serta console adapter.
    - Acceptance: input valid dibuat; input invalid menghasilkan
      `InvalidArgumentException`; opsi default dan mode terbaca stabil.
    - Evidence: Pint lulus; `php artisan test
tests/Unit/ModuleGenerationRequestTest.php` lulus dengan 6 test/18
      assertion; `git diff --check` lulus.
    - Risiko: result contract dan plan generator dibuat pada increment berikutnya.

## Review Checklist TASK-002

- [x] Checklist ditinjau sebelum coding.
- [x] Positive dan negative test tersedia.
- [x] Path traversal dasar pada domain/profile ditolak.
- [x] Tidak ada filesystem mutation pada DTO.
- [x] Checklist ditinjau sesudah test lulus.

## Detail TASK-003

- [x] Scope task selesai.
    - Kondisi awal: input DTO sudah tersedia, tetapi belum ada profile yang
      menghasilkan plan dan golden structure tanpa menulis filesystem.
    - File dibuat: `packages/StarterKit/src/Generator/Contracts/ModuleGenerationPlan.php`,
      `packages/StarterKit/src/Generator/Profiles/DefaultModuleProfile.php`, dan
      `tests/Unit/DefaultModuleProfileTest.php`.
    - Perubahan: profile `default-v1` menghasilkan directory canonical,
      `module.json`, `module.php`, `permissions.php`, provider, README, serta
      route entry point. Plan menyimpan target path, directory, dan file content.
      Profile lain ditolak oleh `DefaultModuleProfile` agar label output tidak
      salah.
    - Alasan: increment berikutnya membutuhkan plan deterministic untuk conflict
      detection, dry-run, dan staging writer.
    - Acceptance: request sama menghasilkan plan sama; namespace/path sesuai
      domain dan module; golden directory/file tersedia; profile unsupported
      ditolak; tidak ada filesystem mutation.
    - Evidence: Pint lulus; `php artisan test tests/Unit/ModuleGenerationRequestTest.php
tests/Unit/DefaultModuleProfileTest.php` lulus dengan 9 test/32 assertion;
      `git diff --check` lulus.
    - Risiko: profile registry untuk banyak profile dan stub implementation
      detail akan dipertimbangkan setelah baseline generator bekerja.

## Review Checklist TASK-003

- [x] Checklist ditinjau sebelum coding.
- [x] Positive test plan deterministic tersedia.
- [x] Negative test profile unsupported tersedia.
- [x] Golden structure canonical dicocokkan dengan baseline docs.
- [x] Profile belum menulis atau mengubah filesystem.
- [x] Checklist ditinjau sesudah test lulus.

## Detail TASK-004

- [x] Scope task selesai.
    - Kondisi awal: profile sudah menghasilkan plan, tetapi belum ada preview
      yang memeriksa target existing atau duplicate identity sebelum write.
    - File dibuat: `packages/StarterKit/src/Generator/Contracts/ModuleGenerationPreview.php`,
      `packages/StarterKit/src/Generator/ModuleConflictDetector.php`,
      `packages/StarterKit/src/Generator/ModuleGenerationPreviewer.php`, dan
      `tests/Unit/ModuleGenerationPreviewTest.php`.
    - Perubahan: preview menggabungkan profile plan dan conflict detector;
      detector memeriksa target existing, diagnostic registry, duplicate name,
      path, namespace, dan provider, serta menolak target di luar `app/Modules`.
    - Alasan: generator harus gagal sebelum side effect dan dry-run harus dapat
      menampilkan hasil valid/conflict tanpa menulis filesystem.
    - Acceptance: preview valid tidak membuat directory; target existing dan
      duplicate identity menghasilkan diagnostic stabil; path di luar boundary
      ditolak.
    - Evidence: Pint lulus; `php artisan test
tests/Unit/ModuleGenerationPreviewTest.php` lulus dengan 3 test/8
      assertion; `git diff --check` lulus.
    - Risiko: staging, atomic promotion, rollback, dan cleanup belum dibuat.

## Review Checklist TASK-004

- [x] Checklist ditinjau sebelum coding.
- [x] Positive test preview valid tersedia.
- [x] Negative test target existing dan duplicate identity tersedia.
- [x] Path containment dasar diuji.
- [x] Tidak ada filesystem mutation pada preview.
- [x] Checklist ditinjau sesudah test lulus.

## Detail TASK-005

- [x] Scope task selesai.
    - Kondisi awal: preview dan conflict detector sudah mencegah conflict, tetapi
      belum ada writer yang membuat staging dan melakukan promotion.
    - File dibuat: `packages/StarterKit/src/Generator/Contracts/ModuleGenerationPromotion.php`,
      `packages/StarterKit/src/Generator/ModulePromotionService.php`, dan
      `tests/Unit/ModulePromotionServiceTest.php`.
    - Perubahan: writer membuat staging directory dengan ULID, membuat directory
      dan file output, memvalidasi relative path, membuat parent target, lalu
      melakukan rename atomic ke target. Exception membersihkan staging; target
      existing ditolak tanpa overwrite.
    - Alasan: generator tidak boleh meninggalkan module setengah jadi atau
      merusak output existing ketika proses gagal.
    - Acceptance: promotion sukses menghasilkan file di target; path unsafe
      membersihkan staging; target existing tetap utuh.
    - Evidence: Pint lulus; `php artisan test
tests/Unit/ModulePromotionServiceTest.php` lulus dengan 3 test/9
      assertion; `git diff --check` lulus.
    - Risiko: backup/restore untuk mode overwrite belum diperlukan karena mode
      extension dan overwrite belum diaktifkan pada Phase 3 awal.

## Review Checklist TASK-005

- [x] Checklist ditinjau sebelum coding.
- [x] Positive test atomic promotion tersedia.
- [x] Negative test cleanup failure tersedia.
- [x] Target existing diuji tanpa overwrite.
- [x] Relative path dan boundary output divalidasi.
- [x] Checklist ditinjau sesudah test lulus.

## Detail TASK-006

- [x] Scope task selesai.
    - Kondisi awal: service input, profile, preview, conflict, dan promotion
      sudah tersedia, tetapi belum ada entry point Artisan untuk developer/CI.
    - File dibuat: `packages/StarterKit/src/Console/Commands/ModuleMakeCommand.php`
      dan `tests/Feature/ModuleMakeCommandTest.php`.
    - File diubah: `packages/StarterKit/src/StarterKitServiceProvider.php` untuk
      mendaftarkan command package.
    - Perubahan: command menerima module, domain, profile, `--dry-run`, `--force`,
      `--yes`, dan `--json`; menjalankan preview sebelum promotion; menghasilkan
      code `MODULE_PREVIEWED`, `MODULE_CREATED`, `MODULE_GENERATION_FAILED`, atau
      `MODULE_GENERATION_INVALID` dengan exit code stabil.
    - Alasan: generator harus dapat dipakai developer dan CI melalui interface
      Artisan yang dapat diulang.
    - Acceptance: dry-run tidak menulis; module valid dibuat; input invalid dan
      target conflict gagal sebelum overwrite; human-readable dan JSON tersedia.
    - Evidence: Pint lulus; feature test subprocess CLI lulus 4 test/12
      assertion; `php artisan list --format=json` memuat `module:make`; test
      tidak meninggalkan `app/Modules`.
    - Risiko: `--force` belum membuka overwrite karena mode extension belum
      diaktifkan; output JSON belum memiliki correlation ID dan akan ditinjau
      pada TASK-007.

## Review Checklist TASK-006

- [x] Checklist ditinjau sebelum coding.
- [x] Positive test create dan dry-run tersedia.
- [x] Negative test invalid input dan conflict tersedia.
- [x] Human-readable/JSON dan exit code diuji.
- [x] Command memakai preview sebelum promotion.
- [x] Checklist ditinjau sesudah test lulus.

## Detail TASK-007

- [x] Scope task selesai.
  - Kondisi awal: generator sudah berfungsi, tetapi guard mutasi `--force`,
    evidence quality gate, dan review forbidden dependency belum ditutup.
  - File diubah: `packages/StarterKit/src/Console/Commands/ModuleMakeCommand.php`,
    `tests/Feature/ModuleMakeCommandTest.php`, `implementation-plan.md`,
    `README.md`, dan execution log.
  - Perubahan: operasi non-dry-run sekarang wajib memakai `--force`; `--force`
    hanya menjadi konfirmasi mutasi dan tidak membuka overwrite. Test negatif
    ditambahkan untuk mutasi tanpa `--force`.
  - Alasan: caller harus menyatakan niat sebelum filesystem berubah, sedangkan
    mode extension/overwrite tetap ditunda.
  - Evidence: focused generator 20 test/65 assertion; full backend 85
    test/256 assertion; Pint, TypeScript, ESLint, Prettier, dan build lulus;
    module hasil generate tervalidasi registry; scan Wayfinder dan Laravel Boost
    bersih; `git diff --check` lulus.
  - Risiko: command `module:validate` belum menerima target module spesifik;
    validasi dilakukan pada seluruh registry dengan fixture aktif.

## Review Checklist TASK-007

- [x] Checklist ditinjau sebelum coding.
- [x] Positive dan negative test tersedia.
- [x] Guard `--force`, dry-run, conflict, cleanup, dan atomic promotion ditinjau.
- [x] Path traversal, overwrite, forbidden dependency, dan output sensitive ditinjau.
- [x] Generated module lulus validasi registry global.
- [x] Full backend dan frontend quality gate lulus.
- [x] Checklist ditinjau sesudah verifikasi.

## Definition of Done

- [x] Scope task memiliki detail kondisi awal, file, perubahan, alasan, dan risiko.
- [x] Positive dan negative test tersedia.
- [x] Conflict, dry-run, cleanup, dan atomic promotion diuji.
- [x] Security impact, redaction, path traversal, dan forbidden dependency ditinjau.
- [x] Generated structure dan manifest lulus validation registry.
- [x] Documentation dan execution log diperbarui.
- [x] Checklist ditinjau sebelum dan sesudah pekerjaan.
