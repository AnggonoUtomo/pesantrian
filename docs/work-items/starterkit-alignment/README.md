# Starterkit Alignment

Work item ini menyiapkan starterkit agar selaras dengan baseline SakaSantri
sebelum module baru dibuat secara incremental.

## Scope

- Audit kondisi module starterkit aktual sebagai bridge dari `System/*` menuju
  baseline `Console/*`.
- Selaraskan generator module agar memakai istilah baseline `namespace` dan
  tidak membuat folder opsional sebagai placeholder.
- Pertahankan kompatibilitas command lama selama consumer route, permission,
  Inertia page, test, dan migration masih memakai identifier `System`.

## Acceptance Criteria

- Command artisan existing tetap normal.
- Generator menerima namespace/module sesuai baseline.
- `--domain` tetap tersedia sebagai alias kompatibilitas sampai migrasi
  identifier disepakati.
- Skeleton default tidak membuat folder kosong tanpa isi nyata.
- Gap `System` ke `Console` terdokumentasi, bukan diselesaikan dengan rename
  massal tanpa audit consumer.
- Panduan pembuatan module baru tersedia di
  [`module-generation.md`](module-generation.md).

## Verifikasi

- `php artisan module:list --no-ansi`
- `php artisan module:validate --no-ansi`
- Focused test generator:
  - `php artisan test --filter=ModuleGenerationRequestTest`
  - `php artisan test --filter=DefaultModuleProfileTest`
  - `php artisan test --filter=ModuleMakeCommandTest`
- `git diff --check`

## Hasil

- Task 1 audit foundation selesai.
- Task 2 generator baseline selesai.
- Task 3 dokumentasi pembuatan module baru selesai.
