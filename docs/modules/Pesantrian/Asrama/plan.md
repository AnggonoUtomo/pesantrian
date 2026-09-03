# Implementation Plan: Pesantrian/Asrama

## Prinsip

Pekerjaan dilakukan incremental. Setiap increment harus kecil, bisa diverifikasi,
dan tidak memulai scope berikutnya tanpa instruksi user.

`Pesantrian/Asrama` dibuka setelah data induk Santri, SDM, Organization, dan
pola KelasRombel tersedia. Fokus awal adalah struktur asrama/kamar dan placement
santri, bukan presensi, kedisiplinan, atau inventaris.

## Increment 1: Dokumentasi Module

Tujuan:

- membuat dokumentasi awal module Asrama;
- menegaskan boundary dengan Santri, SDM, Organization, Presensi, dan Asset;
- menyusun plan dan tasks implementasi.

Deliverable:

- `docs/modules/Pesantrian/Asrama/README.md`
- `docs/modules/Pesantrian/Asrama/specification.md`
- `docs/modules/Pesantrian/Asrama/plan.md`
- `docs/modules/Pesantrian/Asrama/tasks.md`
- update indeks module bila diperlukan.

Verifikasi:

- review manual isi dokumen;
- `git diff --check`.

## Increment 2: Skeleton Module

Tujuan:

- membuat skeleton `app/Modules/Pesantrian/Asrama`;
- memastikan module discovery/registry normal;
- menambahkan permission candidate.

Deliverable:

- `module.json`;
- `module.php`;
- `permissions.php`;
- `ServiceProvider.php`;
- route file baseline;
- struktur layer sesuai `docs/FOLDER-STRUCTURE.md`.

Verifikasi:

```bash
php artisan module:make Pesantrian Asrama --dry-run --json --no-ansi
php artisan module:make Pesantrian Asrama --force --yes --no-ansi
php artisan module:inspect Pesantrian/Asrama --json --no-ansi
php artisan module:validate
php artisan optimize:clear
php artisan test --filter=Asrama
```

## Increment 3: Contract Readiness

Tujuan:

- memastikan lookup Organization, Santri, dan HumanResource cukup untuk Asrama;
- menambahkan contract minimum hanya jika consumer nyata membutuhkan field baru.

Deliverable:

- review public contract existing;
- contract/DTO tambahan bila diperlukan;
- focused tests boundary lintas module.

Acceptance:

- Asrama tidak mengimpor model Infrastructure Santri, HumanResource, atau
  Organization;
- field minimum untuk placement dan musyrif jelas.

## Increment 4: Data Foundation

Tujuan:

- membuat schema asrama, kamar, placement, dan musyrif;
- menyiapkan model Infrastructure.

Deliverable:

- migration `dormitories`;
- migration `dormitory_rooms`;
- migration `student_room_placements`;
- migration `dormitory_supervisor_assignments`;
- Eloquent record Infrastructure;
- factory/test fixture minimum.

Acceptance:

- semua table aplikasi memakai ULID;
- unique key mencegah satu santri punya dua placement aktif;
- kapasitas kamar bisa dihitung dari placement aktif.

## Increment 5: Backend Read/List

Tujuan:

- menyediakan query daftar dan detail asrama.

Deliverable:

- DTO/read model Asrama;
- use case list/search/filter;
- use case detail;
- route/controller API read;
- focused feature tests.

Acceptance:

- list dapat difilter status, unit, dan gender policy;
- detail memuat kamar, kapasitas, santri aktif, dan musyrif aktif.

## Increment 6: Create/Update Asrama dan Kamar

Tujuan:

- membuat asrama dan kamar oleh operator berwenang.

Deliverable:

- request validation;
- action create/update asrama;
- action create/update kamar;
- permission enforcement;
- audit mutation.

Acceptance:

- kode asrama unique;
- kode kamar unique per asrama;
- capacity wajib angka positif;
- unauthorized actor ditolak.

## Increment 7: Penempatan dan Transfer Santri

Tujuan:

- menempatkan, memindahkan, dan mengeluarkan santri dari kamar.

Deliverable:

- action place student;
- action transfer room;
- action remove student;
- audit placement/transfer/remove;
- focused tests kapasitas dan unique active placement.

Acceptance:

- hanya santri aktif yang dapat ditempatkan;
- kamar aktif dan kapasitas tersedia;
- transfer menutup placement lama dan membuat placement baru;
- keluar kamar mencatat alasan.

## Increment 8: Penugasan Musyrif dan Archive

Tujuan:

- mengelola musyrif/pembina asrama;
- menyediakan archive/restore aman.

Deliverable:

- action assign/end supervisor;
- action archive/restore asrama;
- action archive/restore kamar;
- audit supervisor/archive/restore;
- focused tests.

Acceptance:

- hanya pegawai aktif yang dapat menjadi musyrif;
- assignment lama bisa ditutup;
- archived asrama/kamar tidak menerima placement baru.

## Increment 9: Demo Seeder

Tujuan:

- menyediakan data demo Asrama lengkap untuk manual QA dan lifecycle.

Deliverable:

- `AsramaDemoSeeder`;
- update business demo seeder;
- tests idempotent seeder.

Acceptance:

- demo mencakup asrama putra/putri, beberapa kamar, santri ditempatkan, santri
  pindah kamar, dan musyrif aktif/ended;
- seeder aman diulang dan tidak berjalan di production.

## Increment 10: UI/Inertia List dan Detail

Tujuan:

- membuat UI baca daftar dan detail Asrama.

Deliverable:

- `resources/js/pages/Pesantrian/Asrama/pages/Index.tsx`;
- `resources/js/pages/Pesantrian/Asrama/pages/Show.tsx`;
- komponen di `resources/js/pages/Pesantrian/Asrama/components/`;
- sidebar menu namespace Pesantrian.

Acceptance:

- index tetap tipis;
- filter/search/pagination tersedia;
- detail menampilkan kamar, kapasitas, santri, dan musyrif.

## Increment 11: UI Mutation dan QA Browser

Tujuan:

- menyediakan form mutation utama;
- memastikan flow UI stabil di browser.

Deliverable:

- form asrama;
- form kamar;
- form placement/transfer/remove;
- form assignment musyrif;
- archive/restore confirmation;
- browser QA desktop/mobile;
- dokumentasi hasil QA.

Acceptance:

- form tidak menumpuk di index page;
- permission action konsisten dengan backend;
- destructive action memakai konfirmasi;
- console browser bersih dari error route/Ziggy.

## Risiko dan Guardrail

- Jangan menjadikan asrama putra dan asrama putri sebagai module terpisah; itu
  data asrama/unit.
- Jangan mengambil alih status utama Santri.
- Jangan membuat presensi asrama sebelum kebutuhan presensi disepakati.
- Jangan memasukkan inventaris kamar ke module Asrama baseline.
- Jangan membuat public contract keluar dari Asrama tanpa consumer nyata.
- Jangan menaruh UI Asrama di folder selain `resources/js/pages`.
