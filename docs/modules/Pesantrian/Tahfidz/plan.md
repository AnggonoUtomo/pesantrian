# Implementation Plan: Pesantrian/Tahfidz

## Overview

`Pesantrian/Tahfidz` akan dibangun incremental dari dokumentasi, skeleton,
contract readiness, data foundation, API, seeder, UI, sampai QA browser.
Baseline awal fokus pada pencatatan internal/admin untuk target, setoran,
murojaah, review, dan rekap progres hafalan.

## Prinsip Implementasi

- Mulai dari module skeleton kecil dan permission identity.
- Gunakan public contract module lain untuk lookup santri aktif, pembimbing,
  periode, rombel, dan asrama.
- Simpan snapshot `student_no`, `student_name`, dan `supervisor_name` agar
  histori setoran tetap terbaca meski master data berubah.
- Mutation Tahfidz selalu melalui Application action.
- Catatan setoran tidak dihapus permanen pada baseline.
- UI berada di `resources/js/pages/Pesantrian/Tahfidz/`, dengan page tipis dan
  komponen di folder `components`.
- Seeder demo wajib dibuat bersama module.

## Dependency Graph

```text
Santri active reader
HumanResource active employee reader
AcademicPeriod active period reader
KelasRombel/Asrama optional filter contract
        |
        v
Tahfidz module skeleton + permission
        |
        v
Migration + models + factories
        |
        v
Application actions + read queries
        |
        v
API/web routes + resources + request validation
        |
        v
Demo seeder
        |
        v
Inertia UI list/detail/mutation
        |
        v
Browser QA + user manual update
```

## Increment 1: Documentation Baseline

Tujuan:

- membuat dokumen module sebelum coding.

Deliverable:

- `README.md`;
- `specification.md`;
- `plan.md`;
- `tasks.md`;
- update `docs/MODULES.md`.

Acceptance:

- boundary, dependency, permission, lifecycle, dan open questions tercatat;
- roadmap incremental tersedia;
- belum ada source code module yang dibuat.

## Increment 2: Module Skeleton dan Permission

Tujuan:

- membuat source module minimum dan permission identity.

Deliverable:

- generator module `Pesantrian Tahfidz`;
- `module.json`, `module.php`, `permissions.php`, `ServiceProvider.php`;
- README source module;
- permission seeder wiring.

Acceptance:

- module discover/validate normal;
- permission `tahfidz.*` tersedia;
- belum ada table bisnis.

## Increment 3: Contract Readiness

Tujuan:

- memastikan Tahfidz bisa mengambil data pendukung tanpa membaca model
  Infrastructure module lain.

Deliverable:

- audit contract `ActiveStudentReader`, `ActiveEmployeeReader`, dan
  `ActiveAcademicPeriodReader`;
- contract tambahan hanya jika consumer nyata belum tersedia;
- tests contract readiness.

Acceptance:

- Tahfidz dapat mengambil kandidat santri aktif dan pembimbing aktif melalui
  contract;
- tidak ada import model Infrastructure lintas module.

## Increment 4: Data Foundation

Tujuan:

- membuat struktur data program, target, setoran, dan revision history.

Deliverable:

- migration `tahfidz_programs`;
- migration `tahfidz_targets`;
- migration `tahfidz_submissions`;
- migration `tahfidz_submission_revisions`;
- record model dan factory minimum.

Acceptance:

- table memakai ULID;
- unique constraint menjaga kode program dan target utama baseline;
- migration aman di MySQL dengan nama index eksplisit pendek.

## Increment 5: Backend Read/List

Tujuan:

- menyediakan query list dan detail setoran/progres Tahfidz.

Deliverable:

- DTO read model;
- query list/search/filter;
- query detail;
- API read routes/resources.

Acceptance:

- list mendukung filter program, santri, pembimbing, periode, tipe, status, dan
  pagination;
- detail menampilkan target, setoran, pembimbing, dan ringkasan;
- actor tanpa `tahfidz.view` ditolak.

## Increment 6: Program dan Target Hafalan

Tujuan:

- mengelola program tahfidz dan target hafalan santri.

Deliverable:

- request validation program/target;
- action create/update program;
- action create/update target;
- audit program/target.

Acceptance:

- program aktif bisa dibuat dan diubah;
- target hanya untuk santri aktif;
- target menyimpan snapshot santri dan periode.

## Increment 7: Setoran dan Murojaah

Tujuan:

- mencatat setoran hafalan baru dan murojaah.

Deliverable:

- request validation setoran;
- action create/update submission;
- rule rentang juz/surah/ayat;
- audit setoran.

Acceptance:

- setoran/murojaah bisa dibuat sebagai draft/submitted;
- rentang ayat valid;
- setoran void/accepted tidak bisa diedit langsung tanpa jalur yang sesuai.

## Increment 8: Review, Koreksi, dan Void

Tujuan:

- mengelola lifecycle review setoran.

Deliverable:

- action review accepted/needs_revision;
- action void dengan alasan;
- revision history;
- audit lifecycle.

Acceptance:

- review hanya melalui permission `tahfidz.review`;
- void wajib alasan dan tidak menghapus data;
- koreksi/revision meninggalkan histori.

## Increment 9: Demo Seeder

Tujuan:

- menyediakan data demo lengkap untuk manual QA.

Deliverable:

- `TahfidzDemoSeeder`;
- update `DatabaseSeeder`;
- test idempotent seeder.

Acceptance:

- demo mencakup program, target, setoran accepted, submitted, needs_revision,
  dan void;
- demo memakai santri dan pembimbing demo yang sudah ada;
- seeder aman diulang dan tidak berjalan di production.

## Increment 10: UI/Inertia List dan Detail

Tujuan:

- membuat UI baca Tahfidz.

Deliverable:

- page index/detail;
- filter program/santri/pembimbing/periode/status;
- table/card responsif;
- summary cards;
- sidebar menu namespace Pesantrian.

Acceptance:

- page tetap tipis;
- route Ziggy tersedia;
- list/detail tampil di desktop dan mobile.

## Increment 11: UI Mutation

Tujuan:

- membuat UI program, target, setoran, review, dan void.

Deliverable:

- mutation dialogs;
- form fields reusable;
- review dialog;
- confirmation void.

Acceptance:

- form mutation berada di folder `components`;
- destructive/final action memakai confirmation;
- backend tetap authority permission dan rule.

## Increment 12: QA Browser dan User Manual

Tujuan:

- memastikan flow manual siap diuji user.

Deliverable:

- QA browser desktop/mobile;
- update user manual lifecycle;
- update README/tasks hasil final.

Acceptance:

- console browser bersih dari error route/Ziggy;
- flow list/detail/mutation utama bisa diuji;
- keterbatasan integrasi sertifikat, portal wali/santri, dan lampiran audio
  tercatat jelas.

## Risiko dan Guardrail

- Jangan mencampur nilai akademik formal ke Tahfidz; itu perlu boundary module
  akademik/penilaian tersendiri.
- Jangan membaca model Infrastructure Santri/HumanResource/Academic/Asrama
  secara langsung.
- Jangan membuat sertifikat, upload audio, atau portal wali/santri sebelum
  baseline pencatatan setoran stabil.
- Jangan menaruh UI di luar `resources/js/pages/Pesantrian/Tahfidz/`.
