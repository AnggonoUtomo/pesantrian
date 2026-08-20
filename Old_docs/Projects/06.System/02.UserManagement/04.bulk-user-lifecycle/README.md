# 04. Bulk Lifecycle User

## Status

`Berjalan — keputusan atomik disetujui.`

Increment ini menambahkan bulk soft delete dan bulk force delete pada boundary `System/UserManagement`.

## Keputusan

Semua target harus valid sebelum mutation. Jika satu target tidak ditemukan, protected, atau tidak sesuai state lifecycle, seluruh operasi dibatalkan. Backend mengirim toast error dan tidak ada user berubah.

## Dokumen

1. [Specification](specification.md)
2. [Implementation plan](implementation-plan.md)
3. [Tasks](tasks.md)
4. [Execution log](planning/execution-log.md)
