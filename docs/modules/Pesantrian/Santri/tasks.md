# Tasks: Pesantrian/Santri

## Increment 1: Dokumentasi Module

- [x] Buat folder dokumentasi `docs/modules/Pesantrian/Santri/`.
- [x] Buat `README.md`.
- [x] Buat `specification.md`.
- [x] Buat `plan.md`.
- [x] Buat `tasks.md`.
- [ ] Review dan setujui open questions sebelum coding.

## Increment 2: Skeleton Module

- [x] Jalankan dry-run generator module.
- [x] Buat skeleton `app/Modules/Pesantrian/Santri`.
- [x] Tambahkan metadata module.
- [x] Tambahkan permissions candidate.
- [x] Pastikan module registry valid.
- [x] Pastikan semua command artisan baseline normal.

Hasil verifikasi:

- [x] `php artisan module:make Pesantrian Santri --dry-run --json --no-ansi`
- [x] `php artisan module:make Pesantrian Santri --force --yes --json --no-ansi`
- [x] `php artisan module:validate --no-ansi`
- [x] `php artisan optimize:clear --no-ansi`
- [x] `php artisan module:inspect Pesantrian/Santri --json --no-ansi`
- [x] `php artisan test tests\Unit\SantriPermissionIdentityTest.php --no-ansi`
- [x] `php artisan test --filter=Santri --no-ansi`

## Increment 3: Data Foundation

- [x] Buat migration `students`.
- [x] Buat migration `student_guardians`.
- [x] Buat Eloquent record Infrastructure.
- [x] Buat factory/test fixture minimum.
- [x] Tambahkan test schema dan constraint penting.

Hasil verifikasi:

- [x] `php artisan test tests\Feature\SantriSchemaTest.php --no-ansi`
- [x] `vendor\bin\pint --dirty --test`
- [x] `php artisan module:validate --no-ansi`
- [x] `php artisan test --filter=Santri --no-ansi`
- [x] `php artisan optimize:clear --no-ansi`
- [x] `php artisan module:inspect Pesantrian/Santri --json --no-ansi`
- [x] `git diff --check`

Catatan: `php artisan migrate:fresh --seed` tidak dijalankan pada database
lokal karena command tersebut menghapus ulang seluruh schema. Verifikasi schema
Increment 3 memakai test database melalui `RefreshDatabase`.

## Increment 4: Backend Read/List

- [x] Buat DTO/read model Santri ringkas.
- [x] Buat use case list/search/filter.
- [x] Buat use case detail.
- [x] Buat controller/route read.
- [x] Tambahkan focused feature tests.

Hasil verifikasi:

- [x] `php artisan test tests\Feature\SantriApiTest.php --no-ansi`
- [x] `vendor\bin\pint --dirty --test`
- [x] `php artisan module:validate --no-ansi`
- [x] `php artisan test --filter=Santri --no-ansi`
- [x] `php artisan optimize:clear --no-ansi`
- [x] `php artisan route:list --path=api/v1/pesantrian/students --no-ansi`
- [x] `git diff --check`

## Increment 5: Create/Update Manual

- [x] Buat request validation create/update.
- [x] Buat use case create manual.
- [x] Buat use case update data induk.
- [x] Buat use case update wali snapshot.
- [x] Implementasi auto-generate `student_no`.
- [x] Tambahkan permission enforcement.
- [x] Tambahkan audit create/update.
- [x] Tambahkan focused tests.

Hasil verifikasi:

- [x] `php artisan test tests\Feature\SantriApiTest.php tests\Feature\SantriAuditTest.php --no-ansi`
- [x] `vendor\bin\pint --dirty --test`
- [x] `php artisan module:validate --no-ansi`
- [x] `php artisan test --filter=Santri --no-ansi`
- [x] `php artisan test tests\Feature\SantriAuditTest.php tests\Feature\PenerimaanSantriAuditTest.php tests\Unit\AuditLogContractTest.php --no-ansi`
- [x] `php artisan optimize:clear --no-ansi`
- [x] `php artisan route:list --path=api/v1/pesantrian/students --no-ansi`
- [x] `git diff --check`

## Increment 6: Konversi PPDB Accepted

- [x] Implementasikan atau gunakan `AcceptedAdmissionReader`.
- [x] Buat use case create from accepted admission.
- [x] Tambahkan idempotency check admission ke Santri.
- [x] Simpan trace `admission_id` dan `registration_no`.
- [x] Copy snapshot wali minimum.
- [x] Tambahkan audit `santri.student.created_from_admission`.
- [x] Tambahkan tests untuk accepted/non-accepted/duplicate conversion.
- [x] Pastikan Santri tidak mengimpor Infrastructure PenerimaanSantri.

Hasil verifikasi:

- [x] `php artisan test tests\Feature\SantriAdmissionConversionTest.php --no-ansi`
- [x] `php artisan test tests\Feature\SantriAdmissionConversionTest.php tests\Feature\SantriApiTest.php tests\Feature\SantriAuditTest.php --no-ansi`
- [x] `php artisan test --filter=PenerimaanSantri --no-ansi`
- [x] `vendor\bin\pint --dirty --test`
- [x] `php artisan module:validate --no-ansi`
- [x] `php artisan optimize:clear --no-ansi`
- [x] `php artisan route:list --path=api/v1/pesantrian/students --no-ansi`
- [x] `php artisan test --filter=Santri --no-ansi`
- [x] `git diff --check`

## Increment 7: Lifecycle dan Archive

- [x] Buat use case lifecycle status.
- [x] Buat validasi alasan status.
- [x] Buat use case archive.
- [x] Buat use case restore.
- [x] Tambahkan audit lifecycle/archive/restore.
- [x] Tambahkan focused tests.

Hasil verifikasi:

- [x] `php artisan test tests\Feature\SantriLifecycleTest.php --no-ansi`
- [x] `php artisan test tests\Feature\SantriApiTest.php tests\Feature\SantriAuditTest.php tests\Feature\SantriAdmissionConversionTest.php tests\Feature\SantriLifecycleTest.php --no-ansi`
- [x] `vendor\bin\pint --dirty --test`
- [x] `php artisan module:validate --no-ansi`
- [x] `php artisan optimize:clear --no-ansi`
- [x] `php artisan test --filter=Santri --no-ansi`
- [x] `php artisan route:list --path=api/v1/pesantrian/students --no-ansi`
- [x] `git diff --check`

## Increment 8: UI/Inertia List dan Detail

- [x] Buat page `resources/js/pages/Pesantrian/Santri/pages/Index.tsx`.
- [x] Buat page `resources/js/pages/Pesantrian/Santri/pages/Show.tsx`.
- [x] Buat komponen table/filter/summary/detail di folder `components`.
- [x] Tambahkan pagination.
- [x] Tambahkan menu sidebar namespace Pesantrian.
- [x] Tambahkan presentation tests/Ziggy route test bila relevan.
- [x] Jalankan typecheck, lint, dan build.

Hasil verifikasi:

- [x] `php artisan test tests\Feature\SantriPresentationTest.php --no-ansi`
- [x] `php artisan test --filter=Santri --no-ansi`
- [x] `vendor\bin\pint --dirty --test`
- [x] `npm run types:check`
- [x] `npm run lint:check`
- [x] `npm run build`
- [x] `php artisan module:validate --no-ansi`
- [x] `php artisan optimize:clear --no-ansi`
- [x] `php artisan route:list --path=pesantrian/students --no-ansi`

## Increment 9: UI Create/Update dan Konversi

- [x] Buat form data induk.
- [x] Buat form wali snapshot.
- [x] Buat aksi create/update.
- [x] Buat aksi convert accepted admission.
- [x] Tambahkan feedback sukses/gagal.
- [x] Pastikan page tetap tipis dan komponen terpisah.

Hasil verifikasi:

- [x] `php artisan test tests\Feature\SantriPresentationTest.php --no-ansi`
- [x] `php artisan test --filter=Santri --no-ansi`
- [x] `vendor\bin\pint --dirty --test`
- [x] `npm run types:check`
- [x] `npm run lint:check`
- [x] `npm run build`
- [x] `php artisan module:validate --no-ansi`
- [x] `php artisan route:list --path=pesantrian/students --no-ansi`

## Increment 10: UI Lifecycle, Archive, dan QA Browser

- [x] Buat lifecycle panel.
- [x] Buat archive/restore confirmation.
- [ ] Jalankan browser QA desktop.
- [ ] Jalankan browser QA mobile/responsive.
- [x] Dokumentasikan hasil QA.

Hasil verifikasi:

- [x] `php artisan test tests\Feature\SantriPresentationTest.php --no-ansi`
- [x] `php artisan test --filter=Santri --no-ansi`
- [x] `vendor\bin\pint --dirty --test`
- [x] `npm run types:check`
- [x] `npm run lint:check`
- [x] `npm run build`
- [x] `php artisan module:validate --no-ansi`
- [x] `php artisan route:list --path=pesantrian/students --no-ansi`
- [ ] Browser QA authenticated desktop/mobile belum selesai karena database
  lokal Laragon `pesantrian` belum memiliki table `students` dan
  `student_guardians`. Fixture QA sementara `codex-santri-qa@example.test` dan
  unit `QA-SANTRI` sudah dibersihkan. Jalankan browser QA setelah database lokal
  dimigrasi atau user memberi izin eksplisit untuk menjalankan migration lokal.

## Keputusan Baseline

- [x] Format nomor induk final memakai prefix `NIS`, misalnya `NIS-0001`.
- [x] Create manual dibuka untuk admin/internal dengan permission dan audit.
- [x] Snapshot wali dipertahankan di Santri sampai module `WaliSantri`
  dibutuhkan sebagai master reusable.
- [x] NIK/NISN ditunda dari baseline awal.
