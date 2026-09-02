# Tasks: Demo Seeders Lintas Module

## Sebelum Mulai

- [x] Scope dan non-scope ditentukan.
- [x] Dependency antar module diketahui.
- [x] Focused test ditentukan.
- [x] `docs/README.md`, `docs/ARCHITECTURE.md`, dan
  `docs/FOLDER-STRUCTURE.md` sudah dibaca.

## Pekerjaan

- [x] Tambahkan test seeder lintas module.
  - Acceptance: `DatabaseSeeder` membuat data demo dan idempotent.
  - Verification:
    `php artisan test tests\Feature\BusinessDemoSeederTest.php --no-ansi`.
- [x] Tambahkan seeder demo Organization.
  - Acceptance: tersedia struktur unit aktif dan nonaktif.
- [x] Tambahkan seeder demo AcademicPeriod.
  - Acceptance: tersedia periode `draft`, `active`, dan `closed`.
- [x] Tambahkan seeder demo HumanResource.
  - Acceptance: tersedia pegawai aktif/nonaktif dan assignment unit.
- [x] Tambahkan seeder demo PenerimaanSantri.
  - Acceptance: tersedia status lifecycle PPDB utama.
- [x] Tambahkan seeder demo Santri.
  - Acceptance: tersedia status aktif, nonaktif, pindah, lulus, dan arsip.
- [x] Update workflow/template agar module baru dengan data operasional wajib
  membawa demo seeder idempotent.

## Hasil

- [x] Scope selesai.
  - Perubahan: seeder demo bisnis lintas module dan dokumentasi workflow.
  - Verification: lihat handoff kerja.
  - Risiko terbuka: data demo hanya untuk non-production; jangan menjalankan
    seeder demo pada database produksi.
