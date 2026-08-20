# Specification: AccessControl Module

## Objective

Menyediakan capability authorization publik yang dapat digunakan module lain
melalui contract yang typed, aman, dan tidak membuka detail implementasi
AccessControl.

## Identitas module

```text
Domain: System
Module: AccessControl
Namespace: App\Modules\System\AccessControl
Path: app/Modules/System/AccessControl
```

## Scope saat ini

- Membuat module `AccessControl` pada domain `System`.
- Menetapkan manifest, runtime configuration, provider, dan permission identity.
- Menyediakan public contract untuk pemeriksaan capability authorization.
- Menyediakan adapter internal Spatie Permission untuk capability dasar.
- Menetapkan pola permission owner dan policy boundary.
- Menyediakan test positif, negatif, contract, dan security.

## Non-scope

- CRUD business module.
- User profile dan lifecycle user.
- AuditLog sebagai module terpisah.
- SystemSetting.
- Impersonation sebelum contract dan aturan khususnya disetujui.
- Model atau repository AccessControl digunakan langsung oleh module lain.

## Existing capability contract

- Laravel starter kit menyediakan authentication dan flow Inertia dasar.
- Spatie Permission `8.3.0` tersedia sebagai dependency baseline dan adapter
  runtime berada di dalam module `AccessControl`.
- `packages/StarterKit` menyediakan manifest, permission identity, registry,
  discovery, validation, dan generator `module:make`.
- Generator menghasilkan struktur module baru tanpa overwrite.

## Module contract

Public capability awal yang perlu dirancang:

- memeriksa apakah actor memiliki permission tertentu;
- memeriksa beberapa permission dengan aturan yang jelas;
- menyediakan hasil typed untuk pemanggil;
- menjaga detail Spatie Permission tetap private di dalam module.

Contract awal memakai `AuthorizationCapability`, dengan result typed
`AuthorizationDecision`. Detail model Spatie tetap private di adapter.

## Pola komunikasi dan eksekusi

- `AuthorizationCapability` adalah public contract untuk pemeriksaan capability.
- `RoleAssignmentCapability` adalah public contract untuk assignment role.
- `RoleCatalogCapability` dan `DirectPermissionAssignmentCapability` menjadi
  contract lintas module untuk lookup role serta direct permission mutation.
  Failure `RoleNotFound` dan `PermissionNotFound` berada pada public
  `Application/Contracts/Exceptions`, bukan private domain boundary.
- Application Action menjadi pola mutation synchronous.
- Application Query menjadi pola read typed.
- Domain Event hanya dipakai untuk fakta internal module.
- Integration Event activity version 1 sudah aktif untuk AuditLog. Application
  Event, Command Bus, Queue/Job, Facade, dan Shared Kernel belum aktif pada
  AccessControl baseline.
- Detail target dan status implementasi mengikuti
  [`03.12-MODULE-COMMUNICATION-AND-EXECUTION.md`](../../../03-IMPLEMENTATION/03.12-MODULE-COMMUNICATION-AND-EXECUTION.md).

## Fondasi Enterprise AccessControl

Specification ini menjadi sumber keputusan sebelum implementasi lanjutan:

| Fondasi | Status | Acceptance minimum |
| --- | --- | --- |
| Contract/Interface | `implemented` | Contract typed tidak mengembalikan model private atau object Spatie |
| Domain Event | `planned` | Event past tense, payload typed, ULID actor/correlation, tanpa secret |
| Application Event | `not applicable` | Diaktifkan hanya jika ada beberapa handler application yang perlu dikoordinasikan |
| Integration Event | `implemented synchronous` | AuditLog mengonsumsi event version 1; event ID, correlation ID, failure propagation, dan redaction diuji |
| Command | `planned` | Command immutable, Handler terpisah, authorization dan idempotency jelas |
| Query/Read Contract | `implemented` | Query dashboard mengembalikan DTO typed dan tidak memiliki side effect |
| Shared Kernel | `not applicable` | Value object tidak dipindahkan ke shared package tanpa dua consumer dan owner |
| Facade/Module API | `implemented` | `AuthorizationCapability` dan `RoleAssignmentCapability` menjadi API publik |
| Queue/Job | `not applicable` | Tidak digunakan untuk mutation synchronous tanpa retry/failure contract |

Perubahan status wajib memperbarui specification, implementation plan, tasks,
README, code-flow, dan ADR yang relevan dalam increment yang sama.

## Authorization contract

Role privileged baseline bernama `SuperSystem`.

Permission key menggunakan dot notation dengan underscore:

```text
access_control.role.manage
access_control.permission.manage
access_control.role.assign
access_control.permission.assign
```

Permission dan role yang dibagikan ke Inertia menggunakan associative object
bernilai boolean:

```php
'roles' => [
    'access_control.role.manage' => true,
],
'permissions' => [
    'access_control.role.manage' => true,
],
'superSystem' => $user?->isSuperSystem() ?? false,
```

Controller memakai middleware `can:*` untuk coarse-grained guard. Policy
`AccessControl` menangani aturan resource, scope, state, dan `SuperSystem`.
Application use case mengulang pemeriksaan sebelum mutation. Detail Spatie
Permission tetap berada di adapter internal.

Frontend `usePermission()` hanya digunakan untuk visibility dan UX. Nilainya
tidak menjadi security boundary.

## Data contract

- Primary key dan foreign key menggunakan ULID.
- Actor identity dan correlation ID menggunakan ULID.
- Permission identity memiliki `key`, `description`, `module`, dan `sensitive`.
- Secret, credential, token, password, dan payload sensitif tidak boleh masuk
  ke log, diagnostic, event, atau generated artifact.

## Permission awal

Permission dimiliki oleh module `AccessControl`. Permission awal mencakup
`system.dashboard.view`, `access_control.role.manage`,
`access_control.permission.manage`, `access_control.role.assign`, dan
`access_control.permission.assign`.

## Route/API design

Module memiliki endpoint internal System untuk dashboard, daftar role, create,
update permission, dan delete role. Authorization tetap dipakai melalui
middleware, policy, dan public capability.

## Acceptance criteria

- Module dapat ditemukan, divalidasi, dan didaftarkan oleh registry.
- Manifest dan `module.php` sesuai contract Phase 2.
- Permission identity valid dan ownership-nya jelas.
- Public capability dapat dipakai module lain tanpa mengimpor private class.
- Actor tanpa permission ditolak pada server-side authorization.
- Role `SuperSystem` mengikuti aturan privileged baseline.
- Controller middleware, policy, dan use-case authorization memakai boundary
  yang sesuai.
- Shared authorization props memakai `roles`, `permissions`, dan `superSystem`
  dengan bentuk object boolean yang typed.
- Frontend authorization context tidak menjadi security boundary.
- Positive, negative, contract, dan security test tersedia.
- Tidak ada forbidden dependency atau sensitive payload pada output.

## Commands dan test plan

```bash
php artisan module:make AccessControl --domain=System --dry-run --json
php artisan module:discover --json
php artisan module:validate --json
php artisan module:list --json
php artisan test
```

## Boundaries

- Always: backend authority, policy boundary, public contract, ULID, audit saat
  mutation sensitif, dan redaction.
- Ask first: daftar permission final, model role, impersonation, route publik,
  dan integrasi lintas module.
- Never: cross-module private model import, explicit deny model, hardcoded
  bypass authorization, secret di source/log/output, Wayfinder, atau Laravel
  Boost.
