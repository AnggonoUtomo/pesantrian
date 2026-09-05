# Implementation Plan: Pesantrian/PresensiSantri

## Overview

`Pesantrian/PresensiSantri` akan dibangun incremental dari dokumentasi,
contract readiness, data foundation, API, seeder, UI, sampai QA browser.
Baseline awal fokus pada presensi internal/admin, belum public form dan belum
integrasi perangkat absensi.

## Prinsip Implementasi

- Mulai dari module skeleton kecil dan permission identity.
- Buat public contract hanya ketika consumer nyata membutuhkan data dari module
  lain.
- Simpan snapshot `student_no`, `student_name`, dan `context_name` agar histori
  presensi tetap terbaca meski master data berubah.
- Mutation presensi selalu melalui Application action.
- Entry presensi tidak dihapus permanen pada baseline.
- UI berada di `resources/js/pages/Pesantrian/PresensiSantri/`, dengan page
  tipis dan komponen di folder `components`.
- Seeder demo wajib dibuat bersama module.

## Dependency Graph

```text
Santri active reader
KelasRombel attendance roster reader
Asrama attendance roster reader
        |
        v
PresensiSantri module skeleton + permission
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

- generator module `Pesantrian PresensiSantri`;
- `module.json`, `module.php`, `permissions.php`, `ServiceProvider.php`;
- README source module;
- permission seeder wiring.

Acceptance:

- module discover/validate normal;
- permission `presensi_santri.*` tersedia;
- belum ada table bisnis.

## Increment 3: Contract Readiness

Tujuan:

- menyediakan contract roster/kandidat yang dibutuhkan PresensiSantri tanpa
  mengambil model Infrastructure module lain.

Deliverable:

- contract pada owner module bila belum tersedia:
  - Santri aktif untuk selector umum;
  - anggota rombel aktif dari KelasRombel;
  - penghuni asrama/kamar aktif dari Asrama;
- tests contract readiness.

Acceptance:

- PresensiSantri dapat mengambil kandidat santri dari class group, dormitory,
  atau selector umum melalui contract;
- tidak ada import model Infrastructure lintas module.

## Increment 4: Data Foundation

Tujuan:

- membuat struktur data presensi.

Deliverable:

- migration `student_attendance_sessions`;
- migration `student_attendance_entries`;
- migration `student_attendance_revisions`;
- record model dan factory minimum.

Acceptance:

- table memakai ULID;
- unique constraint mencegah duplikasi session/entry;
- migration aman di MySQL dengan nama index eksplisit pendek.

## Increment 5: Backend Read/List

Tujuan:

- menyediakan query list dan detail presensi.

Deliverable:

- DTO read model;
- query list/search/filter;
- query detail;
- API read routes/resources.

Acceptance:

- list mendukung filter tanggal, konteks, status, dan pagination;
- detail menampilkan entries dan summary;
- actor tanpa `presensi_santri.view` ditolak.

## Increment 6: Create/Update Draft dan Entry

Tujuan:

- membuat sesi draft dan mengisi entry presensi.

Deliverable:

- request validation create/update session;
- action create/update draft;
- action update entries;
- audit create/update.

Acceptance:

- hanya santri aktif yang bisa menjadi entry;
- status terlambat wajib menit keterlambatan;
- sesi submitted/void tidak bisa diedit langsung.

## Increment 7: Submit, Revisi, dan Void

Tujuan:

- mengelola lifecycle sesi presensi.

Deliverable:

- action submit;
- action revise;
- action void/archive;
- audit lifecycle.

Acceptance:

- submit mengunci sesi draft;
- revisi wajib alasan;
- void wajib alasan dan tidak menghapus data.

## Increment 8: Demo Seeder

Tujuan:

- menyediakan data demo lengkap untuk manual QA.

Deliverable:

- `PresensiSantriDemoSeeder`;
- update `DatabaseSeeder`;
- test idempotent seeder.

Acceptance:

- demo mencakup sesi kelas, asrama, dan kegiatan umum;
- demo memiliki variasi hadir/izin/sakit/alfa/terlambat;
- seeder aman diulang dan tidak berjalan di production.

## Increment 9: UI/Inertia List dan Detail

Tujuan:

- membuat UI baca presensi.

Deliverable:

- page index/detail;
- filter tanggal/konteks/status;
- table/card responsif;
- summary cards;
- sidebar menu namespace Pesantrian.

Acceptance:

- page tetap tipis;
- route Ziggy tersedia;
- list/detail tampil di desktop dan mobile.

## Increment 10: UI Mutation

Tujuan:

- membuat UI create/update, entry editor, submit, revisi, dan void.

Deliverable:

- mutation dialogs;
- form fields reusable;
- entry status editor;
- confirmation submit/revise/void.

Acceptance:

- form mutation berada di folder `components`;
- destructive/final action memakai confirmation;
- backend tetap authority permission dan rule.

## Increment 11: QA Browser dan User Manual

Tujuan:

- memastikan flow manual siap diuji user.

Deliverable:

- QA browser desktop/mobile;
- update user manual lifecycle;
- update README/tasks hasil final.

Acceptance:

- console browser bersih dari error route/Ziggy;
- flow list/detail/mutation utama bisa diuji;
- keterbatasan integrasi PerizinanSantri tercatat jelas.

## Risiko dan Guardrail

- Jangan mencampur approval izin ke PresensiSantri; itu milik
  `PerizinanSantri`.
- Jangan membuat jadwal pelajaran detail di PresensiSantri.
- Jangan otomatis membuat pelanggaran kedisiplinan dari alfa/terlambat sebelum
  module KedisiplinanSantri disepakati.
- Jangan membaca model Infrastructure Santri/KelasRombel/Asrama secara
  langsung.
- Jangan menaruh UI di luar `resources/js/pages/Pesantrian/PresensiSantri/`.
