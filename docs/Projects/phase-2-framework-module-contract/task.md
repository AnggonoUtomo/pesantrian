# Task Phase 2 Framework dan Module Contract

| ID | Increment | Task | Acceptance | Verifikasi | Status |
|---|---|---|---|---|---|
| TASK-001 | INC-001 | Finalisasi contract dan ADR | Boundary disetujui | Review spec/ADR | Selesai |
| TASK-002 | INC-002 | Buat package StarterKit | Package dapat dimuat Laravel | Composer/autoload/boot | Selesai |
| TASK-003 | INC-003 | Buat manifest contract | Manifest valid/invalid terdeteksi | Unit test validator | Selesai |
| TASK-004 | INC-004 | Buat permission contract | Metadata permission tervalidasi | Unit test schema | Selesai |
| TASK-005 | INC-005 | Buat registry dan discovery | Valid ditemukan; invalid diisolasi | Integration test | Selesai |
| TASK-006 | INC-006 | Buat command module | Tiga command mendukung JSON | Command test | Selesai |
| TASK-007 | INC-007 | Tutup Phase 2 | Quality gate dan docs lengkap | Full verification | Selesai |

## Detail Task

### TASK-002 — Buat package StarterKit

- [x] Scope task selesai.
  - Kondisi awal: `packages/StarterKit` belum ada dan root Composer belum
    memiliki path repository framework.
  - File dibuat: `packages/StarterKit/composer.json`,
    `packages/StarterKit/src/StarterKitServiceProvider.php`, dan
    `tests/Unit/StarterKitPackageTest.php`.
  - File diubah: root `composer.json` dan `composer.lock`.
  - Perubahan: package `starterkit/framework:1.0.0` ditambahkan dengan PSR-4
    `StarterKit\\`, Laravel provider discovery, dan path repository symlink.
  - Alasan: reusable framework harus dapat dimuat sebagai package terpisah.
  - Evidence: Composer install, package discovery, provider autoload, Laravel
    boot, dan focused test lulus.
  - Risiko: package masih skeleton; registry dan contract belum tersedia.

### TASK-001 — Finalisasi contract dan ADR

- [x] Scope task selesai.
  - Kondisi awal: ADR Phase 2 masih berstatus diusulkan.
  - File diubah: ADR-0001, implementation plan, dan task plan.
  - Perubahan: ADR dan TASK-001 ditandai diterima/selesai.
  - Alasan: Phase 2 sudah disetujui user.
  - Evidence: approval user dan inventory module selesai.
  - Risiko: keputusan dapat direvisi melalui ADR baru jika implementasi menemukan
    konflik.

Setiap task akan ditulis dengan pola berikut sebelum ditandai selesai:

```markdown
- [x] Scope task selesai.
  - Kondisi awal: ...
  - File dibuat/diubah: ...
  - Perubahan: ...
  - Alasan: ...
  - Evidence: ...
  - Risiko: ...
```

### TASK-003 — Buat manifest contract

- [x] Scope task selesai.
  - Kondisi awal: `ModuleManifest` sudah dibuat, tetapi behavior test valid dan
    invalid belum lengkap.
  - File dibuat: `packages/StarterKit/src/Modules/Contracts/ModuleManifest.php`
    dan `tests/Unit/ModuleManifestTest.php`.
  - Perubahan: DTO readonly dan validasi field dasar ditambahkan.
  - Alasan: manifest adalah identity deklaratif module.
  - Evidence: 6 test/6 assertion lulus untuk valid, field hilang, status,
    namespace, path, dan provider.
  - Risiko: validator permission dan duplicate identity masih dikerjakan pada
    task berikutnya.

### TASK-004 — Buat permission contract

- [x] Scope task selesai.
  - Kondisi awal: `permissions.php` belum memiliki validator runtime.
  - File dibuat: `PermissionIdentity.php` dan `PermissionIdentityTest.php`.
  - Perubahan: validator key dot notation, description, module, dan sensitive.
  - Alasan: permission identity harus dimiliki module owner.
  - Evidence: Pint lulus; 6 test/8 assertion lulus untuk valid, field hilang,
    key, description, module, dan sensitive.
  - Risiko: duplicate key masih diperiksa pada registry.

### TASK-005 — Buat registry dan discovery

- [x] Scope task selesai.
  - Kondisi awal: registry sudah membaca `module.json`, tetapi belum memvalidasi
    file `permissions.php` dan belum mendeteksi permission key ganda.
  - File dibuat: `ModuleRegistry.php` dan `ModuleRegistryTest.php`.
  - Perubahan: registry memastikan `permissions.php` ada, memvalidasi setiap
    item dengan `PermissionIdentity::fromArray()`, dan menolak duplicate
    permission key. Module invalid tetap masuk `diagnostics`.
  - Alasan: permission identity harus tervalidasi pada boundary registry.
  - Acceptance: folder hilang kosong; module valid ditemukan; module invalid,
    duplicate module identity, dan duplicate permission key menghasilkan
    diagnostic yang dapat dibaca.
  - Evidence: Pint lulus; `php artisan test tests/Unit/ModuleRegistryTest.php`
    menghasilkan 4 test/10 assertion lulus; `git diff --check` lulus.
  - Risiko: isi `config_source` belum dieksekusi sebagai konfigurasi runtime;
    registry hanya memverifikasi file source tersedia. Eksekusi runtime ditunda
    untuk increment infrastructure agar tidak menjalankan konfigurasi saat
    discovery read-only.

### TASK-006 — Buat command module

- [x] Scope task selesai.
  - Kondisi awal: tiga command sudah tersedia, tetapi test hanya mencakup
    kondisi tanpa module sehingga failure path dan exit code belum terbukti.
  - File diubah: `packages/StarterKit/src/Console/Commands/ModuleListCommand.php`
    dan `tests/Feature/ModuleCommandTest.php`.
  - Perubahan: `module:list --json` sekarang memakai code
    `MODULE_LIST_FAILED` saat discovery memiliki diagnostic. Test fixture
    sementara ditambahkan untuk module dengan status invalid, lalu ketiga
    command diuji dengan exit code `1` dan code JSON failure yang sesuai.
  - Alasan: CI dan developer perlu membedakan hasil berhasil dan gagal secara
    konsisten.
  - Acceptance: `module:discover`, `module:validate`, dan `module:list`
    mendukung output JSON, exit code sukses, exit code gagal, serta diagnostic
    tanpa membocorkan data sensitif.
  - Evidence: Pint lulus; `php artisan test tests/Feature/ModuleCommandTest.php`
    menghasilkan 4 test/12 assertion lulus.
  - Risiko: full quality gate dan scan dependency masih menjadi TASK-007.

## Definition of Done

- [x] Scope task selesai dengan detail kondisi awal, file, perubahan, alasan,
  evidence, dan risiko.
- [x] Test positif dan negatif tersedia pada contract, registry, dan command.
- [x] Duplicate identity dan invalid module diuji melalui fixture Basic,
  Duplicate, dan PermissionDuplicate.
- [x] Security impact dan diagnostic redaction ditinjau; diagnostic hanya
  memuat path dan pesan validasi, tanpa payload permission atau secret.
- [x] Command human-readable dan JSON diverifikasi; focused command test dan
  smoke command lulus.
- [x] Documentation dan execution log diperbarui untuk setiap increment.
- [x] Checklist ditinjau sebelum dan sesudah pekerjaan.
- [x] Full quality gate Phase 2 selesai.

### TASK-007 — Tutup Phase 2

- [x] Scope task selesai.
  - Kondisi awal: quality gate, static analysis, build, dependency scan, dan
    review dokumentasi belum memiliki evidence lengkap.
  - File diubah: `resources/js/lib/route.ts`,
    `resources/js/types/global.d.ts`, dan dokumen Phase 2 terkait.
  - Perubahan: format frontend diperbaiki dan seluruh quality gate dijalankan.
  - Alasan: Phase 2 harus siap menjadi dasar Phase 3 dengan bukti teknis.
  - Evidence: full test 65 test/191 assertion; Pint, PHPStan, ESLint,
    TypeScript, Prettier, build, command module, forbidden dependency scan,
    dan `git diff --check` lulus.
  - Risiko: GitHub Actions dan production deployment belum dijalankan.
