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

Permission dimiliki oleh module `AccessControl`. Daftar permission final belum
ditetapkan dan menjadi Open Decision sebelum implementasi.

## Route/API design

Module baseline ini tidak membuat endpoint publik pada increment pertama.
Authorization dipakai melalui middleware, policy, dan public capability.

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
