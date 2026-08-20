# 01. Security Hardening System

## Status

`Selesai - hardening, quality gate, dan verifikasi browser telah dibuktikan.`

Increment ini menutup temuan required dari evaluasi AccessControl,
UserManagement, dan AuditLog tanpa menambah fitur bisnis baru.

## Ruang Lingkup

1. Hanya user `active` dan tidak diarsipkan yang dapat login atau menjadi target impersonation.
2. Sesi user yang kemudian menjadi `inactive` atau `suspended` ditolak pada request web berikutnya.
3. `access_control.role.manage` hanya mengelola role; sinkronisasi permission role memerlukan `access_control.permission.assign`.
4. Reason audit yang tampak seperti password, bearer token, API key, cookie, atau credential ditolak sebelum disimpan.
5. Immutability AuditLog diperkuat pada code boundary; hardening hak database production tetap pekerjaan operasi terpisah.

## Dokumen

1. [ADR-0001](ADR-0001-SECURITY-HARDENING-SYSTEM.md)
2. [Implementation plan](implementation-plan.md)
3. [Tasks](tasks.md)
4. [Execution log](planning/execution-log.md)

## Batasan

- Tidak menambah package, migration, queue, atau event baru.
- Tidak mengubah kebijakan retensi AuditLog atau credential database production.
- Tidak melanjutkan avatar, invitation, atau multi-role UserManagement.
