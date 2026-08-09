# 03. Pagination, Role Efektif, dan Toolbar User

## Status

`Berjalan — Task 01 pagination selesai; Task 02 role efektif berikutnya.`

Increment ini menjalankan poin 1 sampai 3 ADR-0004 secara berurutan. Poin 1
pagination harus selesai dan diverifikasi sebelum poin 2 role efektif, lalu
poin 3 toolbar dan shortcut dimulai.

## Scope

1. Pagination server-side dengan `page` dan `per_page`.
2. Role efektif sebagai badge pada tabel dan detail user.
3. Toolbar serta shortcut filter dan pagination.

## Batasan

- Route tetap `system.users.index`; query filter lama tetap kompatibel.
- Ukuran halaman yang disetujui: `5`, `10`, `25`, dan `50`; default `25`.
- Tidak ada mutation role, restore, invitation, atau migration baru.
- Role dibaca dari relasi public yang tersedia tanpa import model private
  AccessControl.

## Dokumen

1. [Specification](specification.md)
2. [Implementation plan](implementation-plan.md)
3. [Tasks](tasks.md)
4. [Execution log](planning/execution-log.md)
5. [ADR-0004](../decisions/ADR-0004-USERMANAGEMENT-INCREMENTAL-LIST-SCOPE.md)
