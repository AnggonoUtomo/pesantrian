# Implementation Plan: Archive, Force Delete, dan Perlindungan User

## Preflight

| Item | Evidence yang diperlukan |
| --- | --- |
| Sumber authoritative | `AGENTS.md`, `docs/AGENTS.md`, ADR-0003, dan dokumen module UserManagement. |
| Kode awal | `UpdateUser`, `AssignUserRole`, `UserManagementPolicy`, controller, resource, dan focused test. |
| Dependency | AccessControl public authorization/role capability dan AuditLog Integration Event. |
| Acceptance | Target SuperSystem ditolak pada Application boundary tanpa merusak mutation user biasa. |
| Rollback | Perubahan hanya pada guard/action dan test; dapat ditelusuri dari commit increment Task 01. |

## Tahap Implementasi

### Task 01 — Tutup invariant SuperSystem

1. Tinjau `UpdateUser` dan `AssignUserRole` untuk menentukan satu guard domain
   yang dapat dipakai tanpa membuat dependency lintas module konkret.
2. Tulis test gagal untuk update dan assignment role terhadap target
   `SuperSystem`; sertakan test positif user biasa.
3. Tambahkan guard pada Application Action, bukan hanya UI atau controller.
4. Jalankan focused test, module validation, lint, dan type check.
5. Catat file nyata, command, hasil, dan risiko pada execution log.

### Task 02 — Read model dan UI arsip

Dimulai hanya setelah Task 01 lulus. Menampilkan `Diarsipkan` sebagai state
utama, status lifecycle sebagai informasi sekunder, dan menghapus aksi soft
delete dari user yang telah terarsip.

### Task 03 — Restore user

Memerlukan permission, policy, Application Action, repository contract,
audit event, Dialog, test positif/negatif, dan browser test.

### Task 04 — Force delete user

Memerlukan permission sensitif `user.force.delete`, policy, Application Action,
repository contract, audit event, konfirmasi eksplisit, Dialog, test
positif/negatif, dan browser test. Tidak boleh dilakukan pada target aktif atau
`SuperSystem`.

## Definition of Done Increment

- [x] Checklist task diperbarui dengan kondisi awal, file berubah, alasan, dan evidence.
  - Evidence: task, execution log, ADR-0003, dan README mencatat archive,
    restore, force delete, serta guard SuperSystem.
- [x] Positive dan negative test lulus.
  - Evidence: focused lifecycle test meliputi target aktif, user terarsip,
    target protected, dan audit; bulk test menambah jaminan atomik batch.
- [x] Backend security authority dan public boundary tetap terjaga.
  - Evidence: Action dan policy memeriksa permission/state sebelum repository;
    UserManagement tetap memakai contract publik AccessControl dan AuditLog.
- [x] Dokumentasi downstream dan risiko diperbarui.
  - Evidence: browser bulk lifecycle, traceability, dan runbook migration
    diperbarui pada 10 Agustus 2026.
- [x] Tidak ada task berikutnya dimulai sebelum task aktif terverifikasi.
  - Evidence: restore/force delete telah memiliki test dan browser evidence
    sebelum dipakai sebagai fondasi lifecycle bulk.

## Revision History

| Versi | Tanggal | Perubahan |
| --- | --- | --- |
| 1.0 | 2026-08-06 | Membagi ADR-0003 menjadi empat task yang dapat diverifikasi. |
