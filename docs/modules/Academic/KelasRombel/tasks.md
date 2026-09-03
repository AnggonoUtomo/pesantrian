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

- [x] Buat DTO/read model KelasRombel ringkas.
  - Acceptance: tidak mengekspos model Infrastructure.
  - Verification: focused unit/feature test.
- [x] Buat use case list/search/filter.
  - Acceptance: filter periode, unit, kurikulum, status, dan search tersedia.
  - Verification: focused feature test.
- [x] Buat use case detail.
  - Acceptance: detail memuat data rombel, santri placement, dan wali kelas.
  - Verification: focused feature test.
- [x] Buat controller/route API read.
  - Acceptance: route API read tersedia dan actor tanpa permission ditolak.
  - Verification: `php artisan route:list --path=academic/class-groups --no-ansi`.

Catatan:

- Web/Inertia read tetap dikerjakan pada Increment 10 agar page dan komponen UI
  tidak dicampur ke backend read/list.

Hasil verifikasi:

- [x] `GET /api/v1/academic/class-groups`
- [x] `GET /api/v1/academic/class-groups/{classGroup}`
- [x] `php artisan test --filter=KelasRombelApi --no-ansi`
- [x] `php artisan route:list --path=academic/class-groups --no-ansi`

## Increment 6: Create/Update Kurikulum, Kelas, dan Rombel

- [x] Buat request validation create/update.
  - Acceptance: duplicate code dan payload invalid ditolak jelas.
  - Verification: focused feature test.
- [x] Buat action create/update kurikulum.
  - Acceptance: mutation tercatat audit.
  - Verification: focused feature/audit test.
- [x] Buat action create/update kelas.
  - Acceptance: kelas scoped ke unit dan urutan tampilan valid.
  - Verification: focused feature/audit test.
- [x] Buat action create/update rombel.
  - Acceptance: rombel scoped ke periode dan unit, capacity valid.
  - Verification: focused feature/audit test.

Hasil verifikasi:

- [x] `POST /api/v1/academic/class-groups/curricula`
- [x] `PATCH /api/v1/academic/class-groups/curricula/{curriculum}`
- [x] `POST /api/v1/academic/class-groups/levels`
- [x] `PATCH /api/v1/academic/class-groups/levels/{level}`
- [x] `POST /api/v1/academic/class-groups`
- [x] `PATCH /api/v1/academic/class-groups/{classGroup}`
- [x] `php artisan test --filter=KelasRombelMutationApi --no-ansi`
- [x] `php artisan test --filter=KelasRombel --no-ansi`
- [x] `php artisan route:list --path=academic/class-groups --no-ansi`

## Increment 7: Penempatan Santri

- [x] Buat action place student.
  - Acceptance: hanya santri aktif yang dapat ditempatkan ke rombel aktif.
  - Verification: focused placement test.
- [x] Buat action transfer student.
  - Acceptance: placement lama ditutup, placement baru aktif, alasan tercatat.
  - Verification: focused placement test.
- [x] Buat action remove student.
  - Acceptance: placement aktif ditutup dengan alasan.
  - Verification: focused placement test.
- [x] Tambahkan audit placement.
  - Acceptance: place/transfer/remove menghasilkan audit aman.
  - Verification: focused audit test.

Hasil verifikasi:

- [x] `POST /api/v1/academic/class-groups/{classGroup}/students`
- [x] `PATCH /api/v1/academic/class-groups/{classGroup}/students/{placement}/transfer`
- [x] `PATCH /api/v1/academic/class-groups/{classGroup}/students/{placement}/remove`
- [x] `php artisan test --filter=KelasRombelPlacementApi --no-ansi`
- [x] `php artisan test --filter=KelasRombel --no-ansi`
- [x] `php artisan route:list --path=academic/class-groups --no-ansi`

## Increment 8: Wali Kelas dan Archive

- [x] Buat action assign/end homeroom.
  - Acceptance: satu rombel hanya punya satu wali kelas aktif.
  - Verification: focused homeroom test.
- [x] Buat use case archive/restore rombel.
  - Acceptance: archived rombel tidak tampil di list aktif default dan bisa
    dipulihkan.
  - Verification: focused archive test.
- [x] Tambahkan audit homeroom dan archive.
  - Acceptance: mutation menghasilkan audit aman.
  - Verification: focused audit test.

Hasil verifikasi:

- [x] `POST /api/v1/academic/class-groups/{classGroup}/homerooms`
- [x] `PATCH /api/v1/academic/class-groups/{classGroup}/homerooms/{homeroom}/end`
- [x] `PATCH /api/v1/academic/class-groups/{classGroup}/archive`
- [x] `PATCH /api/v1/academic/class-groups/{classGroup}/restore`
- [x] `php artisan test --filter=KelasRombelHomeroomArchiveApi --no-ansi`
- [x] `php artisan test --filter=KelasRombel --no-ansi`
- [x] `php artisan route:list --path=academic/class-groups --no-ansi`

## Increment 9: Demo Seeder

- [x] Tambahkan seeder demo KelasRombel idempotent.
  - Acceptance: data demo mencakup kurikulum, kelas, rombel, placement santri,
    dan wali kelas.
  - Verification: `php artisan db:seed --no-ansi`.
- [x] Tambahkan seeder test.
  - Acceptance: seeder aman diulang dan tidak berjalan di production.
  - Verification: focused seeder test.

Hasil:

- Seeder `KelasRombelDemoSeeder` membuat data demo kurikulum, tingkat kelas,
  rombel aktif/draft/closed/archived, placement aktif/pindah/keluar, dan wali
  kelas aktif/ended.
- Seeder memakai data demo yang sudah dibuat oleh Organization, AcademicPeriod,
  HumanResource, dan Santri.
- Seeder tidak berjalan pada environment `production`.

Verifikasi:

- [x] `php artisan test --filter=BusinessDemoSeeder --no-ansi`
- [x] `php artisan db:seed --no-ansi`

## Increment 10: UI/Inertia List dan Detail

- [x] Buat page `resources/js/pages/Academic/KelasRombel/pages/Index.tsx`.
- [x] Buat page `resources/js/pages/Academic/KelasRombel/pages/Show.tsx`.
- [x] Buat komponen table/filter/summary/detail di folder `components`.
- [x] Tambahkan pagination.
- [x] Tambahkan menu sidebar namespace Academic.
- [x] Tambahkan presentation tests/Ziggy route test bila relevan.
- [x] Jalankan typecheck, lint, dan build.

Hasil:

- UI read-only Kelas / Rombel / Kurikulum tersedia di
  `resources/js/pages/Academic/KelasRombel/`.
- Page list mendukung search, filter periode/unit/kurikulum/status/arsip,
  pagination, summary cards, table desktop, dan card mobile.
- Page detail menampilkan identitas rombel, periode, kapasitas, daftar santri,
  dan riwayat wali kelas.
- Menu sidebar ditambahkan di namespace Academic memakai route
  `academic.class-groups.index`.

Verifikasi:

- [x] `php artisan test --filter=KelasRombelPresentation --no-ansi`
- [x] `npm run types:check`
- [x] `npm run lint:check`
- [x] `npm run build`

## Increment 11: UI Mutation dan QA Browser

- [x] Buat form kurikulum/kelas/rombel.
- [x] Buat form placement santri.
- [x] Buat form wali kelas.
- [x] Buat archive/restore confirmation.
- [x] Jalankan browser QA desktop.
- [x] Jalankan browser QA mobile/responsive.
- [x] Dokumentasikan hasil QA.

Hasil:

- Route web mutation ditambahkan untuk kurikulum, tingkat kelas, rombel,
  placement santri, wali kelas, archive, dan restore.
- UI daftar dipindahkan ke `KelasRombelDashboard` agar `pages/Index.tsx`
  tetap minimal.
- Form business-specific ditempatkan di
  `resources/js/pages/Academic/KelasRombel/components/`.
- Browser QA authenticated memakai user demo hasil seeder AccessControl.
- QA desktop membuka `/academic/class-groups`, memverifikasi judul, sidebar
  namespace Academic, pagination, tombol mutation utama, teks penempatan
  santri, teks wali kelas, dan detail rombel.
- QA mobile/responsive membuka halaman yang sama pada viewport kecil dan
  memverifikasi elemen utama tetap tersedia.
- Console browser bersih dari error/warning, tidak ada failed request, dan
  tidak ada response HTTP 4xx/5xx pada halaman yang diuji.
- Screenshot QA lokal tersimpan di `storage/app/qa-kelas-rombel-desktop.png`
  dan `storage/app/qa-kelas-rombel-mobile.png`.

Verifikasi:

- [x] `php artisan route:list --path=academic/class-groups --no-ansi`
- [x] `php artisan test --filter=KelasRombelPresentation --no-ansi`
- [x] `php artisan test --filter=KelasRombel --no-ansi`
- [x] `npm run types:check`
- [x] `npm run lint:check`
- [x] `vendor\bin\pint --dirty --test`
- [x] `npm run build`
- [x] Browser QA Playwright desktop `1366x900`
- [x] Browser QA Playwright mobile `390x844`

## Keputusan Baseline

- [x] Module teknis memakai `Academic/KelasRombel`.
- [x] Nama tampil memakai `Kelas / Rombel / Kurikulum`.
- [x] Kurikulum dimulai sebagai label/struktur minimum, bukan subject map penuh.
- [x] Rombel scoped ke tahun akademik dan semester.
- [x] Penempatan santri aktif dijaga satu rombel aktif per periode.

Jangan menambahkan pekerjaan baru ke checklist ini tanpa persetujuan user.
