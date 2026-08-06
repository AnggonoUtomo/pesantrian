# Alur Code System/AuditLog

## Tujuan

Dokumen ini menjelaskan hubungan code pada module `System/AuditLog`, mulai dari
aktivitas pada module producer sampai record audit dapat dibaca melalui halaman
System atau API internal.

## Dokumen di Folder Ini

- [Alur backend](backend-flow.md): alur event, sanitasi, penyimpanan, query, dan authorization.
- [Alur frontend](frontend-flow.md): alur page, filter, dialog detail, Ziggy, dan state UI.
- [Peta file](file-map.md): daftar file penting beserta tanggung jawabnya.

## Sumber Utama

- [Dokumentasi module AuditLog](../03.AuditLog/README.md)
- [Specification AuditLog](../03.AuditLog/specification.md)
- [Implementation plan AuditLog](../03.AuditLog/implementation-plan.md)
- [Task AuditLog](../03.AuditLog/tasks.md)
- [Aturan module dan authorization](../../../AGENTS.md)

## Ringkasan Alur

```text
Mutation penting pada AccessControl atau UserManagement
    -> publisher membuat Integration Event versi 1
    -> event dikirim secara synchronous di dalam transaksi producer
    -> listener AuditLog memvalidasi nama dan versi event
    -> RecordAuditEntry menyaring metadata sensitif
    -> repository menyimpan record append-only secara idempotent
    -> Query AuditLog memeriksa permission dan scope actor
    -> controller mengirim DTO ke Inertia atau JSON Resource
    -> frontend menampilkan filter, tabel, kartu mobile, dan dialog detail
```

Jika pencatatan event gagal, transaksi mutation producer ikut gagal. Pola ini
dipilih agar aktivitas sensitif tidak berhasil tanpa jejak audit.

## Checklist Fondasi Enterprise

| Fondasi | Status saat ini |
| --- | --- |
| Contract/Interface | `implemented` melalui `AuditRecorder` dan `AuditLogRepository` |
| Domain Event | `not applicable`; AuditLog mencatat fakta lintas module dan tidak memiliki mutation domain sendiri |
| Application Event | `not applicable` untuk flow saat ini |
| Integration Event | `implemented` melalui event publik AccessControl dan UserManagement versi 1 |
| Command | `implemented` sebagai Action `RecordAuditEntry`; Command Bus belum diperlukan |
| Query/Read Contract | `implemented` melalui `ListAuditLogs`, `GetAuditLog`, filter, page, dan record DTO |
| Shared Kernel | `not applicable`; `packages/StarterKit` bukan Shared Kernel bisnis |
| Facade/Module API | `implemented` melalui public contract `AuditRecorder` |
| Queue/Job | `not applicable`; pencatatan sengaja synchronous dan fail-closed |

## Batas Penting

- Producer tidak mengakses model atau repository private milik AuditLog.
- `event_id` mencegah record ganda saat event yang sama diproses ulang.
- Metadata hanya menyimpan key yang diizinkan dan nilai sensitif disamarkan.
- Record bersifat append-only. Update dan delete melalui model ditolak.
- `SuperSystem` dapat melihat seluruh record. Actor lain hanya melihat record
  miliknya setelah memiliki permission `audit_log.view`.
- Frontend hanya mengatur visibility. Backend tetap menjadi security authority.

## Status

Code-flow AuditLog selesai untuk integration event AccessControl dan
UserManagement, persistence MySQL, query terotorisasi, API internal, frontend,
seeder, dan quality gate.

## Revision History

| Version | Date | Description |
| --- | --- | --- |
| 1.0 | 2026-08-06 | Menambahkan alur code AuditLog setelah module selesai diverifikasi |
