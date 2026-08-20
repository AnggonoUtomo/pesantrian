# ADR-0002: Session dan audit impersonation

## Status

`Accepted`.

## Date

2026-08-06

## Context

UserManagement membutuhkan impersonation untuk kebutuhan support dan operasi
administratif. Fitur ini sensitif karena actor dapat melihat aplikasi sebagai
user lain. Actor asli harus dapat dipulihkan, target `SuperSystem` harus selalu
ditolak, dan aktivitas harus dapat diteruskan ke AuditLog tanpa membuat storage
audit kedua di UserManagement.

## Decision

### Session

Saat impersonation dimulai, adapter session menyimpan key berikut:

- `impersonation.actor_id`: ULID actor asli;
- `impersonation.target_id`: ULID user target;
- `impersonation.started_at`: timestamp ISO-8601;
- `impersonation.reason`: alasan yang sudah divalidasi.
- `impersonation.correlation_id`: ULID korelasi start/end dan audit.

Setelah konteks actor asli tersimpan, adapter menjalankan login ke user target.
Actor asli tidak boleh ditimpa atau hilang sebelum proses login target berhasil.

Saat leave, adapter memuat `actor_id`, login kembali sebagai actor asli,
menghapus seluruh key `impersonation.*`, lalu meregenerasi session ID untuk
mengurangi risiko session fixation.

### Route

Route memakai middleware `web`, `auth`, `verified`, dan permission coarse-grained:

```text
POST /system/users/{user}/impersonate
POST /system/users/impersonation/leave
POST /api/v1/users/{user}/impersonation
DELETE /api/v1/impersonation
```

Controller tetap tipis. Validasi reason berada pada FormRequest/DTO, aturan
target berada pada policy dan Application Action, sedangkan perubahan session
berada pada Infrastructure adapter.

API start/end memakai idempotency. Setelah authentication berpindah ke target,
middleware idempotency tetap memakai `impersonation.actor_id` sebagai owner
reservation. Dengan begitu replay start dan end tidak berganti identity akibat
perubahan guard. Response API hanya membawa status aktif serta display name
actor/target; actor ID, target ID, reason, key session, cookie, dan token tidak
pernah dikirim.

### Audit

UserManagement menerbitkan public event berikut:

- `UserImpersonationStarted`;
- `UserImpersonationEnded`.

Event membawa actor ID, target ID, reason, correlation/session context yang
aman, dan timestamp. Event tidak boleh membawa password, token, credential,
session cookie, atau seluruh payload request. AuditLog menjadi consumer dan
owner persistence audit saat module tersebut tersedia.

### Authorization

Backend wajib memeriksa `user.impersonate` dan menolak target `SuperSystem` pada
policy serta Application Action. Frontend hanya mengatur visibility dan UX.

## Alternatives considered

### Membuat tabel audit di UserManagement

Ditolak karena AuditLog adalah boundary yang memiliki audit persistence. Dua
storage audit akan membuat retensi, korelasi, dan redaction tidak konsisten.

### Hanya menyimpan target user pada session

Ditolak karena actor asli dapat hilang dan proses leave tidak dapat dipercaya.

### Mengandalkan permission frontend

Ditolak karena frontend bukan security boundary.

## Consequences

- Session adapter menjadi satu-satunya komponen yang boleh mengubah konteks
  authentication impersonation.
- Event audit dapat dibuat sebelum AuditLog selesai tanpa membuat dependency
  concrete lintas module.
- Leave route wajib tersedia bersamaan dengan start route.
- Test harus membuktikan permission, reason, protected target, actor restore,
  session cleanup, event redaction, dan session regeneration.

## Rollback

Fitur dapat dinonaktifkan dengan menghapus route dan menu UI setelah event
contract tetap dipertahankan. Jangan menghapus session key atau event yang sudah
dipakai environment bersama tanpa rencana migrasi dan audit.

## Revision history

| Version | Date | Description |
| --- | --- | --- |
| 1.1 | 2026-08-06 | Menyetujui session, route, audit event, dan redaction impersonation |
| 1.2 | 2026-08-20 | Menambahkan contract API idempotent dengan ownership actor asli dan response secret-safe. |
