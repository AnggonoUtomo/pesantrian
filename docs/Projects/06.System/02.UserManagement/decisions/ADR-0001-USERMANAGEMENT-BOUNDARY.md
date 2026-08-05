# ADR-0001: Boundary System/UserManagement

## Status

`Proposed`.

## Date

2026-08-06

## Context

UserManagement membutuhkan role, permission, policy, dan kemungkinan
impersonation. AccessControl sudah memiliki public authorization capability dan
menjadi owner role/permission. Jika UserManagement langsung memakai model atau
adapter private AccessControl, boundary module akan bocor dan sulit diganti.

Selain itu, authentication dan model `User` sudah disediakan starter kit dengan
Fortify, Passkey, 2FA, dan ULID. UserManagement perlu memperluas lifecycle tanpa
membangun ulang authentication.

Kedua module berada dalam parent boundary `System`, tetapi tetap memiliki
ownership terpisah:

```text
app/Modules/System/
├── AccessControl/
└── UserManagement/
```

## Decision

UserManagement menjadi owner lifecycle user di atas tabel `users`, sedangkan:

- authentication tetap dimiliki starter kit;
- authorization dan role/permission persistence tetap dimiliki AccessControl;
- komunikasi UserManagement ke AccessControl memakai public contract, DTO,
  capability, atau public event;
- skeleton UserManagement dibuat melalui `module:make` dengan domain `System`
  sebelum business implementation dimulai;
- private model, repository, policy, dan service AccessControl tidak boleh
  diimpor;
- controller UserManagement tetap tipis dan business rule berada pada
  Application atau Domain owner;
- frontend UserManagement mengikuti System dashboard baseline dan memakai
  authorization context hanya untuk visibility/UX.

## Alternatives Considered

### UserManagement mengelola role sendiri

Ditolak karena membuat authorization implementation kedua dan berpotensi
menghasilkan dua sumber kebenaran permission.

### UserManagement mengimpor model Role AccessControl

Ditolak karena concrete dependency lintas module melanggar boundary dan membuat
perubahan persistence AccessControl berdampak langsung ke UserManagement.

### Semua lifecycle user tetap berada di starter kit

Ditolak karena starter kit hanya menyediakan authentication dasar. Lifecycle
administratif, status, soft delete, role assignment, dan impersonation adalah
business capability module.

## Consequences

- AccessControl harus menyediakan public role-assignment contract sebelum task
  assignment UserManagement dimulai.
- UserManagement memiliki test contract untuk memastikan tidak ada private
  import lintas module.
- Perubahan schema user harus additive dan diuji terhadap auth, Passkey, dan 2FA.
- Impersonation membutuhkan keputusan session dan audit sebelum implementasi.

## Open Decision

ADR ini belum dapat berstatus `Accepted` sebelum disetujui bersama keputusan:

1. Bentuk status user: `is_active` atau enum `status`.
2. Apakah create user memakai password langsung atau invitation flow.
3. Detail session impersonation dan route leave.
4. Apakah soft delete berlaku untuk semua user selain `SuperSystem`.

## Revision History

| Version | Date | Description |
| --- | --- | --- |
| 1.0 | 2026-08-06 | ADR boundary UserManagement diajukan |
