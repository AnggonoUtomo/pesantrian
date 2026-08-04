# Spesifikasi Phase 1 Starter Foundation

## Kebutuhan

| ID      | Kebutuhan                             | Prioritas | Kriteria Penerimaan                                                         |
| ------- | ------------------------------------- | --------- | --------------------------------------------------------------------------- |
| REQ-001 | Versi Laravel dan PHP sesuai baseline | Wajib     | Laravel 13 dan PHP 8.4+ terdeteksi                                          |
| REQ-002 | Stack frontend utama tersedia         | Wajib     | React, Inertia, TypeScript, Vite, Tailwind, dan Ziggy terpasang             |
| REQ-003 | Dependency terlarang tidak digunakan  | Wajib     | Wayfinder dan Laravel Boost tidak ditemukan                                 |
| REQ-004 | Runtime service terverifikasi         | Wajib     | Database, Redis, cache, queue, session, storage, dan ULID memiliki evidence |
| REQ-005 | Quality gate foundation lulus         | Wajib     | Test, lint, type check, build, dan PHP format lulus                         |
| REQ-006 | Verification dapat diulang            | Wajib     | `starter:verify` tersedia atau gap-nya terdokumentasi jelas                 |

## Batasan

- Backend tetap menjadi security authority.
- Ziggy hanya digunakan untuk membuat URL frontend.
- Tidak ada secret atau credential dalam source dan output diagnostic.
- Tidak ada module bisnis pada Phase 1.

## Keputusan Terbuka

| ID     | Pertanyaan                                                    | Dampak                                     | Status                                                                          |
| ------ | ------------------------------------------------------------- | ------------------------------------------ | ------------------------------------------------------------------------------- |
| OD-001 | Apakah PostgreSQL dan Redis lokal aktif pada environment ini? | Menentukan hasil health check runtime      | Diputuskan: aktif dan terverifikasi                                             |
| OD-002 | Package Spatie mana yang wajib untuk foundation?              | Menentukan package baseline sebelum module | Diputuskan: Permission wajib; Media Library hanya saat module membutuhkan media |
