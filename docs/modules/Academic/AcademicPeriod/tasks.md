# Tasks: Academic/AcademicPeriod

## Sebelum Mulai

- [x] Scope dan non-scope awal ditentukan.
- [x] Dependency dan keputusan terbuka diketahui.
- [x] Focused test atau cara verifikasi awal ditentukan.
- [x] `AGENTS.md`, `docs/README.md`, `docs/ARCHITECTURE.md`,
  `docs/FOLDER-STRUCTURE.md`, `docs/MODULES.md`, dan roadmap module dibaca.

## Increment 1: Dokumentasi module

- [x] Buat README module.
  - Acceptance: boundary, public boundary, data, permission, audit, operasi,
    dan verifikasi utama tertulis.
  - Verification: `git diff --check`.
- [x] Buat specification module.
  - Acceptance: scope, non-scope, contract, data, authorization, audit,
    dependency, acceptance criteria, dan risiko terbuka tertulis.
  - Verification: `git diff --check`.
- [x] Buat implementation plan module.
  - Acceptance: increment skeleton, data foundation, backend behavior, active
    lifecycle, audit, dan candidate contract tersusun berurutan.
  - Verification: `git diff --check`.

## Increment 2: Skeleton module

- [x] Dry-run generator.
  - Acceptance: target `app/Modules/Academic/AcademicPeriod`, diagnostics
    kosong, dan tidak ada folder optional placeholder.
  - Verification:
    `php artisan module:make Academic AcademicPeriod --dry-run --json --no-ansi`.
- [x] Generate skeleton module.
  - Acceptance: file awal module dibuat tanpa folder kosong placeholder.
  - Verification:
    `php artisan module:make Academic AcademicPeriod --force --yes --no-ansi`.
- [x] Validasi module registry.
  - Acceptance: semua module valid.
  - Verification: `php artisan module:validate --no-ansi`.

## Increment 3: Data foundation

- [x] Tambahkan migration `academic_years`.
  - Acceptance: ULID primary key, unique `code`, date range, status, dan
    timestamp tersedia.
  - Verification: focused migration/model tests.
- [x] Tambahkan migration `academic_terms`.
  - Acceptance: ULID primary key, foreign key ke academic year, unique term code
    per tahun, sequence, date range, status, marker aktif, dan timestamp
    tersedia.
  - Verification: focused migration/model tests.
- [x] Tambahkan persistence minimum.
  - Acceptance: model/repository hanya dibuat bila dipakai oleh use case.
  - Verification: focused unit tests.
- [x] Tambahkan permission identity awal.
  - Acceptance: `academic_period.view` dan `academic_period.manage` valid.
  - Verification: focused permission identity test.

## Increment 4: Backend read/list dan create/update minimum

- [x] Tambahkan read/list tahun akademik dan term.
  - Acceptance: actor dengan `academic_period.view` dapat membaca daftar
    periode akademik.
  - Verification: focused feature test.
- [x] Tambahkan create/update tahun akademik dan term.
  - Acceptance: actor dengan `academic_period.manage` dapat membuat dan
    mengubah periode; duplicate code dan date range invalid ditolak.
  - Verification: focused feature test.
- [x] Tambahkan authorization failure coverage.
  - Acceptance: actor tanpa permission mendapat response forbidden.
  - Verification: focused feature test.

## Increment 5: Active period lifecycle

- [x] Tambahkan activate term.
  - Acceptance: actor dengan `academic_period.manage` dapat menetapkan active
    term sesuai rule yang disetujui.
  - Verification: focused lifecycle feature test.
- [x] Jaga invariant satu active term.
  - Acceptance: aktivasi term baru menonaktifkan term aktif sebelumnya atau
    menolak aktivasi sesuai keputusan scope.
  - Verification: focused lifecycle feature test.
- [x] Tambahkan close term.
  - Acceptance: closed term tidak menjadi active term dan tidak bisa dipakai
    sebagai active period.
  - Verification: focused lifecycle feature test.

## Increment 6: Audit mutation

- [x] Tambahkan audit create/update tahun akademik.
  - Acceptance: mutation menghasilkan audit entry/event yang aman.
  - Verification: focused audit test.
- [x] Tambahkan audit create/update/activate/close term.
  - Acceptance: mutation lifecycle menghasilkan audit entry/event yang aman.
  - Verification: focused audit test.

## Increment 7: Candidate active-period contract

- [x] Dokumentasikan query contract active period.
  - Acceptance: contract menjelaskan input/output/failure tanpa mengekspos
    model Infrastructure.
  - Verification: `git diff --check`.
- [ ] Implementasikan contract hanya jika consumer nyata disetujui.
  - Acceptance: consumer memakai DTO/query public boundary, bukan Eloquent model
    AcademicPeriod.
  - Verification: focused contract/query tests.

  Catatan: belum diimplementasikan pada Increment 7 karena belum ada consumer
  runtime yang disetujui. Ini menjaga contract tidak menjadi placeholder.

## Increment 8: UI/Inertia read page

- [x] Tambahkan route web Inertia periode akademik.
  - Acceptance: actor dengan `academic_period.view` dapat membuka halaman
    `academic.periods.index`; actor tanpa permission ditolak backend.
  - Verification: `php artisan test --filter=AcademicPeriodPresentationTest`.
- [x] Tambahkan frontend module canonical.
  - Acceptance: page berada di
    `resources/js/modules/academic-period/pages/Index.tsx`, komponen
    business-specific berada di
    `resources/js/modules/academic-period/components/`, dan `Index.tsx`
    tetap minimal.
  - Verification: `npm run types:check`, `npm run build`.
- [x] Tambahkan menu sidebar namespace Academic.
  - Acceptance: menu `Periode Akademik` muncul untuk actor berizin
    `academic_period.view` atau `academic_period.manage`.
  - Verification:
    `php artisan test --filter=NavigationSidebarTest|ZiggyRouteTest`.
- [ ] Tambahkan form mutation dan lifecycle UI.
  - Acceptance: create/update tahun, create/update term, activate, dan close
    tersedia via form Inertia dengan flash/validation yang aman.
  - Verification: focused presentation + frontend tests.

Jangan menambahkan pekerjaan baru ke checklist ini tanpa persetujuan user.
