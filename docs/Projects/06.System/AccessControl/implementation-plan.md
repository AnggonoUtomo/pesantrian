# Implementation Plan: AccessControl Module

## Architecture

```text
Authentication
    -> Middleware/Controller guard
    -> AccessControl public capability
    -> Spatie Permission adapter internal
    -> Policy/resource rule milik module owner
    -> Audit bila mutation sensitif
```

Module lain hanya memanggil public contract atau capability. Detail model,
repository, dan adapter Spatie tetap berada di dalam `AccessControl`.

## Urutan

1. Finalisasi namespace, boundary, permission owner, dan ADR.
2. Buat module melalui generator dan verifikasi manifest.
3. Buat permission identity dan contract authorization typed.
4. Implementasikan adapter internal ke Spatie Permission.
5. Implementasikan policy/gate integration dan server-side denial.
6. Tambahkan authorization context untuk frontend UX bila dibutuhkan.
7. Tambahkan test positif, negatif, contract, security, dan isolation.
8. Jalankan discovery, validation, quality gate, dan review documentation.

## Risiko

| Risiko | Mitigasi |
|---|---|
| Module lain mengakses private implementation | Public contract test dan dependency review |
| Frontend dianggap sebagai security boundary | Negative backend test tanpa authorization |
| Permission key dimiliki module yang salah | Permission identity hanya didefinisikan di module owner |
| Spatie Permission menjadi coupling publik | Adapter dibungkus public capability AccessControl |
| Role atau impersonation terlalu cepat dibakukan | Catat sebagai Open Decision dan implementasikan bertahap |
| Authorization context membocorkan data sensitif | Typed context minimal dan redaction test |

## Rollback

Perubahan dapat dibatalkan melalui commit module terkait. Jika migration atau
data permission sudah masuk environment bersama, gunakan forward migration atau
prosedur data rollback yang disetujui. Jangan menghapus histori authorization
secara hard delete.
