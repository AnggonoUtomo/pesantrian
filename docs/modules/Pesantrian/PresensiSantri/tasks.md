# Tasks: Pesantrian/PresensiSantri

## Increment 1: Documentation Baseline

- [x] Buat folder dokumentasi module.
- [x] Buat README module.
- [x] Buat specification module.
- [x] Buat implementation plan.
- [x] Buat task roadmap.
- [x] Update indeks module.

Hasil:

- Boundary PresensiSantri ditetapkan sebagai pencatatan sesi dan detail
  kehadiran santri.
- Konteks baseline ditetapkan: kelas/rombel, asrama, dan kegiatan umum.
- Status kehadiran baseline ditetapkan: hadir, izin, sakit, alfa, terlambat.
- Integrasi `PerizinanSantri`, `KedisiplinanSantri`, dan `KesehatanSantri`
  ditunda sampai module terkait dibuat.

Verifikasi:

- [x] Review manual dokumen oleh user sebelum Increment 2.

## Increment 2: Module Skeleton dan Permission

- [x] Jalankan dry-run generator module.
- [x] Buat module `Pesantrian/PresensiSantri`.
- [x] Tambahkan permission identity.
- [x] Wire permission ke seeder AccessControl.
- [x] Jalankan module validate.

Acceptance:

- `php artisan module:validate --no-ansi` berhasil.
- Permission `presensi_santri.view`, `presensi_santri.manage`,
  `presensi_santri.submit`, `presensi_santri.revise`, dan
  `presensi_santri.archive` tersedia.

Hasil:

- Skeleton source module dibuat di `app/Modules/Pesantrian/PresensiSantri/`.
- Module terdaftar sebagai `enabled`, bootable, dan terbaca oleh registry
  module.
- Permission baseline dibuat untuk akses lihat, kelola draft, submit, revisi,
  dan arsip/batal sesi.
- Seeder AccessControl memberi akses operasional awal:
  `OperatorSantri` dan `OperatorAkademik` dapat melihat, mengelola, submit,
  dan revisi; `Auditor` dan `Viewer` hanya dapat melihat.
- Permission `presensi_santri.archive` sementara hanya melekat ke role super/
  admin keamanan melalui mekanisme all-permission, karena arsip/batal sesi
  termasuk aksi sensitif.

Verifikasi:

- [x] `php artisan module:make Pesantrian PresensiSantri --dry-run --json --no-ansi`
- [x] `php artisan test tests/Unit/PresensiSantriPermissionIdentityTest.php tests/Feature/AccessControlSeederTest.php tests/Feature/BusinessDemoSeederTest.php --no-ansi`
- [x] `php artisan module:validate --no-ansi`
- [x] `php artisan module:list --json --no-ansi`

## Increment 3: Contract Readiness

- [x] Audit contract Santri aktif yang sudah tersedia.
- [x] Tambahkan contract roster rombel aktif bila belum ada.
- [x] Tambahkan contract penghuni asrama aktif bila belum ada.
- [x] Tambahkan tests readiness lintas module.

Acceptance:

- PresensiSantri tidak membaca model Infrastructure module lain.
- Candidate santri untuk presensi bisa berasal dari selector umum, rombel, atau
  asrama.

Hasil:

- `Pesantrian/Santri` sudah menyediakan `ActiveStudentReader` untuk selector
  santri aktif; tidak perlu contract baru.
- `Academic/KelasRombel` menyediakan contract read-only
  `ActiveClassGroupRosterReader` untuk daftar santri aktif dalam rombel.
- `Pesantrian/Asrama` menyediakan contract read-only
  `ActiveDormitoryResidentReader` untuk daftar penghuni aktif asrama/kamar.
- Contract baru tetap dimiliki module sumber data masing-masing, sehingga
  `Pesantrian/PresensiSantri` nanti cukup mengonsumsi public contract dan tidak
  membaca model Infrastructure module lain.

Verifikasi:

- [x] `php artisan test tests/Feature/PresensiSantriContractReadinessTest.php --no-ansi`
- [x] `rg -n "App\\Modules\\(Academic\\KelasRombel|Pesantrian\\Asrama|Pesantrian\\Santri)\\Infrastructure" app\\Modules\\Pesantrian\\PresensiSantri tests\\Feature\\PresensiSantriContractReadinessTest.php`

## Increment 4: Data Foundation

- [x] Buat migration session presensi.
- [x] Buat migration entry presensi.
- [x] Buat migration revision presensi.
- [x] Buat record model.
- [x] Buat factory minimum.
- [x] Jalankan focused data foundation tests.

Acceptance:

- Table memakai ULID.
- Entry unique per sesi dan santri.
- Nama index eksplisit aman untuk MySQL.

Hasil:

- Migration `student_attendance_sessions`,
  `student_attendance_entries`, dan `student_attendance_revisions` dibuat di
  module `Pesantrian/PresensiSantri`.
- `ServiceProvider` PresensiSantri memuat migration module.
- Record model Eloquent dibuat untuk session, entry, dan revision.
- Factory minimum dibuat untuk mendukung test dan seeder demo pada increment
  berikutnya.
- Constraint baseline dibuat:
  - unique sesi per tanggal, konteks, dan kode sesi;
  - unique entry per session dan santri;
  - revision history dapat menyimpan beberapa perubahan untuk session yang
    sama.

Verifikasi:

- [x] `php artisan test tests/Feature/PresensiSantriDataFoundationTest.php --no-ansi`

## Increment 5: Backend Read/List

- [ ] Buat DTO/read model presensi.
- [ ] Buat query list/search/filter.
- [ ] Buat query detail.
- [ ] Buat controller/resource API read.
- [ ] Jalankan focused API tests.

Acceptance:

- List presensi mendukung filter tanggal, konteks, status, dan pagination.
- Detail presensi mengembalikan summary dan entries.
- Actor tanpa permission view ditolak.

## Increment 6: Create/Update Draft dan Entry

- [ ] Buat request validation create/update session.
- [ ] Buat action create/update draft.
- [ ] Buat action update entries.
- [ ] Tambahkan audit create/update.
- [ ] Jalankan focused mutation tests.

Acceptance:

- Sesi draft bisa dibuat dan diubah.
- Entry presensi bisa diisi.
- Sesi submitted/void tidak bisa diedit langsung.

## Increment 7: Submit, Revisi, dan Void

- [ ] Buat action submit.
- [ ] Buat action revise.
- [ ] Buat action void.
- [ ] Tambahkan audit lifecycle.
- [ ] Jalankan focused lifecycle tests.

Acceptance:

- Submit mengunci sesi draft.
- Revisi wajib alasan.
- Void wajib alasan dan tidak menghapus data.

## Increment 8: Demo Seeder

- [ ] Buat `PresensiSantriDemoSeeder`.
- [ ] Update `DatabaseSeeder`.
- [ ] Tambahkan test idempotent seeder.
- [ ] Dokumentasikan data demo.

Acceptance:

- Seeder mencakup sesi kelas, asrama, dan kegiatan umum.
- Seeder mencakup variasi status hadir/izin/sakit/alfa/terlambat.
- Seeder aman diulang dan tidak berjalan pada environment production.

## Increment 9: UI/Inertia List dan Detail

- [ ] Buat page index.
- [ ] Buat page detail.
- [ ] Buat komponen filter/table/card/summary/pagination.
- [ ] Tambahkan sidebar menu namespace Pesantrian.
- [ ] Tambahkan presentation/Ziggy tests.
- [ ] Jalankan typecheck, lint, dan build.

Acceptance:

- Page tetap tipis.
- UI berada di `resources/js/pages/Pesantrian/PresensiSantri/`.
- Console browser bersih dari route/Ziggy error.

## Increment 10: UI Mutation

- [ ] Buat dialog create/update session.
- [ ] Buat editor entry presensi.
- [ ] Buat confirmation submit.
- [ ] Buat dialog revisi.
- [ ] Buat confirmation void.
- [ ] Jalankan typecheck, lint, dan build.

Acceptance:

- Form mutation berada di folder `components`.
- Submit/revisi/void memakai confirmation.
- Backend tetap authority permission dan validasi.

## Increment 11: QA Browser dan User Manual

- [ ] Jalankan browser QA desktop.
- [ ] Jalankan browser QA mobile/responsive.
- [ ] Update user manual lifecycle.
- [ ] Update README/tasks hasil final.

Acceptance:

- Flow list/detail/mutation utama bisa diuji manual.
- Relasi ke module yang belum ada diberi keterangan.
- Console browser bersih dari error.

## Keputusan Baseline

- [x] Module teknis memakai `Pesantrian/PresensiSantri`.
- [x] Nama tampil memakai `Presensi Santri`.
- [x] Presensi awal memakai admin/internal, bukan public form.
- [x] Status kehadiran memakai istilah sederhana untuk operator.
- [x] PerizinanSantri belum menjadi dependency runtime wajib.
- [x] Integrasi perangkat absensi ditunda.
