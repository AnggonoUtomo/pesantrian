# Tasks: Pesantrian/Asrama

## Sebelum Mulai

- [x] Scope dan non-scope awal ditentukan.
- [x] Dependency dan keputusan terbuka diketahui.
- [x] Cara verifikasi awal ditentukan.
- [x] `AGENTS.md`, `docs/README.md`, `docs/ARCHITECTURE.md`,
  `docs/FOLDER-STRUCTURE.md`, `docs/MODULES.md`, dan baseline Asrama dibaca.

## Increment 1: Dokumentasi Module

- [x] Buat folder dokumentasi `docs/modules/Pesantrian/Asrama/`.
- [x] Buat `README.md`.
- [x] Buat `specification.md`.
- [x] Buat `plan.md`.
- [x] Buat `tasks.md`.
- [x] Review dan setujui open questions sebelum coding.

Hasil:

- Dokumentasi awal menetapkan nama tampil `Asrama`.
- Boundary awal memisahkan Asrama dari Santri, Presensi, Perizinan,
  Kedisiplinan, Kesehatan, Pembinaan, dan Asset.
- Dependency awal memakai Organization, Santri contract, HumanResource contract,
  AccessControl, dan AuditLog.

Verifikasi:

- [x] Review manual isi dokumen.
- [x] `git diff --check`

## Increment 2: Skeleton Module

- [x] Jalankan dry-run generator module.
  - Acceptance: target `app/Modules/Pesantrian/Asrama`, diagnostics kosong,
    dan tidak ada folder optional placeholder.
  - Verification:
    `php artisan module:make Pesantrian Asrama --dry-run --json --no-ansi`.
- [x] Generate skeleton module.
  - Acceptance: file awal module dibuat tanpa folder kosong placeholder.
  - Verification:
    `php artisan module:make Pesantrian Asrama --force --yes --json --no-ansi`.
- [x] Tambahkan metadata module.
  - Acceptance: manifest dan runtime config sesuai baseline.
  - Verification: `php artisan module:inspect Pesantrian/Asrama --json --no-ansi`.
- [x] Tambahkan permission candidate.
  - Acceptance: `asrama.view`, `asrama.manage`, `asrama.placement`,
    `asrama.supervisor`, dan `asrama.archive` tersedia.
  - Verification: focused permission identity test.
- [x] Pastikan module registry valid.
  - Verification: `php artisan module:validate --no-ansi`.
- [x] Pastikan command artisan baseline normal.

Hasil:

- Skeleton module tersedia di `app/Modules/Pesantrian/Asrama`.
- Generator membuat artefak root module dan route baseline tanpa folder optional
  placeholder.
- Permission identity tersedia: `asrama.view`, `asrama.manage`,
  `asrama.placement`, `asrama.supervisor`, dan `asrama.archive`.
- Seeder AccessControl memberikan permission Asrama ke role demo terkait:
  SuperSystem/SecurityAdmin melalui sync semua permission, OperatorSantri untuk
  operasional Asrama, serta Auditor/Viewer untuk akses baca.

Verifikasi:

- [x] `php artisan module:make Pesantrian Asrama --dry-run --json --no-ansi`
- [x] `php artisan module:make Pesantrian Asrama --force --yes --json --no-ansi`
- [x] `php artisan module:inspect Pesantrian/Asrama --json --no-ansi`
- [x] `php artisan test tests\Unit\AsramaPermissionIdentityTest.php --no-ansi`
- [x] `php artisan test --filter=AccessControlSeeder --no-ansi`
- [x] `php artisan test --filter=BusinessDemoSeeder --no-ansi`
- [x] `php artisan module:validate --no-ansi`
- [x] `php artisan optimize:clear --no-ansi`

## Increment 3: Contract Readiness

- [x] Review kebutuhan public contract lintas module.
  - Acceptance: field minimum Organization, Santri, dan HumanResource jelas.
  - Verification: review manual spec dan source contract existing.
- [x] Implementasikan contract minimum hanya bila consumer nyata disetujui.
  - Acceptance: consumer memakai DTO/query public boundary, bukan Eloquent model
    Infrastructure module lain.
  - Verification: focused contract/query tests.

Hasil:

- `EducationUnitReader` tidak dipakai ulang untuk Asrama karena namanya dan
  kontraknya khusus unit pendidikan.
- Ditambahkan `DormitoryUnitReader` dan `DormitoryUnitOptionData` di
  `Organization/Organization` untuk selector unit organisasi bertipe
  `dormitory`.
- `ActiveStudentOptionData` ditambah field optional `gender` agar Asrama dapat
  memvalidasi aturan asrama putra/putri tanpa membaca model Infrastructure
  Santri.
- `ActiveEmployeeReader` existing dinilai cukup untuk selector musyrif/pembina
  karena sudah membawa employee no, nama, unit utama, jenis pegawai, dan posisi.
- Belum dibuat public contract keluar dari Asrama karena belum ada consumer
  nyata.

Verifikasi:

- [x] `php artisan test tests\Feature\AsramaContractReadinessTest.php --no-ansi`
- [x] `php artisan test tests\Feature\KelasRombelContractReadinessTest.php --no-ansi`
- [x] `php artisan test --filter=Santri --no-ansi`

## Increment 4: Data Foundation

- [x] Buat migration `dormitories`.
- [x] Buat migration `dormitory_rooms`.
- [x] Buat migration `student_room_placements`.
- [x] Buat migration `dormitory_supervisor_assignments`.
- [x] Buat Infrastructure model/factory minimum.
- [x] Tambahkan focused schema/invariant tests.

Hasil:

- Migration module Asrama dimuat dari `ServiceProvider`.
- Table `dormitories`, `dormitory_rooms`, `student_room_placements`, dan
  `dormitory_supervisor_assignments` tersedia dengan ULID.
- Kode asrama unique global, kode kamar unique per asrama.
- Satu placement kamar aktif per santri dijaga dengan `active_student_key`
  nullable unique, sementara placement lama tetap tersimpan sebagai histori.
- Record model dan factory minimum tersedia di layer Infrastructure/Database.

Verifikasi:

- [x] `php artisan test tests\Feature\AsramaDataFoundationTest.php --no-ansi`

## Increment 5: Backend Read/List

- [x] Buat DTO/read model Asrama.
- [x] Buat use case list/search/filter.
- [x] Buat use case detail.
- [x] Buat controller/route API read.
- [x] Jalankan focused API tests.

Hasil:

- Contract `AsramaReadRepository` tersedia untuk query read-only.
- DTO list/detail Asrama tersedia, termasuk unit, kamar, occupancy, placement
  aktif, dan musyrif aktif.
- Endpoint internal `GET /api/v1/pesantrian/asrama` mendukung search, filter,
  pagination, sort, dan permission `asrama.view`.
- Endpoint internal `GET /api/v1/pesantrian/asrama/{dormitory}` mengembalikan
  detail asrama dengan kamar, keterisian, placement aktif, dan musyrif aktif.
- Response mengikuti envelope API canonical dengan correlation id.

Verifikasi:

- [x] `php artisan test tests\Feature\AsramaApiTest.php --no-ansi`
- [x] `php artisan test --filter=Asrama --no-ansi`
- [x] `php artisan module:validate --no-ansi`
- [x] `php artisan optimize:clear --no-ansi`

## Increment 6: Create/Update Asrama dan Kamar

- [x] Buat request validation create/update.
- [x] Buat action create/update asrama.
- [x] Buat action create/update kamar.
- [x] Tambahkan audit mutation.
- [x] Jalankan focused mutation tests.

Hasil:

- Endpoint mutation internal Asrama tersedia:
  - `POST /api/v1/pesantrian/asrama`
  - `PATCH /api/v1/pesantrian/asrama/{dormitory}`
  - `POST /api/v1/pesantrian/asrama/{dormitory}/rooms`
  - `PATCH /api/v1/pesantrian/asrama/{dormitory}/rooms/{room}`
- Mutation memakai permission `asrama.manage` dan idempotency middleware.
- Request validation menjaga kode uppercase, status valid, capacity positif,
  kode kamar unique per asrama, dan unit organisasi wajib bertipe `dormitory`.
- Action mutation mem-publish audit:
  - `asrama.dormitory.created`
  - `asrama.dormitory.updated`
  - `asrama.room.created`
  - `asrama.room.updated`
- AuditLog menerima integration event `Asrama`.

Verifikasi:

- [x] `php artisan test tests\Feature\AsramaMutationApiTest.php --no-ansi`

## Increment 7: Penempatan dan Transfer Santri

- [x] Buat action place student.
- [x] Buat action transfer room.
- [x] Buat action remove student.
- [x] Tambahkan audit placement/transfer/remove.
- [x] Jalankan focused placement tests.

Hasil:

- Endpoint mutation penempatan kamar santri tersedia:
  - `POST /api/v1/pesantrian/asrama/{dormitory}/placements`
  - `PATCH /api/v1/pesantrian/asrama/{dormitory}/placements/{placement}/transfer`
  - `PATCH /api/v1/pesantrian/asrama/{dormitory}/placements/{placement}/remove`
- Mutation memakai permission `asrama.placement` dan idempotency middleware.
- Action placement memvalidasi santri aktif melalui contract Santri,
  memastikan santri berada pada unit asrama, menjaga satu kamar aktif per
  santri, mengecek kapasitas kamar, status/arsip asrama dan kamar, serta
  kebijakan gender asrama.
- Transfer kamar menutup placement lama sebagai `moved` dan membuat placement
  aktif baru.
- Keluar kamar menutup placement sebagai `inactive`, menyimpan alasan, dan
  mencatat actor penutup.
- Audit placement mem-publish:
  - `asrama.student.placed`
  - `asrama.student.transferred`
  - `asrama.student.removed`

Verifikasi:

- [x] `php artisan test tests\Feature\AsramaPlacementApiTest.php --no-ansi`
- [x] `php artisan test --filter=Asrama --no-ansi`
- [x] `php artisan route:list --name=api.v1.pesantrian.asrama --no-ansi`
- [x] `php artisan module:validate --no-ansi`
- [x] `php artisan optimize:clear --no-ansi`
- [x] `vendor\bin\pint --dirty --test`
- [x] `vendor\bin\phpstan analyse app/Modules/Pesantrian/Asrama --no-progress --error-format=table`

## Increment 8: Penugasan Musyrif dan Archive

- [x] Buat action assign/end supervisor.
- [x] Buat use case archive/restore asrama.
- [x] Buat use case archive/restore kamar.
- [x] Tambahkan audit supervisor/archive/restore.
- [x] Jalankan focused tests.

Hasil:

- Endpoint mutation penugasan musyrif/pembina tersedia:
  - `POST /api/v1/pesantrian/asrama/{dormitory}/supervisors`
  - `PATCH /api/v1/pesantrian/asrama/{dormitory}/supervisors/{assignment}/end`
- Endpoint archive/restore tersedia:
  - `PATCH /api/v1/pesantrian/asrama/{dormitory}/archive`
  - `PATCH /api/v1/pesantrian/asrama/{dormitory}/restore`
  - `PATCH /api/v1/pesantrian/asrama/{dormitory}/rooms/{room}/archive`
  - `PATCH /api/v1/pesantrian/asrama/{dormitory}/rooms/{room}/restore`
- Penugasan musyrif memakai contract `ActiveEmployeeReader` dari
  `HumanResource/HumanResource`, sehingga Asrama tidak membaca model
  Infrastructure module SDM secara langsung.
- Penugasan menolak pegawai tidak aktif, asrama/kamar arsip, kamar beda
  asrama, dan duplicate active assignment pada scope yang sama.
- Archive asrama/kamar mengubah status menjadi `inactive`, menyimpan
  `archived_at` dan `archived_by`; restore mengembalikan status menjadi
  `active`.
- Placement baru otomatis tertolak ketika asrama atau kamar sudah terarsip.
- Audit mutation mem-publish:
  - `asrama.supervisor.assigned`
  - `asrama.supervisor.ended`
  - `asrama.dormitory.archived`
  - `asrama.dormitory.restored`
  - `asrama.room.archived`
  - `asrama.room.restored`

Verifikasi:

- [x] `php artisan test tests\Feature\AsramaSupervisorArchiveApiTest.php --no-ansi`
- [x] `php artisan test --filter=Asrama --no-ansi`
- [x] `php artisan route:list --name=api.v1.pesantrian.asrama --no-ansi`
- [x] `php artisan module:validate --no-ansi`
- [x] `php artisan optimize:clear --no-ansi`
- [x] `vendor\bin\pint --dirty --test`
- [x] `vendor\bin\phpstan analyse app/Modules/Pesantrian/Asrama --no-progress --error-format=table`

## Increment 9: Demo Seeder

- [x] Tambahkan seeder demo Asrama idempotent.
- [x] Update business demo seeder.
- [x] Tambahkan seeder test.
- [x] Dokumentasikan data demo di README/user manual bila diperlukan.

Hasil:

- `AsramaDemoSeeder` tersedia dan aman dijalankan ulang pada environment non
  production.
- Global `DatabaseSeeder` memanggil `AsramaDemoSeeder` setelah Organization,
  HumanResource, Santri, dan Kelas/Rombel demo tersedia.
- Data demo Asrama mencakup:
  - asrama putra dan putri aktif;
  - asrama arsip/renovasi;
  - lima kamar demo termasuk kamar arsip;
  - placement santri aktif;
  - riwayat pindah kamar;
  - riwayat keluar kamar;
  - musyrif aktif dan pembina historis yang sudah selesai.
- User manual lifecycle dan README module menjelaskan data demo Asrama serta
  relasinya ke Organization, Santri, dan HumanResource.

Verifikasi:

- [x] `php artisan test tests\Feature\BusinessDemoSeederTest.php --no-ansi`

## Increment 10: UI/Inertia List dan Detail

- [x] Buat page `resources/js/pages/Pesantrian/Asrama/pages/Index.tsx`.
- [x] Buat page `resources/js/pages/Pesantrian/Asrama/pages/Show.tsx`.
- [x] Buat komponen table/filter/summary/detail di folder `components`.
- [x] Tambahkan pagination.
- [x] Tambahkan menu sidebar namespace Pesantrian.
- [x] Tambahkan presentation tests/Ziggy route test bila relevan.
- [x] Jalankan typecheck, lint, dan build.

Hasil:

- Web route Inertia `pesantrian.asrama.index` dan
  `pesantrian.asrama.show` tersedia agar route Ziggy tidak hilang dari UI.
- Halaman Index dan Show tetap tipis; logic list/detail berada pada komponen
  `resources/js/pages/Pesantrian/Asrama/components/`.
- Index menyediakan ringkasan, filter, table responsif, empty state, dan
  pagination.
- Detail menyediakan ringkasan asrama, daftar kamar, penempatan santri aktif,
  dan musyrif/pembina.
- Sidebar namespace Pesantrian menampilkan menu `Asrama` untuk user dengan
  permission Asrama.
- UI mutation belum dibuka; form create/update, placement, musyrif, dan archive
  disiapkan untuk Increment 11.

Verifikasi:

- [x] `php artisan test tests\Feature\AsramaPresentationTest.php --no-ansi`
- [x] `php artisan test tests\Feature\SantriPresentationTest.php tests\Feature\KelasRombelPresentationTest.php --no-ansi`
- [x] `php artisan route:list --name=pesantrian.asrama --no-ansi`
- [x] `npm run lint:check`
- [x] `npm run types:check`
- [x] `npm run build`

## Increment 11: UI Mutation dan QA Browser

- [x] Buat form asrama.
- [x] Buat form kamar.
- [x] Buat form placement/transfer/remove.
- [x] Buat form musyrif.
- [x] Buat archive/restore confirmation.
- [x] Jalankan browser QA desktop.
- [x] Jalankan browser QA mobile/responsive.
- [x] Dokumentasikan hasil QA.

Hasil:

- Web mutation route Asrama tersedia untuk Inertia:
  - `POST /pesantrian/asrama`
  - `PATCH /pesantrian/asrama/{dormitory}`
  - `POST /pesantrian/asrama/{dormitory}/rooms`
  - `PATCH /pesantrian/asrama/{dormitory}/rooms/{room}`
  - `POST /pesantrian/asrama/{dormitory}/placements`
  - `PATCH /pesantrian/asrama/{dormitory}/placements/{placement}/transfer`
  - `PATCH /pesantrian/asrama/{dormitory}/placements/{placement}/remove`
  - `POST /pesantrian/asrama/{dormitory}/supervisors`
  - `PATCH /pesantrian/asrama/{dormitory}/supervisors/{assignment}/end`
  - `PATCH /pesantrian/asrama/{dormitory}/archive`
  - `PATCH /pesantrian/asrama/{dormitory}/restore`
  - `PATCH /pesantrian/asrama/{dormitory}/rooms/{room}/archive`
  - `PATCH /pesantrian/asrama/{dormitory}/rooms/{room}/restore`
- Form mutation ditempatkan pada
  `resources/js/pages/Pesantrian/Asrama/components/AsramaMutationDialogs.tsx`
  dan field reusable pada `AsramaFormFields.tsx`.
- Page `Index.tsx` dan `Show.tsx` tetap tipis; logic UI berada di folder
  `components`.
- Backend tetap menjadi authority permission dan validasi business rule.
  Frontend hanya menampilkan action sesuai permission untuk UX.
- QA browser menemukan migrasi MySQL gagal karena nama index auto-generated
  terlalu panjang. Migration Asrama diperbaiki dengan nama index eksplisit yang
  lebih pendek.
- QA browser desktop/mobile berhasil membuka list/detail Asrama, dialog tambah
  asrama, tambah kamar, placement, transfer, musyrif, dan archive confirmation
  tanpa console error/Ziggy error.

Verifikasi:

- [x] `php artisan migrate --no-ansi`
- [x] `php artisan db:seed --class=App\Modules\Pesantrian\Asrama\Database\Seeders\AsramaDemoSeeder --no-ansi`
- [x] `php artisan test tests\Feature\AsramaPresentationTest.php tests\Feature\AsramaMutationApiTest.php tests\Feature\AsramaPlacementApiTest.php tests\Feature\AsramaSupervisorArchiveApiTest.php`
- [x] `php artisan route:list --name=pesantrian.asrama --no-ansi`
- [x] `vendor\bin\pint --dirty --test`
- [x] `vendor\bin\phpstan analyse app/Modules/Pesantrian/Asrama tests/Feature/AsramaPresentationTest.php --no-progress --error-format=table`
- [x] `npm run types:check`
- [x] `npm run lint:check`
- [x] `npm run build`
- [x] Browser QA desktop: `http://pesantrian.test/pesantrian/asrama`
      dan detail demo Asrama.
- [x] Browser QA mobile 390x844: list/detail Asrama dan dialog mutation utama.

## Keputusan Baseline

- [x] Module teknis memakai `Pesantrian/Asrama`.
- [x] Nama tampil memakai `Asrama`.
- [x] Asrama putra/putri dimodelkan sebagai data, bukan module terpisah.
- [x] Inventaris kamar ditunda ke `Support/Asset`.
- [x] Presensi asrama ditunda sampai work item presensi disepakati.
- [x] Penempatan santri aktif dijaga satu kamar aktif pada satu waktu.
