# ADR-0003: Archive, Force Delete, dan Perlindungan User

## Status

`Accepted.`

## Tanggal

2026-08-06

## Context

UserManagement saat ini memakai dua state berbeda:

- `status`: lifecycle akun `active`, `inactive`, atau `suspended`.
- `deleted_at`: tanda user sudah diarsipkan melalui soft delete.

Soft delete saat ini tidak mengubah `status`. Karena itu user dapat terlihat
sebagai `active` ketika sedang berada pada filter arsip. Ini benar secara data
karena lifecycle dan arsip adalah dua hal berbeda, tetapi belum jelas secara UI
dan belum ada keputusan tertulis tentang perilaku restore atau force delete.

Temuan lain:

- UI user terarsip masih menawarkan aksi `Arsipkan`, padahal `destroy` hanya
  mendukung soft delete dan akan gagal untuk record yang sudah terhapus.
- Force delete belum memiliki permission, policy, action, repository method,
  route, audit action, dialog, atau test.
- `isProtected` berarti user memiliki role `SuperSystem`. UI menyembunyikan
  sebagian mutation, tetapi backend `UpdateUser` dan `AssignUserRole` belum
  memiliki guard target `SuperSystem` yang setara dengan soft delete,
  perubahan status, dan impersonation.

## Open Decision

### 1. Semantik arsip dan status

Usulan: **arsip tidak otomatis mengubah `status`**.

`deleted_at` menjadi state dominan untuk ketersediaan user, sedangkan `status`
menyimpan lifecycle terakhir sebelum arsip. Pada daftar arsip, UI menampilkan
badge utama `Diarsipkan` dan dapat menampilkan status sebelumnya sebagai
informasi sekunder. Saat restore nanti dibuat, status lama tetap dipulihkan
tanpa perlu mapping tambahan.

Alternatif auto nonaktif ditolak sementara karena akan menimpa status
`suspended` atau `inactive` yang mungkin sudah bermakna. Restore juga tidak
dapat mengetahui status asal tanpa field tambahan seperti `status_before_archive`.

### 2. Lifecycle user terarsip

Jika force delete disetujui, user terarsip hanya dapat menerima dua aksi:

1. Restore user — capability terpisah yang belum dibuat.
2. Force delete — penghapusan permanen yang memerlukan permission terpisah,
   konfirmasi eksplisit, audit, dan perlindungan `SuperSystem`.

Tidak boleh lagi menampilkan aksi soft delete pada record yang sudah diarsipkan.

### 3. Permission dan policy force delete

Permission force delete: `user.force.delete` dengan status `sensitive: true`.

Policy `forceDelete()` hanya mengizinkan actor yang memiliki permission
tersebut terhadap target yang sudah terarsip dan bukan `SuperSystem`. Controller
middleware, Application Action, repository adapter, audit event, dan frontend
harus memakai aturan yang sama.

Restore memakai permission terpisah `user.restore` dengan status `sensitive:
true`. Restore hanya diizinkan untuk user yang sedang terarsip dan bukan
`SuperSystem`. Restore serta force delete wajib menerbitkan audit activity
terpisah agar pemberian akses kedua capability tidak saling membuka akses.

### 4. Invariant perlindungan SuperSystem

`isProtected=true` berarti target memiliki role `SuperSystem`, bukan role biasa
yang kebetulan tidak dapat diubah oleh UI.

Seluruh mutation administratif terhadap target ini ditolak server-side:
update profil administratif, status, role assignment/revoke, soft delete,
restore, force delete, dan impersonation. View tetap diizinkan untuk actor
yang memiliki `user.view`.

## Consequences jika Disetujui

- `status` tidak berubah ketika soft delete; tampilan arsip diperjelas.
- Force delete dan restore dibuat sebagai increment terpisah dengan test
  positive/negative, audit, dan browser flow.
- `UserData`/resource dan tabel perlu menyajikan state arsip secara eksplisit.
- Semua mutation harus menyamakan guard `SuperSystem`; UI hanya menjadi
  visibility/UX, backend menjadi security authority.

## Non-scope

- Tidak mengubah authentication starter kit.
- Tidak menjalankan force delete pada database nyata tanpa prosedur operasi yang
  disetujui.
- Tidak mengubah status user existing melalui migration massal.

## Acceptance saat Implementasi Nanti

- [x] User terarsip tidak dapat menerima soft delete kedua melalui UI.
- [x] Force delete ditolak tanpa `user.force.delete`, untuk user aktif, dan
  untuk target `SuperSystem`.
- [x] Mutation update, role assignment, status, soft delete, restore, force
  delete, dan impersonation target `SuperSystem` ditolak server-side.
- [x] Audit menyimpan actor, subject, action, dan correlation ID tanpa data sensitif.
- [x] UI daftar arsip membedakan `Diarsipkan`, lifecycle status, restore, dan
  force delete secara jelas.

## Revision History

| Versi | Tanggal | Perubahan |
| --- | --- | --- |
| 1.0 | 2026-08-06 | Mencatat keputusan terbuka archive, force delete, dan invariant SuperSystem. |
| 1.1 | 2026-08-06 | Menetapkan archive dan status sebagai state terpisah serta menyetujui arah force delete. |
| 1.2 | 2026-08-06 | Memprioritaskan guard backend SuperSystem sebagai task implementasi pertama. |
| 1.3 | 2026-08-06 | Menetapkan `user.restore` dan `user.force.delete` sebagai permission sensitif terpisah serta diaudit. |
| 1.4 | 2026-08-06 | Menyelesaikan lifecycle restore dan force delete berikut test, audit, serta UI. |
