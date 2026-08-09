# Specification: Bulk Lifecycle User

## Contract

| Operasi | Route | Permission | Target valid |
| --- | --- | --- | --- |
| Bulk archive | `DELETE /system/users/bulk` | `user.delete` | Aktif dan bukan `SuperSystem`. |
| Bulk force delete | `DELETE /system/users/bulk/force` | `user.force.delete` | Terarsip dan bukan `SuperSystem`. |

Payload: `{ "user_ids": ["ULID"] }`. `user_ids` unik, minimal satu, maksimal 50, dan seluruhnya ULID.

## Aturan Atomik

1. Backend memeriksa permission dan seluruh target sebelum mutation.
2. Target dibaca termasuk soft-deleted user.
3. Satu target tidak valid membatalkan seluruh operation serta memberi flash toast `error`.
4. Target valid diproses dalam satu transaksi.
5. Audit tetap dibuat per user dengan correlation ID batch yang sama.

## UI

- Checkbox per user non-`SuperSystem` dan checkbox header hanya untuk halaman aktif.
- Toolbar menampilkan jumlah pilihan dan tombol sesuai permission serta mode
  arsip. Daftar awal/aktif hanya menawarkan archive; filter `Arsip saja` hanya
  menawarkan force delete.
- Dialog konfirmasi, loading state, Ziggy, dan global Sonner wajib dipakai.

## Acceptance Criteria

- [x] Request invalid ditolak tanpa mutation.
- [x] Satu target invalid membatalkan seluruh batch serta memberi toast error.
- [x] Bulk archive hanya mengarsipkan target aktif.
- [x] Bulk force delete hanya menghapus target terarsip.
- [x] Audit per target memakai correlation ID yang sama.
- [ ] Browser console bersih dan kontrol dapat diakses keyboard.
