# Tasks: Pesantrian/Tahfidz

## Increment 1: Documentation Baseline

- [x] Buat folder dokumentasi module.
- [x] Buat README module.
- [x] Buat specification module.
- [x] Buat implementation plan.
- [x] Buat task roadmap.
- [x] Update indeks module.

Hasil:

- Boundary Tahfidz ditetapkan sebagai pencatatan program, target, setoran,
  murojaah, review, dan progres hafalan santri.
- Nama tampil ditetapkan `Tahfidz / Hafalan` agar familiar untuk operator
  pesantren.
- Integrasi sertifikat, portal wali/santri, upload audio/video, dan nilai rapor
  formal ditunda sampai baseline operasional stabil.

Verifikasi:

- [x] Review manual dokumen module dan roadmap.

## Increment 2: Module Skeleton dan Permission

- [x] Jalankan dry-run generator module.
- [x] Buat module `Pesantrian/Tahfidz`.
- [x] Tambahkan permission identity.
- [x] Wire permission ke seeder AccessControl.
- [x] Jalankan module validate.

Acceptance:

- `php artisan module:validate --no-ansi` berhasil.
- Permission `tahfidz.view`, `tahfidz.manage`, `tahfidz.record`,
  `tahfidz.review`, dan `tahfidz.archive` tersedia.
- Belum ada table bisnis pada increment ini.

Hasil:

- Skeleton source module dibuat di `app/Modules/Pesantrian/Tahfidz/`.
- Module terdaftar sebagai `enabled`, bootable, dan terbaca oleh registry
  module.
- Permission baseline dibuat untuk akses lihat, kelola program/target,
  pencatatan setoran, review setoran, dan arsip/batal data Tahfidz.
- Seeder AccessControl memberi akses operasional awal:
  `OperatorSantri` dapat melihat, mengelola, mencatat, dan review;
  `OperatorAkademik`, `Auditor`, dan `Viewer` dapat melihat.
- Permission `tahfidz.archive` sementara hanya melekat ke role super/admin
  keamanan melalui mekanisme all-permission, karena arsip/batal catatan
  termasuk aksi sensitif.

Verifikasi:

- [x] `php artisan module:make Pesantrian Tahfidz --dry-run --json --no-ansi`
- [x] `php artisan test tests/Unit/TahfidzPermissionIdentityTest.php tests/Feature/AccessControlSeederTest.php tests/Feature/BusinessDemoSeederTest.php --no-ansi`
- [x] `php artisan module:validate --no-ansi`
- [x] `php artisan module:list --json --no-ansi`

## Increment 3: Contract Readiness

- [x] Audit contract Santri aktif.
- [x] Audit contract pembimbing/pegawai aktif.
- [x] Audit contract periode akademik aktif.
- [x] Tambahkan contract hanya bila belum tersedia dan memang dibutuhkan.
- [x] Tambahkan tests readiness lintas module.

Acceptance:

- Tahfidz tidak membaca model Infrastructure module lain.
- Candidate santri, pembimbing, dan periode bisa diambil melalui public
  contract.

Hasil:

- Contract `ActiveStudentReader` dari `Pesantrian/Santri` sudah cukup untuk
  selector dan validasi santri aktif.
- Contract `ActiveEmployeeReader` dari `HumanResource/HumanResource` sudah
  cukup untuk selector pembimbing aktif.
- Contract `ActiveAcademicPeriodReader` dari `Academic/AcademicPeriod` sudah
  cukup untuk konteks periode target hafalan.
- Tidak ada contract baru yang dibuat karena kebutuhan Tahfidz awal sudah
  terlayani oleh public contract existing.
- Guardrail readiness ditambahkan agar Tahfidz tidak mengambil model
  Infrastructure module dependency secara langsung.

Verifikasi:

- [x] `php artisan test tests/Feature/TahfidzContractReadinessTest.php --no-ansi`
- [x] `php artisan module:validate --no-ansi`

## Increment 4: Data Foundation

- [x] Buat migration program tahfidz.
- [x] Buat migration target hafalan.
- [x] Buat migration setoran/murojaah.
- [x] Buat migration revision setoran.
- [x] Buat record model.
- [x] Buat factory minimum.
- [x] Jalankan focused data foundation tests.

Acceptance:

- Table memakai ULID.
- Kode program unique.
- Target dan setoran menyimpan snapshot santri/pembimbing.
- Nama index eksplisit aman untuk MySQL.

Hasil:

- Migration `2026_09_07_000000_create_tahfidz_tables.php` membuat table:
  `tahfidz_programs`, `tahfidz_targets`, `tahfidz_submissions`, dan
  `tahfidz_submission_revisions`.
- ServiceProvider Tahfidz memuat migration module dari folder
  `Database/Migrations`.
- Record model Infrastructure dibuat untuk program, target, setoran, dan
  histori revisi.
- Factory minimum dibuat untuk semua record model agar seeder/demo/test
  berikutnya lebih mudah disusun.
- Snapshot santri, periode, dan pembimbing disimpan pada target/setoran agar
  histori tetap terbaca walaupun master data berubah.

Verifikasi:

- [x] `php artisan test tests/Feature/TahfidzDataFoundationTest.php --no-ansi`
- [x] `php artisan module:validate --no-ansi`

## Increment 5: Backend Read/List

- [ ] Buat DTO/read model Tahfidz.
- [ ] Buat query list/search/filter.
- [ ] Buat query detail.
- [ ] Buat controller/resource API read.
- [ ] Jalankan focused API tests.

Acceptance:

- List mendukung filter program, santri, pembimbing, periode, tipe, status, dan
  pagination.
- Detail menampilkan target, setoran, pembimbing, dan summary.
- Actor tanpa permission view ditolak.

## Increment 6: Program dan Target Hafalan

- [ ] Buat request validation program/target.
- [ ] Buat action create/update program.
- [ ] Buat action create/update target.
- [ ] Tambahkan audit program/target.
- [ ] Jalankan focused mutation tests.

Acceptance:

- Program aktif bisa dibuat dan diubah.
- Target hanya untuk santri aktif.
- Target menyimpan snapshot santri dan periode.

## Increment 7: Setoran dan Murojaah

- [ ] Buat request validation setoran.
- [ ] Buat action create/update setoran.
- [ ] Buat rule rentang juz/surah/ayat.
- [ ] Tambahkan audit setoran.
- [ ] Jalankan focused mutation tests.

Acceptance:

- Setoran hafalan baru dan murojaah bisa dibuat.
- Rentang ayat valid.
- Setoran final/void tidak bisa diedit langsung tanpa lifecycle yang sesuai.

## Increment 8: Review, Koreksi, dan Void

- [ ] Buat action review accepted/needs_revision.
- [ ] Buat action void dengan alasan.
- [ ] Buat revision history.
- [ ] Tambahkan audit lifecycle.
- [ ] Jalankan focused lifecycle tests.

Acceptance:

- Review memakai permission `tahfidz.review`.
- Void wajib alasan dan tidak menghapus data.
- Koreksi/revision meninggalkan histori.

## Increment 9: Demo Seeder

- [ ] Buat `TahfidzDemoSeeder`.
- [ ] Update `DatabaseSeeder`.
- [ ] Tambahkan test idempotent seeder.
- [ ] Dokumentasikan data demo.

Acceptance:

- Seeder mencakup program, target, setoran accepted, submitted,
  needs_revision, dan void.
- Seeder memakai data demo Santri dan SDM yang sudah ada.
- Seeder aman diulang dan tidak berjalan pada environment production.

## Increment 10: UI/Inertia List dan Detail

- [ ] Buat page index.
- [ ] Buat page detail.
- [ ] Buat komponen filter/table/card/summary/pagination.
- [ ] Tambahkan sidebar menu namespace Pesantrian.
- [ ] Tambahkan presentation/Ziggy tests.
- [ ] Jalankan typecheck, lint, dan build.

Acceptance:

- Page tetap tipis.
- UI berada di `resources/js/pages/Pesantrian/Tahfidz/`.
- Console browser bersih dari route/Ziggy error.

## Increment 11: UI Mutation

- [ ] Buat dialog create/update program.
- [ ] Buat dialog create/update target.
- [ ] Buat dialog create/update setoran.
- [ ] Buat dialog review.
- [ ] Buat confirmation void.
- [ ] Jalankan typecheck, lint, dan build.

Acceptance:

- Form mutation berada di folder `components`.
- Review/void memakai confirmation atau dialog alasan.
- Backend tetap authority permission dan validasi.

## Increment 12: QA Browser dan User Manual

- [ ] Jalankan browser QA desktop.
- [ ] Jalankan browser QA mobile/responsive.
- [ ] Update user manual lifecycle.
- [ ] Update README/tasks hasil final.

Acceptance:

- Flow list/detail/mutation utama bisa diuji manual.
- Relasi ke module yang belum ada diberi keterangan.
- Console browser bersih dari error.

## Keputusan Baseline

- [x] Module teknis memakai `Pesantrian/Tahfidz`.
- [x] Nama tampil memakai `Tahfidz / Hafalan`.
- [x] Tahfidz awal memakai admin/internal, bukan public form.
- [x] Status hasil setoran awal dibuat sederhana untuk operator.
- [x] Upload audio/video dan sertifikat ditunda.
- [x] Portal wali/santri ditunda.
