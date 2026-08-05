# Specification: System/UserManagement

## Status

`Ready for Task 05` — Task 01 sampai Task 04 sudah diverifikasi.

## Objective

Membuat module UserManagement sebagai owner lifecycle user pada tabel `users`
starter kit. Module menggunakan authentication capability yang sudah ada dan
menggunakan public contract AccessControl untuk authorization serta assignment
role.

## Boundary Parent System

UserManagement merupakan child module dari parent boundary `System`:

```text
app/Modules/System/UserManagement
App\Modules\System\UserManagement
```

`System` mengelompokkan capability system baseline. UserManagement tetap
memiliki boundary implementasi sendiri dan tidak boleh menaruh business logic
di `app/Models`, `packages/StarterKit`, atau private layer AccessControl.

## Scope Saat Ini

- vertical slice awal membaca daftar dan detail user;
- search/filter sederhana pada daftar user;
- state loading, empty, error, unauthorized, dan responsive layout;
- tahap lanjutan mencakup create, update, status, soft delete, role assignment,
  dan impersonation setelah slice baca lulus;
- menyediakan permission identity milik UserManagement;
- menyediakan page frontend dengan layout System baseline;
- menyediakan loading, empty, error, permission visibility, responsive layout,
  dan browser flow.

## Non-Scope

- mengganti Fortify login, register, password reset, Passkey, atau 2FA;
- membuat adapter Spatie kedua di UserManagement;
- mengimpor private model, repository, policy, atau service AccessControl;
- mengubah role atau permission langsung melalui model Spatie dari UserManagement;
- invitation flow untuk create user pada increment awal;
- menghapus permanen user dari database;
- membuat audit implementation sendiri sebelum AuditLog tersedia;
- mengubah module AccessControl yang sudah selesai tanpa increment terpisah.

## Existing Capability Contract

| Capability | Source | Cara Pakai |
| --- | --- | --- |
| Authentication | Laravel Fortify dan `App\\Models\\User` | Dipakai kembali untuk login, password, Passkey, dan 2FA |
| User persistence | `users` migration dan `App\\Models\\User` | UserManagement menjadi owner lifecycle di atas tabel existing |
| Authorization | `AccessControl\\Application\\Contracts\\AuthorizationCapability` | Dipakai melalui public contract, bukan private adapter |
| Role/permission context | `HandleInertiaRequests` | Dipakai frontend untuk UX, bukan security boundary |
| UI shell | `system-dashboard-layout` dan shared theme | Menjadi baseline frontend module |

## Target Module Identity

```text
Name: UserManagement
Domain: System
Namespace: App\Modules\System\UserManagement
Path: app/Modules/System/UserManagement
Dependency: AccessControl public capability
```

## Permission Identity Awal

Definisi berikut adalah permission identity yang disetujui untuk increment awal:

| Key | Tujuan | Sensitive |
| --- | --- | --- |
| `user.view` | Melihat daftar dan detail user | false |
| `user.create` | Membuat user | true |
| `user.update` | Mengubah data user | true |
| `user.status.manage` | Mengubah status lifecycle user | true |
| `user.delete` | Soft delete user | true |
| `user.impersonate` | Masuk sebagai user target | true |

Permission owner tetap UserManagement melalui `permissions.php`. AccessControl
hanya melakukan discovery dan sync permission berdasarkan contract yang tersedia.

## Data Contract Awal

### User identity

- `id`: ULID dari starter kit;
- `name`: nama tampilan user;
- `email`: email unik;
- `email_verified_at`: status verifikasi email;
- `password`: tidak pernah dikembalikan pada response;
- `created_at` dan `updated_at`.

### Field tambahan yang sudah disetujui

- `status` enum dengan nilai `active`, `inactive`, dan `suspended`;
- `deleted_at` untuk soft delete user selain `SuperSystem`;
- password lokal/development untuk increment create user awal;
- field audit actor/correlation jika AuditLog sudah tersedia.

## Route/API Design Awal

Nama route final mengikuti keputusan presentation. Proposal awal:

```text
GET    /system/users
GET    /system/users/{user}
POST   /system/users
PATCH  /system/users/{user}
PATCH  /system/users/{user}/status
PATCH  /system/users/{user}/roles
DELETE /system/users/{user}
POST   /system/users/{user}/impersonate
POST   /system/users/impersonation/leave
```

Route mutation harus memakai middleware coarse-grained, policy owner module, dan
application action. Frontend route dibuat dengan Ziggy.

## Authorization Rules

- Backend adalah security authority.
- Frontend hanya menyembunyikan atau menampilkan action untuk UX.
- UserManagement tidak membuat authorization implementation kedua.
- Assignment role memakai public contract terpisah `RoleAssignmentCapability`
  dari AccessControl. Contract tersebut harus tersedia sebelum task role
  assignment dimulai.
- Target `SuperSystem` tidak boleh diubah statusnya, dihapus, atau dijadikan
  target impersonation.
- Impersonation harus memiliki permission dan alasan eksplisit.
- Session actor asli dan target harus dapat dibedakan.

## Acceptance Criteria

- module dapat dibuat melalui `module:make` tanpa duplicate identity;
- manifest, provider, permission source, dan struktur module valid;
- user dapat dilihat, dibuat, dan diperbarui sesuai permission;
- actor tanpa permission ditolak oleh backend;
- status user dapat diubah dengan aturan state yang jelas;
- soft delete tidak menghapus row secara permanen;
- role assignment tidak mengakses private implementation AccessControl;
- `SuperSystem` terlindungi dari mutation berbahaya;
- impersonation menolak tanpa reason, tanpa permission, atau dengan target
  `SuperSystem`;
- page frontend dapat dibuka dengan typed props, state UI, Ziggy, dan layout
  System baseline;
- positive, negative, authorization, schema, frontend, dan browser test lulus.

## Commands dan Test Plan

```bash
php artisan module:discover --json
php artisan module:validate --json
php artisan module:list --json
php artisan module:make UserManagement --domain=System --profile=default-v1 --dry-run --json
php artisan test
composer ci:check
npm run build
```

## Generator Contract dan Expected Output

Pembuatan skeleton wajib dimulai dari inventory dan generator resmi:

```bash
php artisan module:discover --json
php artisan module:validate --json
php artisan module:list --json
php artisan module:make UserManagement --domain=System --profile=default-v1 --dry-run --json
```

Dry-run wajib menghasilkan:

- `success: true`;
- `code: MODULE_PREVIEWED`;
- `data.module: UserManagement`;
- `data.path: app/Modules/System/UserManagement`;
- planned file sesuai profile `default-v1`;
- target filesystem belum berubah.

Setelah dry-run disetujui, command aktual adalah:

```bash
php artisan module:make UserManagement --domain=System --profile=default-v1 --force --yes --json
```

Output aktual wajib memiliki `code: MODULE_CREATED`, manifest valid, provider,
runtime config, permission source, README, route entry point, dan directory
canonical. Business logic tidak boleh dianggap sudah dibuat hanya karena
generator berhasil.

Focused test yang akan dibuat:

- `UserManagementArchitectureTest`;
- `UserManagementPermissionIdentityTest`;
- `UserManagementSchemaTest`;
- `UserManagementPolicyTest`;
- `UserManagementLifecycleTest`;
- `UserManagementRoleAssignmentTest`;
- `UserManagementImpersonationTest`;
- browser/accessibility test untuk critical user flow.

## Boundaries

- Always: gunakan ULID, public contract AccessControl, FormRequest, typed DTO,
  policy, action/query, Ziggy, dan UI baseline System.
- Ask first: detail migration additive, impersonation session/audit design, dan
  perubahan scope frontend dari vertical slice awal.
- Never: direct import private AccessControl class, hard delete user,
  menyimpan password/secret di log atau response, dan menjadikan frontend
  permission sebagai security boundary.

## Revision History

| Version | Date | Description |
| --- | --- | --- |
| 1.1 | 2026-08-06 | Menetapkan keputusan scope dan vertical slice awal |
| 1.2 | 2026-08-06 | Menetapkan enam permission identity dan status Task 04 |
| 1.3 | 2026-08-06 | Menambahkan domain lifecycle dan guard SuperSystem |
