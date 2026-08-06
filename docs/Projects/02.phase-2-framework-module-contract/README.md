# Project: Phase 2 Framework dan Module Contract

## Konteks Project

| Item | Nilai |
|---|---|
| Slug | phase-2-framework-module-contract |
| Source Repository | C:/laragon/www/starter13 |
| Mode | Existing Starter Kit |
| Laravel | 13.23.0 |
| Status | Discovery |
| Owner | Fondasi Framework |

## Tujuan

Membuat fondasi reusable pada `packages/StarterKit` dan menetapkan contract
module sebelum module generator dibangun pada Phase 3.

## Ruang Lingkup

- Membuat Composer package `packages/StarterKit`.
- Menetapkan schema manifest `module.json`.
- Menetapkan runtime configuration `module.php`.
- Menetapkan permission identity `permissions.php`.
- Membuat registry, discovery, dan validation dasar.
- Menyediakan command `module:discover`, `module:validate`, dan `module:list`.
- Menambahkan test contract dan dokumentasi evidence.

## Di Luar Ruang Lingkup

- `module:make` dan profile generator.
- Module `AccessControl` atau module bisnis lain.
- CRUD, API generator, dan frontend generator.
- Sinkronisasi permission ke database.

## Prasyarat

- Phase 1 Starter Foundation selesai.
- Laravel 13, MySQL, Redis, Predis, Ziggy, dan Spatie Permission tersedia.
- Tidak ada module valid pada `app/Modules` yang boleh tertimpa.

## Aturan Kerja

Setiap task wajib mencatat kondisi awal, file/path, perubahan, alasan, evidence,
dan risiko. Checklist ditinjau sebelum dan sesudah pekerjaan.
