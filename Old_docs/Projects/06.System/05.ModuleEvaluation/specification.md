# Specification: Evaluasi Ulang Module System

## Tujuan

Mengevaluasi module System secara bertahap untuk menentukan perbaikan, penambahan fitur, atau penyelarasan lintas module. Hasil evaluasi bukan langsung coding. Setiap rekomendasi wajib memiliki owner, alasan, dampak, acceptance criteria, dan urutan implementasi.

## Kondisi Awal

| Module | Owner utama | Dependency | Kondisi inventory |
| --- | --- | --- | --- |
| AccessControl | role, permission, authorization capability | - | valid dan aktif |
| UserManagement | lifecycle user dan impersonation | AccessControl | valid dan aktif |
| AuditLog | audit append-only dan redaction | AccessControl, UserManagement | valid dan aktif |
| SystemSetting | konfigurasi runtime global | AccessControl, AuditLog | valid dan aktif |

Mode project adalah `module extension` pada Laravel 13, PHP 8.4, MySQL, Redis, Inertia React, TypeScript, Ziggy, dan Spatie Permission.

## Cakupan Evaluasi

Setiap module diperiksa dari kegunaan UI/UX, correctness, architecture, security dan audit, serta performance dan operasi. Review mencakup code, test, route, policy, contract publik, dokumentasi, dan browser flow bila module memiliki UI.

## Aturan Lintas Module

```text
Feature pengguna
    -> module owner menentukan business rule
    -> public contract/DTO/event bila consumer lintas module diperlukan
    -> AccessControl untuk authorization
    -> AuditLog untuk mutation sensitif
    -> SystemSetting hanya bila konfigurasi runtime global memang diperlukan
```

- Import concrete model, repository, policy, atau service private lintas module dilarang.
- Module pemilik fitur tetap memiliki route, controller, policy, UI, test, dan dokumentasi sendiri.
- Perubahan public contract, event, permission, schema, atau dependency harus memiliki ADR sebelum coding.

## Kandidat Awal

| Kandidat | Owner awal | Dampak | Status |
| --- | --- | --- | --- |
| Restore user | UserManagement | AccessControl, AuditLog, UI | perlu review detail |
| Invitation email | UserManagement | mail, AuditLog, UI | perlu keputusan desain |
| Multi-role management | UserManagement | AccessControl, AuditLog, UI | perlu review contract atomic |
| Filter dan retensi audit | AuditLog | UI dan operasi | perlu review detail |
| Pengalaman konfigurasi runtime | SystemSetting | seluruh System UI | perlu review detail |
| Penyederhanaan role/permission | AccessControl | seluruh consumer | perlu review detail |

Kandidat ini bukan keputusan implementasi. Hasil review dapat menolak, menggabungkan, atau memindahkan owner kandidat tersebut.

## Acceptance Evaluasi

- Setiap module memiliki temuan berstatus `required`, `optional`, atau `FYI`.
- Temuan menjelaskan file atau flow, alasan, dampak, dan bukti.
- Rekomendasi lintas module memiliki owner tunggal dan contract jelas.
- Tidak ada coding feature sebelum backlog per fitur disetujui.

## Risiko

| Risiko | Mitigasi |
| --- | --- |
| Feature baru membuat dependency melingkar | Petakan owner dan contract sebelum coding |
| UI terlihat lengkap tetapi backend belum aman | Review policy, Action, dan test negatif lebih dahulu |
| Perubahan contract merusak consumer | Gunakan perubahan additive dan contract test |
| Evaluasi terlalu luas | Kerjakan satu module sampai checkpoint sebelum lanjut |

## Revision History

| Versi | Tanggal | Perubahan |
| --- | --- | --- |
| 1.0 | 2026-08-06 | Menetapkan scope, aturan, dan kandidat awal evaluasi |
