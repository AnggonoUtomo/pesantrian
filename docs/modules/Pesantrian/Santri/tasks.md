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

- [ ] Buat DTO/read model Santri ringkas.
- [ ] Buat use case list/search/filter.
- [ ] Buat use case detail.
- [ ] Buat controller/route read.
- [ ] Tambahkan focused feature tests.

## Increment 5: Create/Update Manual

- [ ] Buat request validation create/update.
- [ ] Buat use case create manual.
- [ ] Buat use case update data induk.
- [ ] Buat use case update wali snapshot.
- [ ] Implementasi auto-generate `student_no`.
- [ ] Tambahkan permission enforcement.
- [ ] Tambahkan audit create/update.
- [ ] Tambahkan focused tests.

## Increment 6: Konversi PPDB Accepted

- [ ] Implementasikan atau gunakan `AcceptedAdmissionReader`.
- [ ] Buat use case create from accepted admission.
- [ ] Tambahkan idempotency check admission ke Santri.
- [ ] Simpan trace `admission_id` dan `registration_no`.
- [ ] Copy snapshot wali minimum.
- [ ] Tambahkan audit `santri.created_from_admission`.
- [ ] Tambahkan tests untuk accepted/non-accepted/duplicate conversion.
- [ ] Pastikan Santri tidak mengimpor Infrastructure PenerimaanSantri.

## Increment 7: Lifecycle dan Archive

- [ ] Buat use case lifecycle status.
- [ ] Buat validasi alasan status.
- [ ] Buat use case archive.
- [ ] Buat use case restore.
- [ ] Tambahkan audit lifecycle/archive/restore.
- [ ] Tambahkan focused tests.

## Increment 8: UI/Inertia List dan Detail

- [ ] Buat page `resources/js/pages/Pesantrian/Santri/pages/Index.tsx`.
- [ ] Buat page `resources/js/pages/Pesantrian/Santri/pages/Show.tsx`.
- [ ] Buat komponen table/filter/summary/detail di folder `components`.
- [ ] Tambahkan pagination.
- [ ] Tambahkan menu sidebar namespace Pesantrian.
- [ ] Tambahkan presentation tests/Ziggy route test bila relevan.
- [ ] Jalankan typecheck, lint, dan build.

## Increment 9: UI Create/Update dan Konversi

- [ ] Buat form data induk.
- [ ] Buat form wali snapshot.
- [ ] Buat aksi create/update.
- [ ] Buat aksi convert accepted admission.
- [ ] Tambahkan feedback sukses/gagal.
- [ ] Pastikan page tetap tipis dan komponen terpisah.

## Increment 10: UI Lifecycle, Archive, dan QA Browser

- [ ] Buat lifecycle panel.
- [ ] Buat archive/restore confirmation.
- [ ] Jalankan browser QA desktop.
- [ ] Jalankan browser QA mobile/responsive.
- [ ] Dokumentasikan hasil QA.

## Keputusan Baseline

- [x] Format nomor induk final memakai prefix `NIS`, misalnya `NIS-0001`.
- [x] Create manual dibuka untuk admin/internal dengan permission dan audit.
- [x] Snapshot wali dipertahankan di Santri sampai module `WaliSantri`
  dibutuhkan sebagai master reusable.
- [x] NIK/NISN ditunda dari baseline awal.
