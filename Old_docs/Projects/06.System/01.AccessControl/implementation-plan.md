# Implementation Plan: AccessControl Module

## Architecture

```text
Authentication
    -> Controller middleware (coarse-grained)
    -> Policy AccessControl (resource/scope/state)
    -> Application use case (re-check sebelum mutation)
    -> AccessControl public capability
    -> Spatie Permission adapter internal
    -> Audit bila mutation sensitif
```

Module lain hanya memanggil public contract atau capability. Detail model,
repository, dan adapter Spatie tetap berada di dalam `AccessControl`.

Route web dan API juga menjadi milik module. `ServiceProvider` memuat file
`Routes/web.php` dan `Routes/api.php`; route global hanya menyimpan route
bootstrap aplikasi seperti welcome, login area, dan settings.

## Fondasi Enterprise dan Batas Evolusi

Sebelum increment CQRS berikutnya, status fondasi harus tetap dapat ditelusuri:

- Contract/Interface: sudah aktif untuk authorization dan role assignment.
- Role catalog: sudah aktif melalui `RoleCatalogCapability` dan DTO
  `RoleOption` untuk consumer yang membutuhkan daftar role.
- Domain Event: disiapkan sebagai boundary fakta role/permission, tetapi belum
  dibuat tanpa consumer atau kebutuhan audit yang jelas.
- Application Event: belum diperlukan karena mutation belum memiliki beberapa
  handler application yang perlu dikoordinasikan.
- Integration Event: sudah aktif melalui `AccessControlActivityOccurred`
  version 1 dengan AuditLog sebagai consumer synchronous.
- Command: Action saat ini dapat dinaikkan menjadi Command + Handler melalui
  ADR dan focused migration increment.
- Query/Read Contract: sudah aktif melalui `BuildAccessControlDashboard` dan
  `AccessControlDashboardData`.
- Shared Kernel: tidak digunakan; `packages/StarterKit` tetap framework.
- Facade/Module API: public capability menjadi API module; Facade bukan default.
- Queue/Job: tidak digunakan untuk flow synchronous role/permission.

Implementasi role assignment saat ini menggunakan
`Infrastructure/Services/SpatieRoleAssignmentAdapter`. Adapter memeriksa
permission `access_control.role.assign`, melindungi role `SuperSystem` agar
hanya dapat dikelola oleh actor `SuperSystem`, lalu memanggil API role pada
target melalui public capability. Binding dilakukan oleh `ServiceProvider`.

Setiap perubahan pada status tersebut wajib memiliki acceptance criteria,
focused positive/negative test, failure behavior, dan rollback trace.

Pola execution baseline adalah CQRS-lite: Action untuk mutation dan Query untuk
read. Integration Event sudah ditambahkan karena AuditLog menjadi consumer
nyata. Command Bus, Queue/Job, Facade, dan Shared Kernel tidak ditambahkan tanpa
kebutuhan serta keputusan baru.

### Pembagian tanggung jawab runtime

- `Presentation/Controllers/RoleController` hanya mengatur middleware,
  menerima request yang sudah tervalidasi, memanggil query/action, dan
  mengembalikan response Inertia atau redirect.
- `Presentation/Requests` memiliki aturan validasi input mutation.
- `Application/Queries/BuildAccessControlDashboard` mengambil dan membentuk
  data role serta permission untuk kebutuhan page.
- `Application/DTO/AccessControlDashboardData` menjadi bentuk data typed yang
  dikirim ke Inertia.
- `Application/Actions` memiliki side effect create role, sync permission, dan
  delete role.
- `Application/Services/AuthorizeRoleMutation` menjadi pemeriksaan use-case
  kedua sebelum mutation. Policy memakai service yang sama untuk coarse-grained
  dan resource/state authorization.

Controller tidak boleh mengambil query Eloquent, menjalankan persistence
mutation, atau menulis aturan validasi langsung.

## Urutan

1. Finalisasi namespace, boundary, permission owner, dan ADR.
2. Buat module melalui generator dan verifikasi manifest.
3. Tetapkan role `SuperSystem`, permission key, dan contract authorization typed.
4. Implementasikan adapter internal ke Spatie Permission. **Selesai untuk
   capability dasar.**
5. Implementasikan policy/gate integration dan server-side denial.
6. Tambahkan shared Inertia props `user`, `roles`, `permissions`, dan
   `superSystem` dengan object boolean yang typed.
7. Tambahkan test positif, negatif, contract, security, dan isolation.
8. Jalankan discovery, validation, quality gate, dan review documentation.

## Risiko

| Risiko | Mitigasi |
|---|---|
| Module lain mengakses private implementation | Public contract test dan dependency review |
| Frontend dianggap sebagai security boundary | Negative backend test tanpa authorization |
| Permission key dimiliki module yang salah | Semua permission AccessControl, termasuk `system.dashboard.view`, memakai metadata owner `AccessControl` |
| Spatie Permission menjadi coupling publik | Adapter dibungkus public capability AccessControl |
| Role atau impersonation terlalu cepat dibakukan | Catat sebagai Open Decision dan implementasikan bertahap |
| Authorization context membocorkan data sensitif | Typed context minimal dan redaction test |
| Nama permission tidak konsisten | Gunakan dot notation dengan underscore dan contract test |
| Bypass `SuperSystem` tersebar di feature | Satu policy/capability terpusat dan negative test untuk role protected |
| Contract role assignment tidak dapat di-resolve runtime | Binding `RoleAssignmentCapability` ke adapter internal dan focused test container |

## Rollback

Perubahan dapat dibatalkan melalui commit module terkait. Jika migration atau
data permission sudah masuk environment bersama, gunakan forward migration atau
prosedur data rollback yang disetujui. Jangan menghapus histori authorization
secara hard delete.
