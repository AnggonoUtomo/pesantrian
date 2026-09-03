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
- [x] Lengkapi role dan user operator demo.
  - Acceptance: tersedia role PPDB, Santri, Akademik, SDM, Auditor, dan Viewer
    dengan permission sesuai scope uji manual.
- [x] Buat user manual lifecycle lintas module.
  - Acceptance: user pemula bisa mengikuti urutan uji dari System,
    Organisasi, Academic, SDM, PPDB, Santri, sampai Kelas/Rombel.

## Hasil

- [x] Scope selesai.
  - Perubahan: seeder demo bisnis lintas module, role/user operator demo,
    dokumentasi workflow, dan user manual lifecycle.
  - Verification: lihat handoff kerja.
  - Risiko terbuka: data demo hanya untuk non-production; jangan menjalankan
    seeder demo pada database produksi. Browser/manual QA tetap perlu dijalankan
    oleh user dengan password demo lokal.
