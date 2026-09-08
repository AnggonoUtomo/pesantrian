# Implementation Plan: Pesantrian/KedisiplinanSantri

## Overview

`Pesantrian/KedisiplinanSantri` akan dibangun incremental dari dokumentasi,
skeleton, contract readiness, data foundation, API, seeder, UI, sampai QA
browser. Baseline awal fokus pada flow internal/admin: catat pelanggaran,
submit, review, tentukan tindak lanjut, resolve, dan void.

## Prinsip Implementasi

- Mulai dari module skeleton kecil dan permission identity.
- Gunakan public contract module lain untuk santri aktif dan petugas aktif.
- Simpan snapshot `student_no`, `student_name`, `category_name`, dan
  `assigned_employee_name` agar histori tetap terbaca saat master data berubah.
- Mutation kasus kedisiplinan selalu melalui Application action.
- Catatan kedisiplinan tidak dihapus permanen pada baseline.
- Jangan otomatis membuat kasus dari PresensiSantri atau PerizinanSantri.
- Jangan mencampur konseling mendalam ke KedisiplinanSantri; itu akan menjadi
  module PembinaanSantri.
- UI berada di `resources/js/pages/Pesantrian/KedisiplinanSantri/`, dengan page
  tipis dan komponen di folder `components`.
- Seeder demo wajib dibuat bersama module.

## Dependency Graph

```text
Santri active reader
HumanResource active employee reader
        |
        v
KedisiplinanSantri module skeleton + permission
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
Optional read-only integration from Presensi/Perizinan
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

- generator module `Pesantrian KedisiplinanSantri`;
- `module.json`, `module.php`, `permissions.php`, `ServiceProvider.php`;
- README source module;
- permission seeder wiring.

Acceptance:

- module discover/validate normal;
- permission `kedisiplinan_santri.*` tersedia;
- belum ada table bisnis.

## Increment 3: Contract Readiness

Tujuan:

- memastikan KedisiplinanSantri bisa mengambil data pendukung tanpa membaca
  model Infrastructure module lain.

Deliverable:

- audit contract `ActiveStudentReader`;
- audit contract `ActiveEmployeeReader`;
- tests contract readiness;
- dokumentasi batas integrasi PresensiSantri dan PerizinanSantri.

Acceptance:

- KedisiplinanSantri dapat mengambil kandidat santri aktif dan pembina/petugas
  aktif melalui contract;
- tidak ada import model Infrastructure lintas module;
- integrasi otomatis dari Presensi/Perizinan tetap belum dibuat.

## Increment 4: Data Foundation

Tujuan:

- membuat struktur data kategori, kasus, dan revision history.

Deliverable:

- migration `student_discipline_categories`;
- migration `student_discipline_cases`;
- migration `student_discipline_revisions`;
- record model dan factory minimum.

Acceptance:

- table memakai ULID;
- unique constraint menjaga kode kategori dan nomor kasus;
- constraint/index membantu filter santri, tanggal, status, severity, dan
  kategori;
- migration aman di MySQL dengan nama index eksplisit pendek.

## Increment 5: Backend Read/List

Tujuan:

- menyediakan query list dan detail kedisiplinan.

Deliverable:

- DTO read model;
- query list/search/filter;
- query detail;
- API read routes/resources.

Acceptance:

- list mendukung filter tanggal, status, severity, kategori, santri, pembina,
  dan pagination;
- detail menampilkan lifecycle, snapshot santri, kategori, tindakan, resolution,
  dan revision history;
- actor tanpa `kedisiplinan_santri.view` ditolak.

## Increment 6: Category Management

Tujuan:

- mengelola kategori pelanggaran baseline.

Deliverable:

- request validation kategori;
- action create/update/archive kategori;
- API mutation kategori;
- audit kategori.

Acceptance:

- kategori aktif bisa dibuat/diubah;
- kategori archived tidak muncul sebagai pilihan kasus baru;
- kode kategori unique;
- archive kategori tidak menghapus histori kasus lama.

## Increment 7: Create/Update Draft dan Submit Case

Tujuan:

- membuat catatan pelanggaran dan submit untuk penanganan.

Deliverable:

- request validation create/update case;
- action create/update draft;
- action submit;
- audit create/update/submit.

Acceptance:

- hanya santri aktif yang bisa dibuatkan kasus baru;
- kategori aktif wajib dipilih;
- severity valid dan poin tidak negatif;
- submit hanya dari draft.

## Increment 8: Review dan Assign Action

Tujuan:

- mengelola review dan tindakan pembinaan/sanksi edukatif.

Deliverable:

- action review;
- action assign action;
- request validation catatan review/tindakan;
- audit review/action.

Acceptance:

- review hanya dari submitted;
- tindakan pembinaan wajib berisi catatan yang jelas;
- actor, waktu, dan catatan tersimpan.

## Increment 9: Resolve dan Void

Tujuan:

- menutup kasus kedisiplinan dengan aman.

Deliverable:

- action resolve;
- action void;
- revision history;
- audit resolve/void.

Acceptance:

- resolve wajib catatan penyelesaian;
- void wajib alasan dan tidak menghapus data;
- resolved/void menjadi status final baseline.

## Increment 10: Demo Seeder

Tujuan:

- menyediakan data demo lengkap untuk manual QA.

Deliverable:

- `KedisiplinanSantriDemoSeeder`;
- update `DatabaseSeeder`;
- test idempotent seeder.

Acceptance:

- demo mencakup kategori dan kasus draft/submitted/in_review/action_assigned/
  resolved/void;
- demo memakai santri dan petugas demo yang sudah ada;
- seeder aman diulang dan tidak berjalan di production.

## Increment 11: UI/Inertia List dan Detail

Tujuan:

- membuat UI baca kedisiplinan.

Deliverable:

- page index/detail;
- filter tanggal/status/severity/kategori/search;
- table/card responsif;
- summary cards;
- sidebar menu namespace Pesantrian.

Acceptance:

- page tetap tipis;
- route Ziggy tersedia;
- list/detail tampil di desktop dan mobile.

## Increment 12: UI Mutation

Tujuan:

- membuat UI create/update, submit, review, assign action, resolve, dan void.

Deliverable:

- mutation dialogs;
- form fields reusable;
- review/action/resolution dialog;
- confirmation void.

Acceptance:

- form mutation berada di folder `components`;
- destructive/final action memakai confirmation;
- backend tetap authority permission dan rule.

## Increment 13: QA Browser dan User Manual

Tujuan:

- memastikan flow manual siap diuji user.

Deliverable:

- QA browser desktop/mobile;
- update user manual lifecycle;
- update README/tasks hasil final.

Acceptance:

- console browser bersih dari error route/Ziggy;
- flow list/detail/mutation utama bisa diuji;
- keterbatasan integrasi Presensi/Perizinan/Pembinaan tercatat jelas.

## Increment 14: Integrasi Read-only dari Presensi dan Perizinan

Tujuan:

- menyediakan kandidat kasus dari sumber operasional yang sudah ada tanpa
  otomatis membuat pelanggaran.

Deliverable:

- evaluasi public contract PresensiSantri untuk rekap alfa/terlambat;
- evaluasi public contract PerizinanSantri untuk keterlambatan kembali;
- query kandidat kasus bila consumer flow nyata sudah disetujui;
- test consumer readiness.

Acceptance:

- KedisiplinanSantri tidak membaca model Infrastructure Presensi/Perizinan;
- kandidat kasus tidak otomatis menjadi pelanggaran tanpa review manusia;
- pencatatan manual tetap tersedia.

## Risiko dan Guardrail

- Jangan membuat KedisiplinanSantri sebagai catatan bebas tanpa kategori/status.
- Jangan otomatis menghukum santri dari presensi/perizinan tanpa review manusia.
- Jangan mencampur konseling sensitif ke module ini.
- Jangan membuat denda/tagihan dari poin pelanggaran pada baseline.
- Jangan membaca model Infrastructure Santri/HumanResource/Presensi/Perizinan
  secara langsung.
- Jangan menaruh UI di luar `resources/js/pages/Pesantrian/KedisiplinanSantri/`.
