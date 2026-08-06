# 01. Filter Daftar User

## Status

`Selesai — quality checkpoint lulus.`

Increment ini menambahkan filter daftar user untuk `status`, `role`, dan arsip
(`archive`) di samping pencarian yang sudah ada. Perubahan hanya untuk daftar
user pada UserManagement, bukan lifecycle user lain.

## Boundary Module

- Parent boundary: `System`.
- Owner module: `App\\Modules\\System\\UserManagement`.
- Route yang diperluas: `GET /system/users` (`system.users.index`).
- Owner data dan query: UserManagement.
- Catalog role: AccessControl melalui public contract
  `RoleCatalogCapability`; tidak ada import model atau repository private.

## Ruang Lingkup

- Filter server-side untuk nama/email, status, role, dan arsip.
- Toolbar filter pada tabel UserManagement yang mengikuti visual System.
- Query URL Inertia yang dapat dibagikan atau dimuat ulang.
- Focused test backend dan browser untuk hasil serta kondisi tanpa hasil.

## Di Luar Scope

- Pagination, ukuran halaman, sorting, restore user, force delete, dan
  multi-role management.
- Route API baru, schema database, migration, serta perubahan permission.

## Urutan Baca

1. [Specification](specification.md)
2. [Implementation plan](implementation-plan.md)
3. [Tasks](tasks.md)
4. [Execution log](planning/execution-log.md)

## Dokumen Terkait

- [UserManagement](../README.md)
- [Pola komunikasi dan eksekusi module](../../../../03-IMPLEMENTATION/03.12-MODULE-COMMUNICATION-AND-EXECUTION.md)
- [Aturan project](../../../../AGENTS.md)

## Revision History

| Versi | Tanggal | Perubahan |
| --- | --- | --- |
| 1.0 | 2026-08-06 | Membuat dokumen draft untuk filter daftar user poin 1. |
| 1.1 | 2026-08-06 | Mencatat implementasi backend/frontend, test, build, dan browser verification. |
