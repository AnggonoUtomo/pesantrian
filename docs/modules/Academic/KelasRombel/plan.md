# Implementation Plan: Academic/KelasRombel

## Prinsip

Pekerjaan dilakukan incremental. Setiap increment harus kecil, bisa
diverifikasi, dan tidak memulai scope berikutnya tanpa instruksi user.

`Academic/KelasRombel` menjadi modul akademik operasional pertama setelah data
induk Santri. Karena ini consumer nyata pertama untuk lookup Santri, periode
akademik, unit, dan SDM/guru, implementasi harus berhati-hati pada public
contract lintas module.

## Increment 1: Dokumentasi Module

Tujuan:

- membuat dokumentasi awal module KelasRombel;
- menegaskan boundary dengan AcademicPeriod, Santri, HumanResource, dan
  Organization;
- menyusun plan dan tasks implementasi.

Deliverable:

- `docs/modules/Academic/KelasRombel/README.md`
- `docs/modules/Academic/KelasRombel/specification.md`
- `docs/modules/Academic/KelasRombel/plan.md`
- `docs/modules/Academic/KelasRombel/tasks.md`
- update indeks module/roadmap bila diperlukan.

Verifikasi:

```bash
git diff --check
php artisan module:validate --no-ansi
```

## Increment 2: Skeleton Module

Tujuan:

- membuat skeleton `app/Modules/Academic/KelasRombel`;
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
php artisan module:make Academic KelasRombel --dry-run --json --no-ansi
php artisan module:make Academic KelasRombel --force --yes --json --no-ansi
php artisan module:validate --no-ansi
php artisan optimize:clear --no-ansi
git diff --check
```

## Increment 3: Contract Readiness

Tujuan:

- menyiapkan public read contract minimum dari module pemilik data bila belum
  tersedia dan benar-benar dibutuhkan oleh KelasRombel.

Candidate:

- active period dari `Academic/AcademicPeriod`;
- active student lookup dari `Pesantrian/Santri`;
- active teacher/employee lookup dari `HumanResource/HumanResource`;
- active education unit lookup dari `Organization/Organization`.

Acceptance:

- contract memakai DTO ringkas;
- tidak ada import Infrastructure lintas module;
- contract dibuat hanya untuk kebutuhan UI/use case KelasRombel yang nyata.

Status:

- selesai pada Increment 3 dengan focused test
  `KelasRombelContractReadinessTest`;
- contract dibuat di module pemilik data agar KelasRombel tidak bergantung pada
  Eloquent model Infrastructure lintas module.

## Increment 4: Data Foundation

Tujuan:

- membuat schema kurikulum, kelas, rombel, placement santri, dan wali kelas.

Deliverable:

- migration `academic_curricula`;
- migration `class_levels`;
- migration `class_groups`;
- migration `class_group_students`;
- migration `class_group_homerooms`;
- Eloquent record Infrastructure;
- factory/test fixture minimum.

Acceptance:

- semua table memakai ULID;
- unique constraint utama tersedia;
- FK memakai referensi stabil;
- invariant placement aktif bisa diuji.

## Increment 5: Backend Read/List

Tujuan:

- menyediakan query list/detail rombel dan selector minimum.

Deliverable:

- DTO/read model;
- query list/search/filter;
- query detail;
- controller/route read;
- focused feature tests.

Acceptance:

- filter periode, unit, kurikulum, status, dan search tersedia;
- response detail memuat daftar santri dan wali kelas ringkas;
- actor tanpa permission ditolak backend.

## Increment 6: Create/Update Kurikulum, Kelas, dan Rombel

Tujuan:

- membuat mutation master kurikulum, tingkat kelas, dan rombel.

Deliverable:

- request validation;
- action create/update;
- audit mutation;
- focused tests.

Acceptance:

- duplicate code ditolak;
- date/period relation valid;
- closed/archived state tidak bisa dimutasi sembarangan.

## Increment 7: Penempatan Santri

Tujuan:

- menempatkan, memindahkan, atau mengeluarkan santri dari rombel.

Deliverable:

- action place student;
- action transfer student;
- action remove student;
- validation alasan transfer/remove;
- audit placement.

Acceptance:

- satu santri hanya punya satu placement aktif pada periode yang sama;
- hanya santri aktif yang bisa ditempatkan;
- closed/archived rombel ditolak untuk placement baru.

## Increment 8: Wali Kelas dan Archive

Tujuan:

- menetapkan wali kelas dan menyediakan archive/restore rombel.

Deliverable:

- action assign/end homeroom;
- action archive/restore class group;
- audit homeroom dan archive;
- focused tests.

Acceptance:

- wali kelas berasal dari pegawai/guru aktif;
- satu rombel hanya punya satu wali kelas aktif;
- archived rombel tidak tampil default.

## Increment 9: Demo Seeder

Tujuan:

- menyediakan data demo idempotent untuk lifecycle module.

Deliverable:

- `app/Modules/Academic/KelasRombel/Database/Seeders/KelasRombelDemoSeeder.php`;
- integrasi ke `database/seeders/DatabaseSeeder.php`;
- test seeder demo.

Acceptance:

- seeder aman diulang;
- memakai data demo Organization, AcademicPeriod, HumanResource, dan Santri;
- tidak berjalan di production.

## Increment 10: UI/Inertia List dan Detail

Tujuan:

- membuat UI daftar dan detail Kelas/Rombel.

Deliverable:

- `resources/js/pages/Academic/KelasRombel/pages/Index.tsx`;
- `resources/js/pages/Academic/KelasRombel/pages/Show.tsx`;
- komponen di `resources/js/pages/Academic/KelasRombel/components/`;
- menu sidebar namespace Academic.

Acceptance:

- page tetap tipis;
- filter/search/pagination tersedia;
- detail menampilkan santri dan wali kelas;
- permission backend tetap authority.

## Increment 11: UI Mutation dan QA Browser

Tujuan:

- membuat form mutation utama dan memverifikasi browser desktop/mobile.

Deliverable:

- form kurikulum/kelas/rombel;
- form placement santri;
- form wali kelas;
- confirmation archive/restore;
- browser QA desktop/mobile.

Acceptance:

- validation error dan success toast jelas;
- destructive action memakai confirmation;
- console browser bersih dari error route/Ziggy;
- layout mobile tidak menumpuk.

## Risiko dan Guardrail

- Jangan import model Infrastructure Santri, HumanResource, Organization, atau
  AcademicPeriod secara langsung dari KelasRombel.
- Jangan membuat subject/jadwal/presensi akademik sebelum KelasRombel stabil.
- Jangan membuat public contract berlebihan tanpa field yang benar-benar
  dibutuhkan.
- Jangan menaruh UI di folder selain `resources/js/pages`.
- Jangan menyimpan data sensitif berlebihan di audit.
