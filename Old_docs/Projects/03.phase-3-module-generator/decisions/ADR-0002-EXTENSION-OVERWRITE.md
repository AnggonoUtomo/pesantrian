# ADR-0002: Mode Extension dan Overwrite Generator

## Status

Diterima.

## Context

Generator awal hanya membuat module baru dan menolak target yang sudah ada.
Kebutuhan pengembangan sekarang memerlukan generator untuk melengkapi module
existing dan memperbarui file yang memang dikelola profile generator.

## Decision

Generator menyediakan dua mode eksplisit:

1. `--extension` mengizinkan target module existing.
2. Tanpa `--overwrite`, extension hanya membuat file yang belum ada.
3. `--overwrite` hanya boleh dipakai bersama `--extension`, `--force`, dan
   `--yes`; file yang ditulis ulang hanya file dalam plan generator.
4. Promotion overwrite membuat backup file existing pada staging ULID sebelum
   menulis. Jika proses gagal, backup dipulihkan.
5. File existing di luar plan tidak disentuh.
6. Mode default tetap fail-safe dan tidak mengubah module existing.
7. Developer wajib menjalankan dan meninjau `--dry-run --json` sebelum
   menjalankan overwrite.
8. Sebelum extension atau overwrite, generator memeriksa compatibility
   namespace, manifest, profile, struktur target, dan status registry.
9. Generator tidak melakukan merge otomatis pada business logic atau file
   custom module.

## Consequences

- Module existing dapat dilengkapi secara bertahap.
- Overwrite memerlukan niat eksplisit dan backup/restore.
- File custom di luar output profile tetap aman.
- Pemilik module harus meninjau dry-run sebelum menjalankan overwrite.
- Rollback perubahan yang sudah berhasil tetap dilakukan melalui Git.
- Backup staging hanya digunakan untuk memulihkan promotion yang gagal.

## Command Contract

```bash
php artisan module:make AuditLog --domain=System --extension --force --yes --json
php artisan module:make AuditLog --domain=System --extension --overwrite --force --yes --json
```

`--overwrite` tanpa `--extension`, `--force`, atau `--yes` ditolak sebelum
filesystem berubah.

Overwrite hanya berlaku pada file yang dikelola profile generator. File business
logic, migration, test, route, atau file custom tidak boleh di-merge otomatis.

## Rollback

Backup hanya berada selama proses promotion. Jika promotion gagal, backup
dipulihkan dan staging dibersihkan. Jika promotion berhasil, backup staging
dihapus.
