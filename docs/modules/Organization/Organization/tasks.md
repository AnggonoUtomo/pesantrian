# Tasks: Organization/Organization

## Sebelum Mulai

- [x] Scope dan non-scope awal ditentukan.
- [x] Dependency dan keputusan terbuka diketahui.
- [x] Focused test atau cara verifikasi awal ditentukan.
- [x] `AGENTS.md`, `docs/README.md`, `docs/ARCHITECTURE.md`,
  `docs/FOLDER-STRUCTURE.md`, `docs/MODULES.md`, dan panduan generator module
  dibaca.

## Increment 1: Dokumentasi module

- [x] Buat README module.
  - Acceptance: boundary, public boundary, data, permission, audit, dan
    verifikasi utama tertulis.
  - Verification: `git diff --check`.
- [x] Buat specification module.
  - Acceptance: scope, non-scope, contract, data, authorization, audit,
    dependency, acceptance criteria, dan risiko terbuka tertulis.
  - Verification: `git diff --check`.
- [x] Buat implementation plan module.
  - Acceptance: increment skeleton, data foundation, backend behavior, dan audit
    tersusun berurutan.
  - Verification: `git diff --check`.

## Increment 2: Skeleton module

- [x] Dry-run generator.
  - Acceptance: target `app/Modules/Organization/Organization`, diagnostics
    kosong, `directories: []`.
  - Verification:
    `php artisan module:make Organization Organization --dry-run --json --no-ansi`.
- [x] Generate skeleton module.
  - Acceptance: file awal module dibuat tanpa folder kosong placeholder.
  - Verification:
    `php artisan module:make Organization Organization --force --yes --no-ansi`.
- [x] Validasi module registry.
  - Acceptance: semua module valid.
  - Verification: `php artisan module:validate --no-ansi`.

## Increment 3: Data foundation

- [ ] Tambahkan migration `organization_units`.
  - Acceptance: ULID primary key, unique `code`, parent nullable, status, dan
    timestamp tersedia.
  - Verification: focused migration/model tests.
- [ ] Tambahkan persistence minimum.
  - Acceptance: model/repository hanya dibuat bila dipakai oleh use case.
  - Verification: focused unit tests.
- [ ] Tambahkan permission identity awal.
  - Acceptance: `organization.view` dan `organization.manage` valid.
  - Verification: focused permission identity test.

## Increment 4: Backend read/list dan create/update minimum

- [ ] Tambahkan read/list unit.
  - Acceptance: actor dengan `organization.view` dapat membaca daftar unit.
  - Verification: focused feature test.
- [ ] Tambahkan create/update unit.
  - Acceptance: actor dengan `organization.manage` dapat membuat dan mengubah
    unit; duplicate `code` ditolak.
  - Verification: focused feature test.
- [ ] Tambahkan authorization failure coverage.
  - Acceptance: actor tanpa permission mendapat response forbidden.
  - Verification: focused feature test.

## Increment 5: Audit mutation

- [ ] Tambahkan audit create/update unit.
  - Acceptance: mutation menghasilkan audit entry/event yang aman.
  - Verification: focused audit test.

## Hasil

- [ ] Scope backend slice minimum selesai.
  - Perubahan: skeleton, data foundation, read/list, create/update, permission,
    dan audit minimum.
  - Verification:
    - `php artisan module:validate --no-ansi`
    - focused tests Organization
    - `php artisan starter:verify --no-ansi`
    - `git diff --check`
  - Risiko terbuka: UI dan hierarchy lanjutan tetap menunggu increment
    terpisah.

Jangan menambahkan pekerjaan baru ke checklist ini tanpa persetujuan user.
