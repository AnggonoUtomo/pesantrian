# Tasks: Academic/KelasRombel

## Sebelum Mulai

- [x] Scope dan non-scope awal ditentukan.
- [x] Dependency dan keputusan terbuka diketahui.
- [x] Focused test atau cara verifikasi awal ditentukan.
- [x] `AGENTS.md`, `docs/README.md`, `docs/ARCHITECTURE.md`,
  `docs/FOLDER-STRUCTURE.md`, `docs/MODULES.md`, dan roadmap module dibaca.

## Increment 1: Dokumentasi Module

- [x] Buat folder dokumentasi `docs/modules/Academic/KelasRombel/`.
- [x] Buat `README.md`.
- [x] Buat `specification.md`.
- [x] Buat `plan.md`.
- [x] Buat `tasks.md`.
- [x] Review dan setujui open questions sebelum coding.

## Increment 2: Skeleton Module

- [x] Jalankan dry-run generator module.
  - Acceptance: target `app/Modules/Academic/KelasRombel`, diagnostics kosong,
    dan tidak ada folder optional placeholder.
  - Verification:
    `php artisan module:make Academic KelasRombel --dry-run --json --no-ansi`.
- [x] Generate skeleton module.
  - Acceptance: file awal module dibuat tanpa folder kosong placeholder.
  - Verification:
    `php artisan module:make Academic KelasRombel --force --yes --json --no-ansi`.
- [x] Tambahkan metadata module.
  - Acceptance: manifest dan runtime config sesuai baseline.
  - Verification: `php artisan module:inspect Academic/KelasRombel --json --no-ansi`.
- [x] Tambahkan permission candidate.
  - Acceptance: `kelas_rombel.view`, `kelas_rombel.manage`,
    `kelas_rombel.placement`, dan `kelas_rombel.archive` tersedia.
  - Verification: focused permission identity test.
- [x] Pastikan module registry valid.
  - Verification: `php artisan module:validate --no-ansi`.
- [x] Pastikan command artisan baseline normal.
  - Verification: `php artisan optimize:clear --no-ansi`.

Hasil verifikasi:

- [x] `php artisan module:make Academic KelasRombel --dry-run --json --no-ansi`
- [x] `php artisan module:make Academic KelasRombel --force --yes --json --no-ansi`
- [x] `php artisan module:inspect Academic/KelasRombel --json --no-ansi`
- [x] `php artisan test tests\Unit\KelasRombelPermissionIdentityTest.php --no-ansi`
- [x] `php artisan module:validate --no-ansi`
- [x] `php artisan optimize:clear --no-ansi`

## Increment 3: Contract Readiness

- [x] Review kebutuhan public contract lintas module.
  - Acceptance: field minimum untuk Organization, AcademicPeriod, Santri, dan
    HumanResource jelas.
  - Verification: review manual spec dan source contract existing.
- [x] Implementasikan contract minimum hanya bila consumer nyata disetujui.
  - Acceptance: consumer memakai DTO/query public boundary, bukan Eloquent model
    Infrastructure module lain.
  - Verification: focused contract/query tests.

Hasil verifikasi:

- [x] Public contract `EducationUnitReader` tersedia di
  `Organization/Organization` untuk selector unit pendidikan aktif.
- [x] Public contract `ActiveAcademicPeriodReader` tersedia di
  `Academic/AcademicPeriod` untuk periode akademik aktif.
- [x] Public contract `ActiveStudentReader` tersedia di `Pesantrian/Santri`
  untuk selector santri aktif.
- [x] Public contract `ActiveEmployeeReader` tersedia di
  `HumanResource/HumanResource` untuk selector pegawai/guru aktif.
- [x] `php artisan test --filter=KelasRombelContractReadiness --no-ansi`

## Increment 4: Data Foundation

- [x] Buat migration `academic_curricula`.
  - Acceptance: ULID primary key, unique `code`, `name`, `description`,
    `status`, dan timestamp tersedia.
  - Verification: focused schema test.
- [x] Buat migration `class_levels`.
  - Acceptance: ULID primary key, `unit_id`, unique code per unit, sequence,
    status, dan timestamp tersedia.
  - Verification: focused schema test.
- [x] Buat migration `class_groups`.
  - Acceptance: ULID primary key, period/unit/curriculum/class level reference,
    unique code per unit/periode, capacity, status, dan timestamp tersedia.
  - Verification: focused schema test.
- [x] Buat migration `class_group_students`.
  - Acceptance: placement santri aktif dapat dijaga unique per periode.
  - Verification: focused placement invariant test.
- [x] Buat migration `class_group_homerooms`.
  - Acceptance: satu wali kelas aktif per rombel dapat dijaga.
  - Verification: focused homeroom invariant test.

Hasil verifikasi:

- [x] Migration `2026_09_02_000000_create_kelas_rombel_tables.php` tersedia di
  module `Academic/KelasRombel`.
- [x] Infrastructure model tersedia untuk curriculum, class level, class group,
  placement santri, dan wali kelas.
- [x] `class_group_students.active_period_student_key` menjadi unique nullable
  guard untuk satu placement aktif per santri per semester.
- [x] `class_group_homerooms.active_class_group_key` menjadi unique nullable
  guard untuk satu wali kelas aktif per rombel.
- [x] `php artisan test --filter=KelasRombelDataFoundation --no-ansi`

## Increment 5: Backend Read/List

- [ ] Buat DTO/read model KelasRombel ringkas.
  - Acceptance: tidak mengekspos model Infrastructure.
  - Verification: focused unit/feature test.
- [ ] Buat use case list/search/filter.
  - Acceptance: filter periode, unit, kurikulum, status, dan search tersedia.
  - Verification: focused feature test.
- [ ] Buat use case detail.
  - Acceptance: detail memuat data rombel, santri placement, dan wali kelas.
  - Verification: focused feature test.
- [ ] Buat controller/route read.
  - Acceptance: route API/web read tersedia dan actor tanpa permission ditolak.
  - Verification: `php artisan route:list --path=academic/class-groups --no-ansi`.

## Increment 6: Create/Update Kurikulum, Kelas, dan Rombel

- [ ] Buat request validation create/update.
  - Acceptance: duplicate code dan payload invalid ditolak jelas.
  - Verification: focused feature test.
- [ ] Buat action create/update kurikulum.
  - Acceptance: mutation tercatat audit.
  - Verification: focused feature/audit test.
- [ ] Buat action create/update kelas.
  - Acceptance: kelas scoped ke unit dan urutan tampilan valid.
  - Verification: focused feature/audit test.
- [ ] Buat action create/update rombel.
  - Acceptance: rombel scoped ke periode dan unit, capacity valid.
  - Verification: focused feature/audit test.

## Increment 7: Penempatan Santri

- [ ] Buat action place student.
  - Acceptance: hanya santri aktif yang dapat ditempatkan ke rombel aktif.
  - Verification: focused placement test.
- [ ] Buat action transfer student.
  - Acceptance: placement lama ditutup, placement baru aktif, alasan tercatat.
  - Verification: focused placement test.
- [ ] Buat action remove student.
  - Acceptance: placement aktif ditutup dengan alasan.
  - Verification: focused placement test.
- [ ] Tambahkan audit placement.
  - Acceptance: place/transfer/remove menghasilkan audit aman.
  - Verification: focused audit test.

## Increment 8: Wali Kelas dan Archive

- [ ] Buat action assign/end homeroom.
  - Acceptance: satu rombel hanya punya satu wali kelas aktif.
  - Verification: focused homeroom test.
- [ ] Buat use case archive/restore rombel.
  - Acceptance: archived rombel tidak tampil di list aktif default dan bisa
    dipulihkan.
  - Verification: focused archive test.
- [ ] Tambahkan audit homeroom dan archive.
  - Acceptance: mutation menghasilkan audit aman.
  - Verification: focused audit test.

## Increment 9: Demo Seeder

- [ ] Tambahkan seeder demo KelasRombel idempotent.
  - Acceptance: data demo mencakup kurikulum, kelas, rombel, placement santri,
    dan wali kelas.
  - Verification: `php artisan db:seed --no-ansi`.
- [ ] Tambahkan seeder test.
  - Acceptance: seeder aman diulang dan tidak berjalan di production.
  - Verification: focused seeder test.

## Increment 10: UI/Inertia List dan Detail

- [ ] Buat page `resources/js/pages/Academic/KelasRombel/pages/Index.tsx`.
- [ ] Buat page `resources/js/pages/Academic/KelasRombel/pages/Show.tsx`.
- [ ] Buat komponen table/filter/summary/detail di folder `components`.
- [ ] Tambahkan pagination.
- [ ] Tambahkan menu sidebar namespace Academic.
- [ ] Tambahkan presentation tests/Ziggy route test bila relevan.
- [ ] Jalankan typecheck, lint, dan build.

## Increment 11: UI Mutation dan QA Browser

- [ ] Buat form kurikulum/kelas/rombel.
- [ ] Buat form placement santri.
- [ ] Buat form wali kelas.
- [ ] Buat archive/restore confirmation.
- [ ] Jalankan browser QA desktop.
- [ ] Jalankan browser QA mobile/responsive.
- [ ] Dokumentasikan hasil QA.

## Keputusan Baseline

- [x] Module teknis memakai `Academic/KelasRombel`.
- [x] Nama tampil memakai `Kelas / Rombel / Kurikulum`.
- [x] Kurikulum dimulai sebagai label/struktur minimum, bukan subject map penuh.
- [x] Rombel scoped ke tahun akademik dan semester.
- [x] Penempatan santri aktif dijaga satu rombel aktif per periode.

Jangan menambahkan pekerjaan baru ke checklist ini tanpa persetujuan user.
