# Tasks: Pesantrian/PerizinanSantri

## Increment 1: Documentation Baseline

- [x] Buat folder dokumentasi module.
- [x] Buat README module.
- [x] Buat specification module.
- [x] Buat implementation plan.
- [x] Buat task roadmap.
- [x] Update indeks module.

Hasil:

- Boundary PerizinanSantri ditetapkan sebagai permohonan izin, approval,
  check-out, return/check-in, void, dan histori izin.
- Nama tampil ditetapkan `Perizinan Santri`.
- Baseline awal memakai admin/internal, bukan public form wali/santri.
- Integrasi otomatis ke PresensiSantri, KesehatanSantri, KedisiplinanSantri,
  Notification, Document, dan Finance ditunda sampai module/data terkait siap.
- Roadmap incremental dibuat dari skeleton sampai QA browser dan integrasi awal
  PresensiSantri.

Verifikasi:

- [x] Review dokumen terhadap `docs/ARCHITECTURE.md`.
- [x] Review dokumen terhadap `docs/FOLDER-STRUCTURE.md`.

## Increment 2: Module Skeleton dan Permission

- [x] Jalankan dry-run generator module.
- [x] Buat module `Pesantrian/PerizinanSantri`.
- [x] Tambahkan permission identity.
- [x] Wire permission ke seeder AccessControl.
- [x] Jalankan module validate.

Hasil:

- Module `Pesantrian/PerizinanSantri` dibuat memakai generator
  `default-v1`.
- Permission identity awal tersedia untuk lihat, kelola, approve/reject,
  check-out, return/check-in, dan archive.
- Seeder AccessControl membaca permission dari module registry dan memberi
  akses operasional Perizinan Santri ke `OperatorSantri`; `OperatorAkademik`,
  `Auditor`, dan `Viewer` mendapat akses lihat.
- `perizinan_santri.archive` tetap permission sensitif dan belum diberikan ke
  role operator demo.
- Belum ada table bisnis atau migration pada increment ini.

Verifikasi:

- [x] `php artisan module:make Pesantrian PerizinanSantri --dry-run --json --no-ansi`
- [x] `php artisan module:make Pesantrian PerizinanSantri --force --yes --no-ansi`
- [x] `php artisan module:validate --no-ansi`
- [x] `php artisan module:list --json --no-ansi`
- [x] `php artisan test tests/Unit/PerizinanSantriPermissionIdentityTest.php --no-ansi`
- [x] `php artisan test tests/Feature/AccessControlSeederTest.php --no-ansi`

Acceptance:

- [x] `php artisan module:validate --no-ansi` berhasil.
- [x] Permission `perizinan_santri.view`, `perizinan_santri.manage`,
  `perizinan_santri.approve`, `perizinan_santri.checkout`,
  `perizinan_santri.return`, dan `perizinan_santri.archive` tersedia.
- [x] Belum ada table bisnis.

## Increment 3: Contract Readiness

- [x] Audit contract santri aktif.
- [x] Audit contract petugas/pegawai aktif.
- [x] Tentukan strategi snapshot wali baseline.
- [x] Tambahkan contract hanya jika consumer nyata belum tersedia.
- [x] Tambahkan tests readiness lintas module.

Hasil:

- Kandidat santri aktif memakai public contract `ActiveStudentReader` dari
  module Santri.
- Kandidat petugas/approver aktif memakai public contract
  `ActiveEmployeeReader` dari module HumanResource.
- Snapshot wali baseline memakai contract baru `PrimaryStudentGuardianReader`
  pada module Santri karena PerizinanSantri perlu `guardian_name`,
  `guardian_phone`, dan `guardian_relation` tanpa membaca model Infrastructure
  Santri langsung.
- Snapshot wali tetap minimum dan tidak memaksa module master `WaliSantri`.
- PerizinanSantri belum memiliki import model Infrastructure lintas module.

Verifikasi:

- [x] `php artisan test tests/Feature/PerizinanSantriContractReadinessTest.php --no-ansi`
- [x] `php artisan module:validate --no-ansi`

Acceptance:

- [x] PerizinanSantri tidak membaca model Infrastructure module lain.
- [x] Candidate santri dan petugas bisa diambil melalui public contract.
- [x] Snapshot wali tidak memaksa pembuatan module WaliSantri master.

## Increment 4: Data Foundation

- [ ] Buat migration `student_permits`.
- [ ] Buat migration `student_permit_revisions`.
- [ ] Buat record model.
- [ ] Buat factory minimum.
- [ ] Jalankan focused data foundation tests.

Acceptance:

- Table memakai ULID.
- Nomor izin unique.
- Izin aktif tidak overlap untuk santri yang sama pada rentang waktu sama.
- Nama index eksplisit aman untuk MySQL.

## Increment 5: Backend Read/List

- [ ] Buat DTO/read model perizinan.
- [ ] Buat query list/search/filter.
- [ ] Buat query detail.
- [ ] Buat controller/resource API read.
- [ ] Jalankan focused API tests.

Acceptance:

- List perizinan mendukung filter tanggal, jenis izin, status, santri,
  keterlambatan, dan pagination.
- Detail perizinan mengembalikan lifecycle, snapshot santri/wali, approval,
  check-out, return, dan revisions.
- Actor tanpa permission view ditolak.

## Increment 6: Create/Update Draft dan Submit

- [ ] Buat request validation create/update permit.
- [ ] Buat action create/update draft.
- [ ] Buat action submit permit.
- [ ] Tambahkan audit create/update/submit.
- [ ] Jalankan focused mutation tests.

Acceptance:

- Permohonan izin bisa dibuat sebagai draft.
- Draft bisa diubah sebelum submit.
- Submit hanya dari draft.
- Rentang waktu valid dan tidak overlap untuk izin aktif santri.

## Increment 7: Approve dan Reject

- [ ] Buat action approve.
- [ ] Buat action reject.
- [ ] Tambahkan request validation decision note/reason.
- [ ] Tambahkan audit decision.
- [ ] Jalankan focused lifecycle tests.

Acceptance:

- Approve/reject hanya dari submitted.
- Reject wajib alasan.
- Decision menyimpan actor, waktu, dan catatan.

## Increment 8: Check-out, Return, dan Void

- [ ] Buat action check-out.
- [ ] Buat action return/check-in.
- [ ] Buat action void.
- [ ] Tambahkan revision history.
- [ ] Tambahkan audit lifecycle.
- [ ] Jalankan focused lifecycle tests.

Acceptance:

- Check-out hanya dari approved.
- Return hanya dari checked_out.
- Return terlambat terbaca pada read model.
- Void wajib alasan dan tidak menghapus data.

## Increment 9: Demo Seeder

- [ ] Buat `PerizinanSantriDemoSeeder`.
- [ ] Update `DatabaseSeeder`.
- [ ] Tambahkan test idempotent seeder.
- [ ] Dokumentasikan data demo.

Acceptance:

- Seeder mencakup draft/submitted/approved/rejected/checked_out/returned/void.
- Seeder memakai santri dan petugas demo yang sudah ada.
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
- UI berada di `resources/js/pages/Pesantrian/PerizinanSantri/`.
- Console browser bersih dari route/Ziggy error.

## Increment 11: UI Mutation

- [ ] Buat dialog create/update permit.
- [ ] Buat confirmation submit.
- [ ] Buat dialog approve/reject.
- [ ] Buat confirmation check-out.
- [ ] Buat dialog return/check-in.
- [ ] Buat confirmation void.
- [ ] Jalankan typecheck, lint, dan build.

Acceptance:

- Form mutation berada di folder `components`.
- Submit/approve/reject/check-out/return/void memakai confirmation atau alasan.
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

## Increment 13: Integrasi Awal ke PresensiSantri

- [ ] Buat public contract read-only izin approved.
- [ ] Buat DTO status izin santri per tanggal.
- [ ] Tambahkan test consumer readiness.
- [ ] Dokumentasikan batas integrasi.

Acceptance:

- PresensiSantri bisa membaca izin approved tanpa coupling ke Infrastructure.
- Izin tidak otomatis mengubah presensi tanpa keputusan eksplisit.
- Fallback manual PresensiSantri tetap tersedia.

## Keputusan Baseline

- [x] Module teknis memakai `Pesantrian/PerizinanSantri`.
- [x] Nama tampil memakai `Perizinan Santri`.
- [x] Perizinan awal memakai admin/internal, bukan public form.
- [x] Snapshot wali dipakai dulu; master WaliSantri tidak dipaksa.
- [x] Approval baseline satu level dulu.
- [x] Integrasi otomatis Presensi/Kesehatan/Kedisiplinan ditunda.
