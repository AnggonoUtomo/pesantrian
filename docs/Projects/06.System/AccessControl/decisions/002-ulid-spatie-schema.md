# ADR-002: Schema Spatie Permission menggunakan ULID

Status: Diterima  
Tanggal: 2026-08-05  
Owner: AccessControl

## Keputusan

Schema `spatie/laravel-permission` menggunakan ULID untuk ID role, permission,
dan kolom pivot. Schema starter kit yang menjadi dependency terkait juga
diselaraskan ke ULID.

Model `Role` dan `Permission` milik AccessControl membungkus model Spatie dan
menggunakan `HasUlids`. Migration permission disimpan di dalam module dan
dimuat oleh `AccessControl\ServiceProvider`.

## Alasan

Baseline project menetapkan semua primary key dan foreign key menggunakan ULID.
Migration bawaan Spatie menggunakan `bigint`, sehingga tidak boleh dipakai
langsung karena akan membuat schema campuran.

## Dampak

- Migration fresh-install memakai ULID pada `users`, `passkeys`, `jobs`, dan
  tabel permission.
- `User` menggunakan `HasUlids` dan `HasRoles`.
- Database yang sudah berjalan dengan integer memerlukan migration upgrade
  khusus sebelum perubahan ini dipakai pada environment bersama.

## Verifikasi

- `php artisan test tests/Feature/AccessControlSchemaTest.php` lulus.
- Test membuktikan tipe string ULID, relasi role, dan foreign key pivot.
- `php artisan migrate:status` mendeteksi migration permission module.

## Risiko tersisa

Runbook upgrade untuk database existing tersedia pada `upgrade-runbook.md`.
Eksekusi shared environment tetap memerlukan backup, rehearsal, downtime, dan
approval release karena database tersebut belum tersedia pada sesi kerja ini.
