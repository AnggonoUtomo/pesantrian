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

- [x] Buat DTO/read model presensi.
- [x] Buat query list/search/filter.
- [x] Buat query detail.
- [x] Buat controller/resource API read.
- [x] Jalankan focused API tests.

Acceptance:

- List presensi mendukung filter tanggal, konteks, status, dan pagination.
- Detail presensi mengembalikan summary dan entries.
- Actor tanpa permission view ditolak.

Hasil:

- Read repository `StudentAttendanceReadRepository` dibuat sebagai contract
  Application dan di-bind ke adapter Eloquent.
- Query `ListStudentAttendances` dan `ShowStudentAttendance` dibuat untuk
  membaca list/detail presensi.
- DTO read model dibuat untuk sesi, entry, summary, filter, dan pagination.
- Endpoint API read ditambahkan:
  - `GET /api/v1/pesantrian/student-attendances`
  - `GET /api/v1/pesantrian/student-attendances/{attendance}`
- Request validation list mendukung search, filter tanggal, context, status,
  pagination, dan sort.
- Resource API memakai envelope canonical dan summary status kehadiran.

Verifikasi:

- [x] `php artisan test tests/Feature/PresensiSantriApiTest.php --no-ansi`

## Increment 6: Create/Update Draft dan Entry

- [x] Buat request validation create/update session.
- [x] Buat action create/update draft.
- [x] Buat action update entries.
- [x] Tambahkan audit create/update.
- [x] Jalankan focused mutation tests.

Acceptance:

- Sesi draft bisa dibuat dan diubah.
- Entry presensi bisa diisi.
- Sesi submitted/void tidak bisa diedit langsung.

Hasil:

- Endpoint mutation draft ditambahkan:
  - `POST /api/v1/pesantrian/student-attendances`
  - `PATCH /api/v1/pesantrian/student-attendances/{attendance}`
  - `PATCH /api/v1/pesantrian/student-attendances/{attendance}/entries`
- Mutation memakai permission `presensi_santri.manage` dan middleware
  idempotency.
- Entry presensi mengambil snapshot NIS/nama dari contract `ActiveStudentReader`
  module Santri, bukan dari payload frontend.
- Status `late` wajib memiliki `minutes_late` lebih dari 0.
- Sesi `submitted` dan `void` ditolak dari update langsung sampai jalur
  lifecycle/revisi yang sesuai dipakai.
- Audit create/update dicatat sebagai aktivitas module `PresensiSantri`.

Verifikasi:

- [x] `php artisan test tests/Feature/PresensiSantriMutationApiTest.php --no-ansi`

## Increment 7: Submit, Revisi, dan Void

- [x] Buat action submit.
- [x] Buat action revise.
- [x] Buat action void.
- [x] Tambahkan audit lifecycle.
- [x] Jalankan focused lifecycle tests.

Acceptance:

- Submit mengunci sesi draft.
- Revisi wajib alasan.
- Void wajib alasan dan tidak menghapus data.

Hasil:

- Endpoint lifecycle ditambahkan:
  - `PATCH /api/v1/pesantrian/student-attendances/{attendance}/submit`
  - `PATCH /api/v1/pesantrian/student-attendances/{attendance}/revise`
  - `PATCH /api/v1/pesantrian/student-attendances/{attendance}/void`
- Submit hanya menerima sesi `draft` dan mengisi `submitted_at` serta
  `submitted_by`.
- Revisi wajib alasan minimal dan mengubah sesi `submitted/revised` menjadi
  `revised`; setelah itu koreksi entry/session boleh dilakukan lewat endpoint
  update yang sudah ada.
- Void wajib alasan, mengubah status menjadi `void`, menyimpan actor/alasan,
  dan tidak menghapus entry.
- Audit lifecycle dicatat untuk submitted, revised, dan voided.

Verifikasi:

- [x] `php artisan test tests/Feature/PresensiSantriLifecycleApiTest.php --no-ansi`

## Increment 8: Demo Seeder

- [x] Buat `PresensiSantriDemoSeeder`.
- [x] Update `DatabaseSeeder`.
- [x] Tambahkan test idempotent seeder.
- [x] Dokumentasikan data demo.

Acceptance:

- Seeder mencakup sesi kelas, asrama, dan kegiatan umum.
- Seeder mencakup variasi status hadir/izin/sakit/alfa/terlambat.
- Seeder aman diulang dan tidak berjalan pada environment production.

Hasil:

- `PresensiSantriDemoSeeder` dipanggil dari global `DatabaseSeeder` setelah
  data demo Santri, Kelas/Rombel, dan Asrama tersedia.
- Seeder membuat sesi demo:
  - `DEMO-KBM-PAGI`: presensi kelas submitted.
  - `DEMO-ASRAMA-MALAM`: presensi asrama draft.
  - `DEMO-MUHADHARAH`: presensi kegiatan umum revised beserta revision record.
  - `DEMO-VOID`: sesi kegiatan umum void tanpa menghapus entry.
- Entry demo mencakup status `present`, `late`, `excused`, `sick`, dan
  `absent`.
- Seeder idempotent dan guard production mengikuti pola seeder demo module lain.

Verifikasi:

- [x] `php artisan test tests/Feature/BusinessDemoSeederTest.php --no-ansi`

## Increment 9: UI/Inertia List dan Detail

- [x] Buat page index.
- [x] Buat page detail.
- [x] Buat komponen filter/table/card/summary/pagination.
- [x] Tambahkan sidebar menu namespace Pesantrian.
- [x] Tambahkan presentation/Ziggy tests.
- [x] Jalankan typecheck, lint, dan build.

Acceptance:

- Page tetap tipis.
- UI berada di `resources/js/pages/Pesantrian/PresensiSantri/`.
- Console browser bersih dari route/Ziggy error.

Hasil:

- Route web Inertia `pesantrian.student-attendances.index` dan
  `pesantrian.student-attendances.show` ditambahkan agar tersedia di Ziggy.
- Page `Index` dan `Show` dibuat tipis dan mendelegasikan tampilan ke komponen
  module.
- Komponen list/detail dibuat di
  `resources/js/pages/Pesantrian/PresensiSantri/components/`, mencakup
  dashboard, filter, table responsif, summary cards, pagination, status badge,
  empty state, access denied, dan detail panel.
- Sidebar namespace Pesantrian menampilkan menu `Presensi Santri` untuk actor
  yang memiliki permission presensi.
- UI Increment 9 masih read-only; dialog create/update, editor entry,
  submit/revisi/void masuk Increment 10.

Verifikasi:

- [x] `php artisan test tests/Feature/PresensiSantriPresentationTest.php --no-ansi`
- [x] `php artisan route:list --name=pesantrian.student-attendances --no-ansi`
- [x] `npm run types:check`
- [x] `npm run build`

## Increment 10: UI Mutation

- [x] Buat dialog create/update session.
- [x] Buat editor entry presensi.
- [x] Buat confirmation submit.
- [x] Buat dialog revisi.
- [x] Buat confirmation void.
- [x] Jalankan typecheck, lint, dan build.

Acceptance:

- Form mutation berada di folder `components`.
- Submit/revisi/void memakai confirmation.
- Backend tetap authority permission dan validasi.

Hasil:

- Route web mutation ditambahkan:
  - `POST /pesantrian/student-attendances`
  - `PATCH /pesantrian/student-attendances/{attendance}`
  - `PATCH /pesantrian/student-attendances/{attendance}/entries`
  - `PATCH /pesantrian/student-attendances/{attendance}/submit`
  - `PATCH /pesantrian/student-attendances/{attendance}/revise`
  - `PATCH /pesantrian/student-attendances/{attendance}/void`
- Controller web memakai action Application yang sama dengan API, sehingga
  validasi, permission, audit, dan rule lifecycle tetap diputuskan backend.
- Dialog `Tambah/Edit sesi presensi` dibuat untuk metadata sesi dan entry awal.
- `Editor entry presensi` dibuat di halaman detail untuk mengubah status
  hadir/terlambat/izin/sakit/alfa saat sesi masih `draft` atau `revised`.
- Confirmation submit, dialog revisi dengan alasan, dan confirmation void
  dengan alasan dibuat di folder komponen module.

Verifikasi:

- [x] `php artisan test tests/Feature/PresensiSantriPresentationTest.php --no-ansi`
- [x] `php artisan test tests/Feature/PresensiSantriPresentationTest.php tests/Feature/PresensiSantriLifecycleApiTest.php tests/Feature/PresensiSantriMutationApiTest.php tests/Feature/PresensiSantriApiTest.php tests/Feature/PresensiSantriDataFoundationTest.php tests/Feature/PresensiSantriContractReadinessTest.php --no-ansi`
- [x] `npm run types:check`
- [x] `npm run lint:check`
- [x] `npm run build`

## Increment 11: QA Browser dan User Manual

- [x] Jalankan browser QA desktop.
- [x] Jalankan browser QA mobile/responsive.
- [x] Update user manual lifecycle.
- [x] Update README/tasks hasil final.

Acceptance:

- Flow list/detail/mutation utama bisa diuji manual.
- Relasi ke module yang belum ada diberi keterangan.
- Console browser bersih dari error.

Hasil:

- Browser QA PresensiSantri ditambahkan untuk desktop dan mobile/responsive.
- Fixture browser membuat operator sementara dengan permission presensi dan dua
  santri aktif sementara tanpa menyimpan credential tetap di source.
- Flow yang diuji: login, buka list, create sesi draft, tambah entry,
  buka detail, edit metadata sesi, edit entry, submit, buka revisi, void, filter
  list, pagination, dan accessibility check dasar.
- `docs/USER-MANUAL-LIFECYCLE.md` diperbarui agar Presensi Santri masuk urutan
  uji manual setelah Asrama.
- README module diperbarui dari status backend/read-only menjadi baseline
  lifecycle UI ready.
- Relasi otomatis ke `PerizinanSantri`, `Kesehatan/Klinik`, dan
  `Pelanggaran/Kedisiplinan` tetap diberi keterangan sebagai belum tersedia
  karena module terkait belum dibuat.

Verifikasi:

- [x] `php artisan migrate --no-ansi`
- [x] `npx playwright test tests/Browser/presensi-santri.spec.ts`
- [x] `php artisan test tests/Feature/PresensiSantriPresentationTest.php --no-ansi`
- [x] `php artisan module:validate --no-ansi`
- [x] `npm run types:check`
- [x] `npm run lint:check`

## Keputusan Baseline

- [x] Module teknis memakai `Pesantrian/PresensiSantri`.
- [x] Nama tampil memakai `Presensi Santri`.
- [x] Presensi awal memakai admin/internal, bukan public form.
- [x] Status kehadiran memakai istilah sederhana untuk operator.
- [x] PerizinanSantri belum menjadi dependency runtime wajib pada baseline
  penutupan PresensiSantri; setelah PerizinanSantri Increment 13, integrasi
  read-only tersedia melalui public contract.
- [x] Integrasi perangkat absensi ditunda.

Catatan setelah PerizinanSantri Increment 13:

- [x] PresensiSantri sekarang memiliki consumer read-only ke public contract
  PerizinanSantri untuk membaca izin yang relevan pada tanggal presensi.
- [x] Integrasi ini tidak mengubah entry presensi otomatis; fallback manual
  tetap berlaku.
