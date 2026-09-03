# Plan: Demo Seeders Lintas Module

## Increment 1: Guard dan Test

- Tambahkan test global seeder untuk membuktikan data demo lintas module dibuat
  dan idempotent.
- Pastikan data demo tidak dibuat di environment `production`.

## Increment 2: Seeder Bisnis

- Tambahkan seeder per module bisnis.
- Panggil semua seeder dari `DatabaseSeeder` setelah System seeders.
- Gunakan natural key (`code`, `employee_no`, `registration_no`,
  `student_no`) agar `db:seed` aman dijalankan berulang.

## Increment 3: Dokumentasi dan Verifikasi

- Update workflow/template supaya module baru wajib membawa demo seeder bila
  memiliki data operasional.
- Jalankan test seeder, `db:seed`, `module:validate`, dan hygiene diff.

## Increment 4: Manual Lifecycle dan Role Operator Demo

- Tambahkan user manual lifecycle lintas module untuk uji manual operator.
- Lengkapi role demo berbasis pekerjaan harian: PPDB, Santri, Akademik, SDM,
  Auditor, dan Viewer.
- Pastikan permission role demo mencakup relasi antar module aktif tanpa
  memberi akses mutation di luar kebutuhan role.

## Rollback

Rollback kode melalui commit. Untuk data lokal demo, hapus record dengan prefix:

- `DEMO-`
- `PEG-DEMO-`
- `PPDB-DEMO-`
- `NIS-DEMO-`
- email dummy `user-management-dummy-*`
