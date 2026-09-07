# Implementation Plan: Pesantrian/PerizinanSantri

## Overview

`Pesantrian/PerizinanSantri` akan dibangun incremental dari dokumentasi,
skeleton, contract readiness, data foundation, API, seeder, UI, sampai QA
browser. Baseline awal fokus pada flow internal/admin: draft izin, submit,
approve/reject, check-out, return, dan void.

## Prinsip Implementasi

- Mulai dari module skeleton kecil dan permission identity.
- Gunakan public contract module lain untuk santri aktif dan petugas aktif.
- Simpan snapshot `student_no`, `student_name`, `guardian_name`, dan
  `guardian_phone` agar histori izin tetap terbaca meski master data berubah.
- Mutation izin selalu melalui Application action.
- Izin tidak dihapus permanen pada baseline.
- Jangan otomatis membuat presensi, pelanggaran, tagihan, atau rekam medis dari
  izin pada baseline.
- UI berada di `resources/js/pages/Pesantrian/PerizinanSantri/`, dengan page
  tipis dan komponen di folder `components`.
- Seeder demo wajib dibuat bersama module.

## Dependency Graph

```text
Santri active reader
HumanResource active employee reader
Wali snapshot from Santri/admission data
        |
        v
PerizinanSantri module skeleton + permission
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
        |
        v
Optional integration contract for PresensiSantri
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

- boundary, dependency, permission, lifecycle, data candidate, dan open
  questions tercatat;
- roadmap incremental tersedia;
- belum ada source code module yang dibuat.

## Increment 2: Module Skeleton dan Permission

Tujuan:

- membuat source module minimum dan permission identity.

Deliverable:

- generator module `Pesantrian PerizinanSantri`;
- `module.json`, `module.php`, `permissions.php`, `ServiceProvider.php`;
- README source module;
- permission seeder wiring.

Acceptance:

- module discover/validate normal;
- permission `perizinan_santri.*` tersedia;
- belum ada table bisnis.

## Increment 3: Contract Readiness

Tujuan:

- memastikan PerizinanSantri bisa mengambil data pendukung tanpa membaca model
  Infrastructure module lain.

Deliverable:

- audit contract `ActiveStudentReader`;
- audit contract `ActiveEmployeeReader`;
- strategi snapshot wali dari data yang sudah tersedia;
- contract tambahan `PrimaryStudentGuardianReader` di module Santri karena
  PerizinanSantri membutuhkan snapshot wali dan contract khusus belum tersedia;
- tests contract readiness.

Acceptance:

- PerizinanSantri dapat mengambil kandidat santri aktif dan petugas aktif
  melalui contract;
- PerizinanSantri dapat mengambil snapshot wali utama tanpa master
  `WaliSantri`;
- tidak ada import model Infrastructure lintas module.

## Increment 4: Data Foundation

Tujuan:

- membuat struktur data izin dan revision history.

Deliverable:

- migration `student_permits`;
- migration `student_permit_revisions`;
- record model dan factory minimum.

Acceptance:

- table memakai ULID;
- unique constraint menjaga nomor izin;
- constraint/index membantu filter santri, tanggal, status, dan jenis izin;
- migration aman di MySQL dengan nama index eksplisit pendek.

## Increment 5: Backend Read/List

Tujuan:

- menyediakan query list dan detail perizinan.

Deliverable:

- DTO read model;
- query list/search/filter;
- query detail;
- API read routes/resources.

Acceptance:

- list mendukung filter tanggal, jenis izin, status, santri, terlambat, dan
  pagination;
- detail menampilkan lifecycle, snapshot santri/wali, approval, check-out,
  return, dan revision history;
- actor tanpa `perizinan_santri.view` ditolak.

## Increment 6: Create/Update Draft dan Submit

Tujuan:

- membuat permohonan izin dan submit untuk review.

Deliverable:

- request validation create/update permit;
- action create/update draft;
- action submit;
- audit create/update/submit.

Acceptance:

- hanya santri aktif yang bisa dibuatkan izin;
- rentang waktu valid;
- izin aktif tidak overlap untuk santri yang sama;
- submit hanya dari draft.

## Increment 7: Approve dan Reject

Tujuan:

- mengelola keputusan izin.

Deliverable:

- action approve;
- action reject;
- request validation alasan/catatan keputusan;
- audit decision.

Acceptance:

- approve/reject hanya dari submitted;
- reject wajib alasan;
- decision menyimpan actor, waktu, dan catatan.

## Increment 8: Check-out, Return, dan Void

Tujuan:

- mengelola lifecycle operasional setelah izin disetujui.

Deliverable:

- action check-out;
- action return/check-in;
- action void;
- revision history;
- audit lifecycle.

Acceptance:

- check-out hanya dari approved;
- return hanya dari checked_out;
- return terlambat bisa ditandai di read model;
- void wajib alasan dan tidak menghapus data.

## Increment 9: Demo Seeder

Tujuan:

- menyediakan data demo lengkap untuk manual QA.

Deliverable:

- `PerizinanSantriDemoSeeder`;
- update `DatabaseSeeder`;
- test idempotent seeder.

Acceptance:

- demo mencakup draft/submitted/approved/rejected/checked_out/returned/void;
- demo memakai santri dan petugas demo yang sudah ada;
- seeder aman diulang dan tidak berjalan di production.

## Increment 10: UI/Inertia List dan Detail

Tujuan:

- membuat UI baca perizinan.

Deliverable:

- page index/detail;
- filter tanggal/status/jenis izin/search;
- table/card responsif;
- summary cards;
- sidebar menu namespace Pesantrian.

Acceptance:

- page tetap tipis;
- route Ziggy tersedia;
- list/detail tampil di desktop dan mobile.

## Increment 11: UI Mutation

Tujuan:

- membuat UI create/update, submit, approve/reject, check-out, return, dan void.

Deliverable:

- mutation dialogs;
- form fields reusable;
- decision dialog;
- confirmation check-out/return/void.

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
- keterbatasan integrasi Presensi/Kesehatan/Kedisiplinan tercatat jelas.

## Increment 13: Integrasi Awal ke PresensiSantri

Tujuan:

- menyediakan jembatan read-only agar PresensiSantri bisa melihat izin approved
  bila dibutuhkan.

Deliverable:

- public contract read-only izin approved;
- DTO sederhana untuk status izin santri per tanggal;
- test consumer readiness;
- dokumentasi batas integrasi.

Acceptance:

- PresensiSantri tidak membaca model Infrastructure PerizinanSantri;
- izin tidak otomatis mengubah presensi tanpa keputusan eksplisit;
- fallback manual PresensiSantri tetap tersedia.

## Risiko dan Guardrail

- Jangan membuat approval bertingkat kompleks sebelum flow satu-level terbukti.
- Jangan mencampur rekam medis klinik ke PerizinanSantri.
- Jangan otomatis membuat pelanggaran atau denda dari keterlambatan kembali.
- Jangan membaca model Infrastructure Santri/HumanResource/Presensi secara
  langsung.
- Jangan menaruh UI di luar `resources/js/pages/Pesantrian/PerizinanSantri/`.
