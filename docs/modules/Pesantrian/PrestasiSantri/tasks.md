# Tasks: Pesantrian/PrestasiSantri

## Increment 1: Documentation Baseline

- [x] Buat folder dokumentasi module.
- [x] Buat README module.
- [x] Buat specification module.
- [x] Buat implementation plan.
- [x] Buat task roadmap.
- [x] Update indeks module.

Hasil:

- Boundary PrestasiSantri ditetapkan sebagai pencatatan kategori, catatan
  prestasi, tingkat, hasil, submit, verifikasi, revision, dan void.
- Nama tampil ditetapkan `Prestasi` agar familiar untuk operator pesantren.
- Upload bukti/sertifikat, portal wali/santri, publikasi otomatis, reward
  finansial, dan laporan kompleks ditunda.

Verifikasi:

- [x] Review manual dokumen module dan roadmap.

## Increment 2: Module Skeleton dan Permission

- [x] Jalankan dry-run generator module.
- [x] Buat module `Pesantrian/PrestasiSantri`.
- [x] Tambahkan permission identity.
- [x] Wire permission ke seeder AccessControl.
- [x] Jalankan module validate.

Acceptance:

- `php artisan module:validate --no-ansi` berhasil.
- Permission `prestasi_santri.view`, `prestasi_santri.manage`,
  `prestasi_santri.record`, `prestasi_santri.verify`, dan
  `prestasi_santri.archive` tersedia.
- Belum ada table bisnis pada increment ini.

Verifikasi:

- [x] `php artisan module:make Pesantrian PrestasiSantri --dry-run --json --no-ansi`
- [x] `php artisan test tests/Unit/PrestasiSantriPermissionIdentityTest.php tests/Feature/AccessControlSeederTest.php tests/Feature/BusinessDemoSeederTest.php --no-ansi`
- [x] `php artisan module:validate --no-ansi`
- [x] `php artisan module:list --json --no-ansi`

Hasil:

- Skeleton source module dibuat di
  `app/Modules/Pesantrian/PrestasiSantri/`.
- Module terdaftar sebagai `enabled`, bootable, dan terbaca oleh registry
  module.
- Permission baseline dibuat untuk akses lihat, kelola kategori, pencatatan,
  verifikasi, dan arsip/batal data prestasi.
- Permission key memakai format underscore `prestasi_santri.*` agar konsisten
  dengan module dua-kata lain seperti `perizinan_santri.*` dan
  `kedisiplinan_santri.*`.
- Seeder AccessControl memberi akses operasional awal:
  `OperatorSantri` dapat melihat, mengelola, mencatat, dan verifikasi;
  `OperatorAkademik`, `Auditor`, dan `Viewer` dapat melihat.
- Permission `prestasi_santri.archive` sementara hanya melekat ke role
  super/admin keamanan melalui mekanisme all-permission karena pembatalan data
  prestasi termasuk aksi sensitif.

## Increment 3: Contract Readiness

- [x] Audit contract Santri aktif.
- [x] Audit contract pembina/pegawai aktif.
- [x] Audit contract periode akademik aktif.
- [x] Tambahkan contract hanya bila belum tersedia dan memang dibutuhkan.
- [x] Tambahkan tests readiness lintas module.

Acceptance:

- PrestasiSantri tidak membaca model Infrastructure module lain.
- Candidate santri, pembina, dan periode bisa diambil melalui public contract.
- Tidak ada public boundary keluar dari PrestasiSantri tanpa consumer nyata.

Verifikasi:

- [x] `php artisan test tests/Feature/PrestasiSantriContractReadinessTest.php --no-ansi`
- [x] `php artisan module:validate --no-ansi`

Hasil:

- Contract `ActiveStudentReader` dari `Pesantrian/Santri` sudah cukup untuk
  selector dan validasi santri aktif.
- Contract `ActiveEmployeeReader` dari `HumanResource/HumanResource` sudah
  cukup untuk selector pembina/pendamping aktif.
- Contract `ActiveAcademicPeriodReader` dari `Academic/AcademicPeriod` sudah
  cukup untuk konteks tahun ajaran/periode rekap prestasi.
- Tidak ada contract baru yang dibuat karena kebutuhan PrestasiSantri awal
  sudah terlayani oleh public contract existing.
- Guardrail readiness ditambahkan agar PrestasiSantri tidak mengambil model
  Infrastructure module dependency secara langsung.

## Increment 4: Data Foundation

- [x] Buat migration kategori prestasi.
- [x] Buat migration catatan prestasi.
- [x] Buat migration revision prestasi.
- [x] Buat record model.
- [x] Buat factory minimum.
- [x] Jalankan focused data foundation tests.

Acceptance:

- Table memakai ULID.
- Kode kategori unique.
- Nomor prestasi unique.
- Catatan prestasi menyimpan snapshot santri dan pembina.
- Nama index eksplisit aman untuk MySQL.

Verifikasi:

- [x] `php artisan test tests/Feature/PrestasiSantriDataFoundationTest.php --no-ansi`
- [x] `php artisan module:validate --no-ansi`

Hasil:

- Migration `2026_09_17_000000_create_prestasi_santri_tables.php` membuat
  table `student_achievement_categories`, `student_achievements`, dan
  `student_achievement_revisions`.
- ServiceProvider PrestasiSantri memuat migration module dari folder
  `Database/Migrations`.
- Record model Infrastructure dibuat untuk kategori prestasi, catatan prestasi,
  dan histori revisi.
- Factory minimum dibuat untuk semua record model agar seeder/demo/test
  berikutnya lebih mudah disusun.
- Catatan prestasi menyimpan snapshot kategori, santri, periode akademik, dan
  pembina agar riwayat tetap terbaca walaupun master data berubah.
- Nama index revision memakai prefix `psar_*` agar tidak bentrok dengan index
  revision PresensiSantri pada SQLite.

## Increment 5: Backend Read/List

- [x] Buat DTO/read model PrestasiSantri.
- [x] Buat query list/search/filter.
- [x] Buat query detail.
- [x] Buat controller/resource API read.
- [x] Jalankan focused API tests.

Acceptance:

- List mendukung filter kategori, santri, pembina, periode, tingkat, status,
  rentang tanggal, search, sort, dan pagination.
- Detail menampilkan kategori, snapshot santri, pembina, status verifikasi, dan
  revision history.
- Actor tanpa permission view ditolak.

Verifikasi:

- [x] `php artisan test tests/Feature/PrestasiSantriApiTest.php --no-ansi`
- [x] `php artisan route:list --name=api.v1.pesantrian.prestasi-santri --no-ansi`
- [x] `php artisan module:validate --no-ansi`

Hasil:

- API read memakai prefix route
  `api.v1.pesantrian.prestasi-santri.*`.
- Endpoint kategori tersedia di
  `api.v1.pesantrian.prestasi-santri.categories.index`.
- Endpoint list dan detail catatan prestasi tersedia di
  `api.v1.pesantrian.prestasi-santri.index` dan
  `api.v1.pesantrian.prestasi-santri.show`.
- List mendukung search, filter kategori, santri, pembina, periode akademik,
  tingkat, status, rentang tanggal prestasi, sort, dan pagination.
- Detail menampilkan snapshot kategori, santri, periode akademik, pembina,
  status verifikasi, ringkasan status, dan revision history.
- Permission `prestasi_santri.view` menjadi guard backend untuk list kategori,
  list catatan, dan detail catatan prestasi.

## Increment 6: Kategori dan Catatan Draft

- [x] Buat request validation kategori.
- [x] Buat action create/update/archive kategori.
- [x] Buat request validation catatan draft.
- [x] Buat action create/update catatan draft.
- [x] Tambahkan audit kategori dan draft.
- [x] Jalankan focused mutation tests.

Acceptance:

- Kategori aktif bisa dibuat, diubah, dan diarsip aman.
- Catatan draft bisa dibuat dan diubah.
- Catatan mengambil snapshot santri aktif dan pembina aktif bila dipilih.
- Catatan `verified` dan `void` tidak bisa diedit langsung.

Verifikasi:

- [x] `php artisan test tests/Feature/PrestasiSantriCategoryMutationApiTest.php tests/Feature/PrestasiSantriDraftMutationApiTest.php tests/Feature/PrestasiSantriApiTest.php --no-ansi`

Hasil:

- Kategori prestasi bisa dibuat, diperbarui, dan diarsip melalui API dengan
  idempotency middleware.
- Arsip kategori tidak menghapus catatan prestasi historis yang sudah memakai
  kategori tersebut.
- Draft prestasi bisa dibuat dan diperbarui melalui API.
- Draft prestasi mengambil snapshot santri aktif, kategori aktif, pembina aktif
  bila dipilih, dan periode akademik aktif bila tersedia.
- Draft prestasi mendapat nomor otomatis `PRS-000001` dan seterusnya.
- Create/update draft membuat revision history.
- Create/update/archive kategori dan create/update draft menerbitkan audit
  `PrestasiSantri` ke `System/AuditLog`.
- Catatan berstatus `verified` atau `void` belum bisa diedit langsung.

## Increment 7: Submit dan Verifikasi

- [x] Buat action submit catatan prestasi.
- [x] Buat action verify dengan hasil `verified`.
- [x] Buat action meminta revisi dengan status `needs_revision`.
- [x] Buat revision history.
- [x] Tambahkan audit lifecycle.
- [x] Jalankan focused lifecycle tests.

Acceptance:

- Draft/needs_revision bisa disubmit.
- Submitted bisa diverifikasi atau diminta revisi.
- Verified menjadi riwayat resmi.
- Semua lifecycle meninggalkan revision history dan audit.

Verifikasi:

- [x] `php artisan test tests/Feature/PrestasiSantriLifecycleApiTest.php tests/Feature/PrestasiSantriDraftMutationApiTest.php tests/Feature/PrestasiSantriApiTest.php --no-ansi`

Hasil:

- Draft dan catatan berstatus `needs_revision` bisa disubmit menjadi
  `submitted`.
- Catatan berstatus `submitted` bisa diverifikasi menjadi `verified`.
- Catatan berstatus `submitted` bisa diminta revisi menjadi `needs_revision`
  dengan catatan verifikasi wajib.
- Setiap transisi menulis revision history dengan status awal/akhir,
  changed fields, actor, dan waktu perubahan.
- Setiap transisi menerbitkan audit `PrestasiSantri`:
  `prestasi_santri.achievement.submitted`,
  `prestasi_santri.achievement.verified`, dan
  `prestasi_santri.achievement.revision_requested`.
- Permission backend memakai `prestasi_santri.record` untuk submit dan
  `prestasi_santri.verify` untuk verifikasi/minta revisi.

## Increment 8: Void / Pembatalan Aman

- [x] Buat action void dengan alasan.
- [x] Batasi void memakai permission sensitif.
- [x] Pastikan void tidak menghapus data.
- [x] Tambahkan revision history dan audit.
- [x] Jalankan focused void tests.

Acceptance:

- Void wajib alasan.
- Catatan void tetap tampil di audit/detail bila user punya akses.
- Reason tidak memuat secret/payload sensitif.

Verifikasi:

- [x] `php artisan test tests/Feature/PrestasiSantriVoidApiTest.php tests/Feature/PrestasiSantriLifecycleApiTest.php --no-ansi`

Hasil:

- Endpoint API `api.v1.pesantrian.prestasi-santri.void` tersedia dengan
  middleware idempotency dan permission backend `prestasi_santri.archive`.
- Void memakai alasan wajib `void_reason`, mengubah status ke `void`,
  menyimpan `voided_at`, `voided_by`, dan `void_reason`, serta tidak menghapus
  row `student_achievements`.
- Pembatalan membuat revision history dan audit event
  `prestasi_santri.achievement.voided` tanpa memasukkan `void_reason` ke
  ringkasan metadata result.

## Increment 9: Demo Seeder

- [x] Buat `PrestasiSantriDemoSeeder`.
- [x] Update `DatabaseSeeder`.
- [x] Tambahkan test idempotent seeder.
- [x] Dokumentasikan data demo.

Acceptance:

- Seeder mencakup kategori aktif/arsip.
- Seeder mencakup catatan draft, submitted, verified, needs_revision, dan void.
- Seeder memakai data demo Santri, HumanResource, dan AcademicPeriod yang sudah
  ada.
- Seeder aman diulang dan tidak berjalan pada environment production.

Verifikasi:

- [x] `php artisan test tests/Feature/BusinessDemoSeederTest.php --no-ansi`

Hasil:

- Seeder demo `PrestasiSantriDemoSeeder` dipanggil dari `DatabaseSeeder`
  setelah dependency Santri, AcademicPeriod, HumanResource, Tahfidz,
  PerizinanSantri, dan KedisiplinanSantri tersedia.
- Data demo mencakup kategori aktif `PRS-DEMO-AKD`, `PRS-DEMO-THF`,
  `PRS-DEMO-NONAKD`, kategori arsip `PRS-DEMO-ARSIP`, dan catatan prestasi
  `PRS-DEMO-DRAFT`, `PRS-DEMO-SUBMITTED`, `PRS-DEMO-VERIFIED`,
  `PRS-DEMO-REVISION`, serta `PRS-DEMO-VOID`.
- Seeder aman diulang, tidak berjalan di production, memakai snapshot santri,
  periode akademik, dan pembina demo yang sudah tersedia.
- Role demo `OperatorSantri` diberi `prestasi_santri.archive` agar flow void
  bisa diuji manual oleh operator demo.

## Increment 10: UI/Inertia List dan Detail

- [x] Buat page index.
- [x] Buat page detail.
- [x] Buat komponen filter/table/card/summary/pagination.
- [x] Tambahkan sidebar menu namespace Pesantrian.
- [x] Tambahkan presentation/Ziggy tests.
- [x] Jalankan typecheck, lint, dan build.

Acceptance:

- Page tetap tipis.
- UI berada di `resources/js/pages/Pesantrian/PrestasiSantri/`.
- Komponen berada di `resources/js/pages/Pesantrian/PrestasiSantri/components/`.
- Console browser bersih dari route/Ziggy error.

Verifikasi:

- [x] `php artisan test tests/Feature/PrestasiSantriPresentationTest.php tests/Feature/PrestasiSantriApiTest.php --no-ansi`
- [x] `php artisan route:list --name=pesantrian.prestasi-santri --no-ansi`
- [x] `php artisan module:validate --no-ansi`
- [x] `npm run types:check`
- [x] `npm run lint:check`
- [x] `npm run build`

Hasil:

- Route web `pesantrian.prestasi-santri.index` dan
  `pesantrian.prestasi-santri.show` ditambahkan ke route module dan Ziggy
  allow-list.
- UI list/detail berada di `resources/js/pages/Pesantrian/PrestasiSantri/`
  dengan page tipis dan komponen terpisah di folder `components/`.
- Sidebar namespace Pesantrian menampilkan menu `Prestasi Santri` untuk
  permission `prestasi_santri.*`.
- Live browser console QA tetap dicatat untuk tahap QA UI setelah mutation UI
  selesai; Increment 10 sudah menutup risiko route/Ziggy lewat route-list,
  build, dan presentation test.

## Increment 11: UI Mutation

- [x] Buat dialog create/update kategori.
- [x] Buat dialog create/update catatan prestasi.
- [x] Buat dialog submit.
- [x] Buat dialog verify/needs_revision.
- [x] Buat confirmation void.
- [x] Jalankan typecheck, lint, dan build.

Acceptance:

- Form mutation berada di folder `components`.
- Verify/void memakai confirmation atau dialog alasan.
- Backend tetap authority permission dan validasi.

Verifikasi:

- [x] `php artisan test tests/Feature/PrestasiSantriPresentationTest.php tests/Feature/PrestasiSantriCategoryMutationApiTest.php tests/Feature/PrestasiSantriDraftMutationApiTest.php tests/Feature/PrestasiSantriLifecycleApiTest.php tests/Feature/PrestasiSantriVoidApiTest.php --no-ansi`
- [x] `php artisan route:list --name=pesantrian.prestasi-santri --no-ansi`
- [x] `php artisan module:validate --no-ansi`
- [x] `npm run types:check`
- [x] `npm run lint:check`
- [x] `npm run build`

Hasil:

- Route web mutation PrestasiSantri ditambahkan untuk create/update kategori,
  arsip kategori, create/update draft prestasi, submit, verify, request
  revision, dan void.
- UI mutation berada di folder
  `resources/js/pages/Pesantrian/PrestasiSantri/components/`.
- Dashboard list dan detail sudah memiliki tombol aksi sesuai permission dan
  status lifecycle.
- Backend tetap menjadi authority permission dan validasi melalui FormRequest
  dan Application actions.

## Increment 12: QA Browser dan User Manual

- [x] Jalankan browser QA desktop.
- [x] Jalankan browser QA mobile/responsive.
- [x] Update user manual lifecycle.
- [x] Update README/tasks hasil final.

Acceptance:

- Flow list/detail/mutation utama bisa diuji manual.
- Relasi ke module yang belum ada diberi keterangan.
- Console browser bersih dari error.

Verifikasi:

- [x] `php artisan migrate --no-ansi`
- [x] `php artisan route:list --name=pesantrian.prestasi-santri --no-ansi`
- [x] `php artisan test tests\Feature\PrestasiSantriPresentationTest.php tests\Feature\PrestasiSantriCategoryMutationApiTest.php tests\Feature\PrestasiSantriDraftMutationApiTest.php tests\Feature\PrestasiSantriLifecycleApiTest.php tests\Feature\PrestasiSantriVoidApiTest.php --no-ansi`
- [x] `php artisan module:validate --no-ansi`
- [x] `npm run types:check`
- [x] `npm run lint:check`
- [x] `npm run build`
- [x] PowerShell:
  `$env:E2E_START_SERVER='true'; npx playwright test tests/Browser/prestasi-santri.spec.ts`

Hasil:

- Browser QA PrestasiSantri tersedia di
  `tests/Browser/prestasi-santri.spec.ts` dengan fixture idempotent di
  `tests/Browser/support/prestasi-santri-fixture.ts`.
- QA mencakup desktop Chromium dan mobile Chromium.
- Flow yang diuji: login operator fixture, buka list, filter prestasi, buat
  kategori, buat draft prestasi, submit, void, buka detail submitted, serta
  buka dialog verifikasi dan revisi.
- Accessibility gate high-impact berjalan dan console/page error serta response
  403 harus kosong.
- User manual lifecycle diperbarui dengan Langkah P: Prestasi Santri dan relasi
  yang belum otomatis.

## Keputusan Baseline

- [x] Module teknis memakai `Pesantrian/PrestasiSantri`.
- [x] Nama tampil memakai `Prestasi`.
- [x] Prestasi awal memakai admin/internal, bukan public form.
- [x] Lampiran bukti/sertifikat ditunda sampai module `Support/Document`.
- [x] Prestasi Tahfidz berbentuk lomba/penghargaan boleh dicatat di Prestasi,
  tetapi setoran dan target hafalan tetap milik `Tahfidz`.
- [x] Verified tidak diedit langsung; koreksi memakai lifecycle revision/void.
