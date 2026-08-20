# ADR-0001: Boundary, Append-only, Scope, dan Retensi AuditLog

## Status

`Accepted` pada 6 Agustus 2026.

## Context

AccessControl dan UserManagement menghasilkan aktivitas sensitif yang harus
dapat ditelusuri. Menyimpan audit pada masing-masing module akan membuat
redaction, scope, retention, dan query tidak konsisten. AuditLog harus menjadi
owner tunggal persistence audit tanpa mengambil alih business rule producer.

Baseline mewajibkan retensi minimum satu tahun, tetapi detail purge/archive
setelah satu tahun belum memiliki kebutuhan operasional, backup, dan owner
deployment yang cukup.

## Decision

- AuditLog berada di `app/Modules/System/AuditLog`.
- AuditLog memiliki tabel `audit_logs`, public `AuditRecorder`, redactor,
  repository, query, policy, dan UI.
- Record hanya dapat ditambah. Application model/repository menolak update dan
  delete; UI/API tidak menyediakan mutation route.
- `actor_id` menggunakan `nullOnDelete` agar histori tidak terhapus bersama actor.
- Default retention adalah 365 hari dan tidak boleh dikurangi.
- Increment awal tidak menjalankan purge/archive otomatis.
- `SuperSystem` dapat membaca semua record.
- Actor biasa dengan `audit_log.view` hanya membaca record miliknya.
- Delegated project, tenant, atau module scope menunggu owner model yang nyata.
- Purge/archive membutuhkan ADR terpisah, backup, authorization, operational
  audit, dan rehearsal.

## Alternatives Considered

### Audit disimpan di setiap module

Ditolak karena membuat banyak sumber kebenaran, retention berbeda, dan redaksi
tidak konsisten.

### Semua auditor dengan permission melihat semua record

Ditolak karena permission umum tidak membawa scope organisasi. Actor-own scope
lebih aman sampai delegated scope tersedia.

### Automatic purge tepat setelah 365 hari

Ditolak untuk increment awal karena belum ada archive destination, backup,
legal policy, dan rehearsal. Minimum satu tahun bukan perintah menghapus tepat
pada hari ke-365.

## Consequences

### Positif

- ownership audit jelas;
- histori aman dari cascade delete;
- akses default konservatif;
- retensi tidak menghapus evidence tanpa keputusan operasional.

### Batasan

- auditor biasa belum dapat melihat audit actor lain;
- storage akan terus tumbuh sampai archive/purge dibuat;
- proteksi direct SQL memerlukan credential database production yang read/write
  access-nya dibatasi oleh operator.

## Verification

- schema, append-only, scope, soft/hard actor delete, dan retention config test;
- negative test update/delete;
- browser test SuperSystem dan auditor biasa;
- review tidak adanya mutation route.

## Revision History

| Versi | Tanggal | Perubahan |
| --- | --- | --- |
| 1.0 | 2026-08-06 | Menerima boundary, actor scope, append-only, dan retensi tanpa purge otomatis |
