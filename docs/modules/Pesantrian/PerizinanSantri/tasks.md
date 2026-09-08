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

- [x] Buat migration `student_permits`.
- [x] Buat migration `student_permit_revisions`.
- [x] Buat record model.
- [x] Buat factory minimum.
- [x] Jalankan focused data foundation tests.

Hasil:

- Table `student_permits` dibuat untuk nomor izin, snapshot santri/wali,
  jenis izin, rentang waktu, status lifecycle, actor lifecycle, dan catatan.
- Table `student_permit_revisions` dibuat untuk histori perubahan izin.
- Record model `StudentPermitRecord` dan `StudentPermitRevisionRecord` dibuat
  dengan ULID, relation revisions/permit, dan casts datetime/json.
- Factory minimum dibuat untuk kebutuhan test dan demo seeder berikutnya.
- `ServiceProvider` PerizinanSantri sekarang memuat migration dan route module.
- Unique nomor izin dijaga di database.
- Rule overlap aktif disiapkan lewat index
  `sp_student_status_range_idx`; enforcement final rentang tidak overlap tetap
  dikerjakan pada Application action Increment 6 karena database baseline
  MySQL/SQLite tidak punya exclusion constraint portable.

Verifikasi:

- [x] `php artisan test tests/Feature/PerizinanSantriDataFoundationTest.php --no-ansi`
- [x] `php artisan module:validate --no-ansi`

Acceptance:

- [x] Table memakai ULID.
- [x] Nomor izin unique.
- [x] Index pendukung rule overlap izin aktif tersedia; enforcement final
  dikerjakan di Increment 6.
- [x] Nama index eksplisit aman untuk MySQL.

## Increment 5: Backend Read/List

- [x] Buat DTO/read model perizinan.
- [x] Buat query list/search/filter.
- [x] Buat query detail.
- [x] Buat controller/resource API read.
- [x] Jalankan focused API tests.

Hasil:

- Read model `StudentPermitData`, `StudentPermitSummaryData`,
  `StudentPermitRevisionData`, dan pagination DTO tersedia.
- Query `ListStudentPermits` dan `ShowStudentPermit` memakai contract
  `StudentPermitReadRepository`.
- Repository Eloquent read-only mendukung search, filter tanggal, jenis izin,
  status, santri, keterlambatan, pagination, dan sort allowlist.
- API internal tersedia:
  - `GET /api/v1/pesantrian/student-permits`
  - `GET /api/v1/pesantrian/student-permits/{permit}`
- Response detail mengembalikan snapshot santri/wali, lifecycle actor/waktu,
  summary, dan revision history.
- Actor tanpa `perizinan_santri.view` ditolak oleh middleware backend.

Verifikasi:

- [x] `php artisan test tests/Feature/PerizinanSantriApiTest.php --no-ansi`
- [x] `php artisan route:list --name=api.v1.pesantrian.student-permits --no-ansi`

Acceptance:

- [x] List perizinan mendukung filter tanggal, jenis izin, status, santri,
  keterlambatan, dan pagination.
- [x] Detail perizinan mengembalikan lifecycle, snapshot santri/wali, approval,
  check-out, return, dan revisions.
- [x] Actor tanpa permission view ditolak.

## Increment 6: Create/Update Draft dan Submit

- [x] Buat request validation create/update permit.
- [x] Buat action create/update draft.
- [x] Buat action submit permit.
- [x] Tambahkan audit create/update/submit.
- [x] Jalankan focused mutation tests.

Hasil:

- API mutation internal tersedia:
  - `POST /api/v1/pesantrian/student-permits`
  - `PATCH /api/v1/pesantrian/student-permits/{permit}`
  - `PATCH /api/v1/pesantrian/student-permits/{permit}/submit`
- Create draft mengambil snapshot santri aktif dan wali utama melalui public
  contract module Santri.
- Update hanya boleh untuk status `draft` dan mencatat revision dengan alasan
  koreksi.
- Submit hanya boleh dari `draft` untuk santri yang masih aktif, menyimpan
  actor/waktu submit, mencatat revision, dan menolak overlap dengan izin aktif
  santri yang sama.
- Audit create/update/submit dicatat melalui event `SystemActivityOccurred`
  dengan module `PerizinanSantri`.

Verifikasi:

- [x] `php artisan test tests/Feature/PerizinanSantriMutationApiTest.php --no-ansi`
- [x] `php artisan test tests/Feature/PerizinanSantriMutationApiTest.php tests/Feature/PerizinanSantriApiTest.php tests/Feature/ApiRouteMatrixTest.php --no-ansi`
- [x] `php artisan module:validate --no-ansi`

Acceptance:

- [x] Permohonan izin bisa dibuat sebagai draft.
- [x] Draft bisa diubah sebelum submit.
- [x] Submit hanya dari draft.
- [x] Rentang waktu valid dan tidak overlap untuk izin aktif santri.

## Increment 7: Approve dan Reject

- [x] Buat action approve.
- [x] Buat action reject.
- [x] Tambahkan request validation decision note/reason.
- [x] Tambahkan audit decision.
- [x] Jalankan focused lifecycle tests.

Hasil:

- API decision internal tersedia:
  - `PATCH /api/v1/pesantrian/student-permits/{permit}/approve`
  - `PATCH /api/v1/pesantrian/student-permits/{permit}/reject`
- Approve hanya boleh dari status `submitted`, mengubah status menjadi
  `approved`, menyimpan `reviewed_at`, `reviewed_by`, dan catatan review
  opsional.
- Reject hanya boleh dari status `submitted`, mengubah status menjadi
  `rejected`, menyimpan `reviewed_at`, `reviewed_by`, dan alasan penolakan.
- Reject mewajibkan `reason` minimal 3 karakter.
- Decision approve/reject mencatat revision history dan audit
  `perizinan_santri.permit.approved` atau
  `perizinan_santri.permit.rejected`.

Verifikasi:

- [x] `php artisan test tests/Feature/PerizinanSantriDecisionApiTest.php --no-ansi`
- [x] `php artisan test tests/Feature/PerizinanSantriDecisionApiTest.php tests/Feature/PerizinanSantriMutationApiTest.php tests/Feature/PerizinanSantriApiTest.php tests/Feature/ApiRouteMatrixTest.php --no-ansi`
- [x] `php artisan module:validate --no-ansi`

Acceptance:

- [x] Approve/reject hanya dari submitted.
- [x] Reject wajib alasan.
- [x] Decision menyimpan actor, waktu, dan catatan.

## Increment 8: Check-out, Return, dan Void

- [x] Buat action check-out.
- [x] Buat action return/check-in.
- [x] Buat action void.
- [x] Tambahkan revision history.
- [x] Tambahkan audit lifecycle.
- [x] Jalankan focused lifecycle tests.

Hasil:

- API lifecycle operasional internal tersedia:
  - `PATCH /api/v1/pesantrian/student-permits/{permit}/checkout`
  - `PATCH /api/v1/pesantrian/student-permits/{permit}/return`
  - `PATCH /api/v1/pesantrian/student-permits/{permit}/void`
- Check-out hanya boleh dari status `approved`, menyimpan `checked_out_at`,
  `checked_out_by`, revision, dan audit `perizinan_santri.permit.checked_out`.
- Return/check-in hanya boleh dari status `checked_out`, menyimpan
  `returned_at`, `returned_by`, catatan kembali opsional, revision, audit
  `perizinan_santri.permit.returned`, dan summary keterlambatan tetap terbaca
  dari read model.
- Void hanya boleh untuk izin non-final, wajib alasan, menyimpan `voided_at`,
  `voided_by`, `void_reason`, revision, dan audit
  `perizinan_santri.permit.voided` tanpa menghapus data.

Verifikasi:

- [x] `php artisan test tests/Feature/PerizinanSantriOperationalLifecycleApiTest.php --no-ansi`

Acceptance:

- [x] Check-out hanya dari approved.
- [x] Return hanya dari checked_out.
- [x] Return terlambat terbaca pada read model.
- [x] Void wajib alasan dan tidak menghapus data.

## Increment 9: Demo Seeder

- [x] Buat `PerizinanSantriDemoSeeder`.
- [x] Update `DatabaseSeeder`.
- [x] Tambahkan test idempotent seeder.
- [x] Dokumentasikan data demo.

Hasil:

- `PerizinanSantriDemoSeeder` dibuat di module PerizinanSantri.
- Seeder dipanggil dari `DatabaseSeeder` setelah data demo Santri,
  PresensiSantri, dan Tahfidz tersedia.
- Seeder memakai data demo `NIS-DEMO-AKTIF` dan `NIS-DEMO-PPDB`, snapshot wali
  utama dari Santri, serta actor user demo AccessControl.
- Data demo dibuat idempotent memakai nomor izin `IZN-DEMO-*`.
- Seeder tidak berjalan pada environment `production`.
- Revision history demo dibuat untuk draft, submit, review approve/reject,
  check-out, return terlambat, dan void.

Verifikasi:

- [x] `php artisan test tests/Feature/BusinessDemoSeederTest.php --no-ansi`

Acceptance:

- [x] Seeder mencakup draft/submitted/approved/rejected/checked_out/returned/void.
- [x] Seeder memakai santri dan petugas demo yang sudah ada.
- [x] Seeder aman diulang dan tidak berjalan pada environment production.

## Increment 10: UI/Inertia List dan Detail

- [x] Buat page index.
- [x] Buat page detail.
- [x] Buat komponen filter/table/card/summary/pagination.
- [x] Tambahkan sidebar menu namespace Pesantrian.
- [x] Tambahkan presentation/Ziggy tests.
- [x] Jalankan typecheck, lint, dan build.

Hasil:

- Web Inertia route tersedia:
  - `GET /pesantrian/student-permits`
  - `GET /pesantrian/student-permits/{permit}`
- Page frontend tersedia di
  `resources/js/pages/Pesantrian/PerizinanSantri/pages/Index.tsx` dan
  `resources/js/pages/Pesantrian/PerizinanSantri/pages/Show.tsx`.
- Komponen business-specific ditempatkan di
  `resources/js/pages/Pesantrian/PerizinanSantri/components/`.
- UI list mendukung filter search, tanggal, jenis izin, status,
  keterlambatan, pagination, summary card, empty state, dan table/card
  responsive.
- UI detail menampilkan informasi izin, snapshot santri/wali, lifecycle waktu,
  status, keterlambatan, dan histori revisi.
- Menu sidebar namespace Pesantrian menampilkan `Perizinan Santri` untuk actor
  dengan permission terkait.
- Route web PerizinanSantri masuk whitelist Ziggy.
- Mutation UI belum dibuat; dialog create/update/submit/approve/reject/
  check-out/return/void masuk Increment 11.

Verifikasi:

- [x] `php artisan test tests/Feature/PerizinanSantriPresentationTest.php tests/Feature/NavigationSidebarTest.php tests/Feature/ZiggyRouteTest.php --no-ansi`
- [x] `npm run types:check`
- [x] `npm run lint:check`
- [x] `npm run build`

Acceptance:

- [x] Page tetap tipis.
- [x] UI berada di `resources/js/pages/Pesantrian/PerizinanSantri/`.
- [x] Console browser bersih dari route/Ziggy error.

## Increment 11: UI Mutation

- [x] Buat dialog create/update permit.
- [x] Buat confirmation submit.
- [x] Buat dialog approve/reject.
- [x] Buat confirmation check-out.
- [x] Buat dialog return/check-in.
- [x] Buat confirmation void.
- [x] Jalankan typecheck, lint, dan build.

Hasil:

- Route web mutation tersedia untuk create draft, update draft, submit,
  approve, reject, check-out, return/check-in, dan void.
- Page Index dan Show tetap tipis; state dialog mutation berada di page,
  sedangkan isi form/action berada di component folder.
- `PerizinanSantriMutationDialog` menangani create/update draft dengan pilihan
  santri aktif, jenis izin, rentang waktu, tujuan, alasan izin, dan alasan
  koreksi saat edit.
- `PerizinanSantriLifecycleDialogs` menangani confirmation submit, approve
  dengan catatan review opsional, reject dengan alasan wajib, check-out,
  return/check-in dengan waktu kembali dan catatan, serta void dengan alasan
  wajib.
- Tombol aksi lifecycle muncul berdasarkan permission dan status izin, tetapi
  backend tetap authority final untuk permission, validasi, dan rule status.
- Route mutation PerizinanSantri masuk whitelist Ziggy supaya UI production
  tidak gagal karena route name hilang.

Verifikasi:

- [x] `php -l app/Modules/Pesantrian/PerizinanSantri/Presentation/Controllers/StudentPermitController.php`
- [x] `php -l app/Modules/Pesantrian/PerizinanSantri/Routes/web.php`
- [x] `php artisan test tests/Feature/PerizinanSantriPresentationTest.php tests/Feature/PerizinanSantriOperationalLifecycleApiTest.php tests/Feature/PerizinanSantriDecisionApiTest.php tests/Feature/PerizinanSantriMutationApiTest.php tests/Feature/NavigationSidebarTest.php tests/Feature/ZiggyRouteTest.php --no-ansi`
- [x] `php artisan module:validate --no-ansi`
- [x] `php artisan route:list --name=pesantrian.student-permits --no-ansi`
- [x] `npm run types:check`
- [x] `npm run lint:check`
- [x] `npm run build`

Acceptance:

- [x] Form mutation berada di folder `components`.
- [x] Submit/approve/reject/check-out/return/void memakai confirmation atau alasan.
- [x] Backend tetap authority permission dan validasi.

## Increment 12: QA Browser dan User Manual

- [x] Jalankan browser QA desktop.
- [x] Jalankan browser QA mobile/responsive.
- [x] Update user manual lifecycle.
- [x] Update README/tasks hasil final.

Hasil:

- Browser QA otomatis ditambahkan untuk flow utama Perizinan Santri.
- Fixture Playwright membuat user operator, permission, unit, santri aktif, dan
  wali utama khusus test, lalu membersihkannya setelah test selesai.
- QA desktop dan mobile memverifikasi login, buka halaman Perizinan Santri,
  create draft, edit draft, submit, approve, check-out, return/check-in,
  reject, void, dan accessibility high-impact check.
- Test menangkap console error, page error, serta response 403 yang tidak
  diharapkan.
- User manual lifecycle SakaSantri diperbarui dengan langkah Perizinan Santri
  dan keterangan relasi yang belum otomatis terhubung.

Verifikasi:

- [x] `npm run types:check`
- [x] `npm run lint:check`
- [x] `E2E_START_SERVER=true npx playwright test tests/Browser/perizinan-santri.spec.ts`

Acceptance:

- [x] Flow list/detail/mutation utama bisa diuji manual.
- [x] Relasi ke module yang belum ada diberi keterangan.
- [x] Console browser bersih dari error.

## Increment 13: Integrasi Awal ke PresensiSantri

- [x] Buat public contract read-only izin approved.
- [x] Buat DTO status izin santri per tanggal.
- [x] Tambahkan test consumer readiness.
- [x] Dokumentasikan batas integrasi.

Hasil:

- Public contract `ApprovedStudentPermitReader` tersedia di Application layer
  PerizinanSantri untuk membaca izin yang relevan pada tanggal presensi.
- DTO `StudentPermitAttendanceStatusData` membawa status izin santri per
  tanggal tanpa mengekspos model Infrastructure PerizinanSantri.
- Adapter Eloquent PerizinanSantri membaca status `approved`, `checked_out`,
  dan `returned` yang rentang waktunya memotong tanggal presensi.
- PresensiSantri memiliki query kecil `FindApprovedPermitForAttendance` sebagai
  consumer nyata melalui public contract, bukan melalui model Eloquent module
  lain.
- Integrasi ini bersifat read-only. UI/flow PresensiSantri belum otomatis
  mengubah entry presensi menjadi izin; operator tetap bisa memilih status
  manual.

Verifikasi:

- [x] `php artisan test tests/Feature/PresensiSantriContractReadinessTest.php --no-ansi`
- [x] `php artisan test tests/Feature/PresensiSantriContractReadinessTest.php tests/Feature/PerizinanSantriContractReadinessTest.php --no-ansi`
- [x] `php artisan test --filter=PerizinanSantri --no-ansi`
- [x] `php artisan test --filter=PresensiSantri --no-ansi`
- [x] `php artisan module:validate --no-ansi`
- [x] `vendor\bin\pint --dirty --test`

Acceptance:

- [x] PresensiSantri bisa membaca izin approved tanpa coupling ke Infrastructure.
- [x] Izin tidak otomatis mengubah presensi tanpa keputusan eksplisit.
- [x] Fallback manual PresensiSantri tetap tersedia.

## Keputusan Baseline

- [x] Module teknis memakai `Pesantrian/PerizinanSantri`.
- [x] Nama tampil memakai `Perizinan Santri`.
- [x] Perizinan awal memakai admin/internal, bukan public form.
- [x] Snapshot wali dipakai dulu; master WaliSantri tidak dipaksa.
- [x] Approval baseline satu level dulu.
- [x] Integrasi read-only awal ke PresensiSantri tersedia; auto-fill Presensi,
  Kesehatan/Kedisiplinan, tagihan/denda, dan notifikasi tetap ditunda.
