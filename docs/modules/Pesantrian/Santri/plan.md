# Implementation Plan: Pesantrian/Santri

## Prinsip

Pekerjaan dilakukan incremental. Setiap increment harus kecil, bisa diverifikasi,
dan tidak memulai scope berikutnya tanpa instruksi user.

`Pesantrian/Santri` menjadi sumber data induk santri. Untuk baseline, data wali
ditangani sebagai snapshot minimum di dalam module Santri. Module
`Pesantrian/WaliSantri` tetap planned untuk master wali penuh setelah kebutuhan
relasi dan akses wali lebih jelas.

## Increment 1: Dokumentasi Module

Tujuan:

- membuat dokumentasi awal module Santri;
- menegaskan boundary dengan PPDB dan WaliSantri;
- menyusun plan dan tasks implementasi.

Deliverable:

- `docs/modules/Pesantrian/Santri/README.md`
- `docs/modules/Pesantrian/Santri/specification.md`
- `docs/modules/Pesantrian/Santri/plan.md`
- `docs/modules/Pesantrian/Santri/tasks.md`
- update indeks module bila diperlukan.

Verifikasi:

- review manual isi dokumen;
- `git diff --check`.

## Increment 2: Skeleton Module

Tujuan:

- membuat skeleton `app/Modules/Pesantrian/Santri`;
- memastikan module discovery/registry normal.

Deliverable:

- `module.json`;
- `module.php`;
- `permissions.php`;
- `ServiceProvider.php`;
- route file baseline;
- struktur layer sesuai `docs/FOLDER-STRUCTURE.md`.

Verifikasi:

```bash
php artisan module:make Pesantrian Santri --dry-run --json --no-ansi
php artisan module:make Pesantrian Santri --force --yes --no-ansi
php artisan module:validate
php artisan optimize:clear
php artisan test --filter=Santri
```

## Increment 3: Data Foundation

Tujuan:

- membuat schema data induk santri dan snapshot wali minimum;
- menyiapkan model Infrastructure tanpa membocorkan model ke Application.

Deliverable:

- migration `students`;
- migration `student_guardians`;
- Eloquent record di Infrastructure;
- factory/test fixture minimum bila diperlukan.

Verifikasi:

```bash
php artisan migrate:fresh --seed
php artisan test --filter=Santri
```

## Increment 4: Backend Read/List

Tujuan:

- menyediakan query list/detail santri;
- menyiapkan DTO/read model untuk Presentation.

Deliverable:

- use case list/search/filter;
- use case detail;
- route/controller Inertia read;
- focused feature tests.

Acceptance:

- list dapat difilter status dan unit;
- detail tidak mengekspos field yang bukan kebutuhan UI baseline.

## Increment 5: Create/Update Manual

Tujuan:

- membuat data santri manual oleh admin berwenang;
- memperbarui data induk dan snapshot wali.

Deliverable:

- request validation;
- use case create/update;
- permission enforcement;
- audit create/update.

Acceptance:

- `student_no` auto-generated dan unique;
- validasi input jelas;
- unauthorized actor ditolak.

## Increment 6: Konversi PPDB Accepted

Tujuan:

- mengubah accepted admission menjadi data induk Santri melalui contract PPDB.

Deliverable:

- implement/use `AcceptedAdmissionReader`;
- use case create from accepted admission;
- idempotency check;
- audit `santri.created_from_admission`;
- tests anti-corruption boundary.

Acceptance:

- hanya admission accepted dan eligible yang dapat dikonversi;
- admission yang sama tidak menghasilkan duplikasi santri;
- Santri tidak membaca model Infrastructure PenerimaanSantri.

## Increment 7: Lifecycle dan Archive

Tujuan:

- mengelola perubahan status santri;
- menyediakan archive/restore aman.

Deliverable:

- use case lifecycle;
- use case archive/restore;
- audit lifecycle/archive/restore;
- focused tests.

Acceptance:

- status hanya dapat memakai vocabulary baseline;
- perubahan status wajib mencatat alasan untuk inactive/transferred/graduated;
- archived data tidak tampil di list aktif default.

## Increment 8: UI/Inertia List dan Detail

Tujuan:

- membuat UI daftar dan detail Santri.

Deliverable:

- `resources/js/pages/Pesantrian/Santri/pages/Index.tsx`;
- `resources/js/pages/Pesantrian/Santri/pages/Show.tsx`;
- komponen di `resources/js/pages/Pesantrian/Santri/components/`;
- sidebar menu sesuai namespace Pesantrian.

Acceptance:

- index tetap tipis dan delegasi UI ke komponen;
- filter/search/pagination tersedia;
- empty/error state jelas.

## Increment 9: UI Create/Update dan Konversi

Tujuan:

- membuat form data induk dan wali snapshot;
- menyediakan aksi konversi dari PPDB accepted bila backend sudah siap.

Deliverable:

- form create/update;
- validasi error Inertia;
- action convert accepted admission;
- feedback sukses/gagal.

Acceptance:

- form tidak menumpuk di index page;
- permission action konsisten dengan backend;
- user paham apakah santri dibuat manual atau dari PPDB.

## Increment 10: UI Lifecycle, Archive, dan QA Browser

Tujuan:

- membuat panel perubahan status dan archive;
- memastikan flow UI stabil di browser.

Deliverable:

- lifecycle panel;
- archive/restore confirmation;
- browser QA desktop/mobile;
- dokumentasi hasil QA.

Acceptance:

- aksi destructive memakai konfirmasi;
- status dan archive terlihat jelas;
- console browser bersih dari error route/Ziggy.

## Risiko dan Guardrail

- Jangan membuat dependency konkret ke Infrastructure module lain.
- Jangan memasukkan semua kebutuhan wali ke Santri; simpan hanya snapshot
  minimum sampai `Pesantrian/WaliSantri` diputuskan.
- Jangan menaruh UI Santri di folder `modules`; UI project berada di
  `resources/js/pages`.
- Jangan membuat public contract tanpa consumer nyata.
- Jangan menyimpan data sensitif berlebihan di audit.
