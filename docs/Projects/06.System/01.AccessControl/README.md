# AccessControl Module

Module ini menjadi pemilik capability authorization publik untuk module lain.
Module berada pada domain `System` dan tidak membagikan detail model,
repository, atau adapter internalnya secara langsung.

## Identitas module

- Domain: `System`
- Module: `AccessControl`
- Namespace: `App\Modules\System\AccessControl`
- Path: `app/Modules/System/AccessControl`
- Permission owner: `AccessControl`
- Status: `Selesai; backend, frontend, test, dan browser verification lulus`

Package `spatie/laravel-permission:8.3.0` sudah terhubung melalui config dan
model adapter ULID milik module. Capability dasar, policy role, middleware
coarse-grained, shared authorization context, page role/permission, mutation
role, dan browser verification sudah tersedia.

## Migration dan seeder global

Migration dan seeder tetap dimiliki module. Provider mendaftarkan migration
melalui `loadMigrationsFrom()`, sedangkan `database/seeders/DatabaseSeeder.php`
memanggil `AccessControlSeeder` sebagai bagian dari bootstrap global.

Gunakan alur standar berikut:

```bash
php artisan migrate:fresh --seed
```

Command `php artisan access-control:seed` tetap tersedia untuk focused operation
atau test module, bukan sebagai alur bootstrap satu-satunya.

## Urutan baca

1. [Specification](specification.md)
2. [Implementation plan](implementation-plan.md)
3. [Tasks](tasks.md)
4. [ADR namespace](decisions/001-access-control-namespace.md)
5. [ADR schema ULID](decisions/002-ulid-spatie-schema.md)
6. [Runbook upgrade ULID](upgrade-runbook.md)
7. [Frontend AccessControl](frontend/README.md)

## Dokumen terkait

- [Module Contracts](../../../06-FRAMEWORK/06.02-MODULE-CONTRACTS.md)
- [Module Communication and Execution](../../../03-IMPLEMENTATION/03.12-MODULE-COMMUNICATION-AND-EXECUTION.md)
- [Module Registry](../../../06-FRAMEWORK/06.03-MODULE-REGISTRY.md)
- [Authorization](../../../07-KERNEL/07.04-AUTHORIZATION.md)
- [Phase 3 Module Generator](../../03.phase-3-module-generator/README.md)

## Batas module

- `AccessControl` menjadi pemilik capability permission resolution.
- Module lain menggunakan public contract atau capability yang disetujui.
- Model dan repository internal tidak boleh diimpor langsung oleh module lain.
- Backend tetap menjadi security authority.
- UI hanya memakai authorization context untuk visibility dan UX.
- `SuperSystem` memiliki bypass terpusat melalui `Gate::before`; impersonation
  tetap membutuhkan aturan khusus dan tidak ikut bypass otomatis.

## Pola komunikasi module

AccessControl memiliki dua public capability: `AuthorizationCapability` dan
`RoleAssignmentCapability`. Module lain wajib memakai interface tersebut, bukan
model, repository, atau adapter Spatie private.

Mutation AccessControl memakai Application Action dan read memakai Application
Query. Domain Event, Integration Event, Command Bus, Queue/Job, Facade, dan
Shared Kernel belum menjadi bagian runtime AccessControl. Baseline keseluruhan
menggunakan CQRS-lite.

## Cara verifikasi awal

```bash
php artisan module:discover --json
php artisan module:validate --json
php artisan module:list --json
```

Verifikasi implementasi:

```bash
php artisan test
npm run types:check
npm run lint:check
npm run build
```
