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

Route web AccessControl dimiliki oleh module pada
`app/Modules/System/AccessControl/Routes/web.php` dan dimuat melalui
`AccessControl\ServiceProvider`. Nama route dan URL tetap menjadi contract
frontend Ziggy.

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
`RoleAssignmentCapability`. Keduanya sudah memiliki binding runtime melalui
adapter internal. Module lain wajib memakai interface tersebut, bukan model,
repository, atau adapter Spatie private.

Mutation AccessControl memakai Application Action dan read memakai Application
Query. Domain Event, Integration Event, Command Bus, Queue/Job, Facade, dan
Shared Kernel belum menjadi bagian runtime AccessControl. Baseline keseluruhan
menggunakan CQRS-lite.

## Fondasi Enterprise Module

Fondasi berikut wajib dipertimbangkan dan statusnya tidak boleh dibiarkan
kosong sebelum increment baru dimulai:

| Fondasi | Status AccessControl saat ini | Aturan evolusi |
| --- | --- | --- |
| Contract/Interface | `implemented` melalui `AuthorizationCapability` dan `RoleAssignmentCapability` | Semua consumer memakai contract publik, bukan model Spatie |
| Domain Event | `planned` | Tambahkan untuk fakta mutation role/permission bila consumer internal nyata sudah ditetapkan |
| Application Event | `not applicable` pada scope saat ini | Aktifkan jika beberapa handler application perlu dikoordinasikan |
| Integration Event | `planned` | Aktifkan saat AuditLog atau consumer eksternal tersedia; wajib versioned dan sanitized |
| Command | `planned` | Mutation dapat dinaikkan dari Action ke Command + Handler melalui increment dan ADR |
| Query/Read Contract | `implemented` internal | Query typed tidak boleh mengubah state; public read contract memerlukan consumer nyata |
| Shared Kernel | `not applicable` | `packages/StarterKit` tetap framework package, bukan Shared Kernel bisnis |
| Facade/Module API | `implemented` melalui public capability | Facade baru memerlukan API stabil dan consumer nyata |
| Queue/Job | `not applicable` pada scope synchronous | Job baru wajib memiliki retry, idempotency, actor/correlation ID, dan failure contract |

Status di atas menjadi guardrail untuk pekerjaan berikutnya. Implementasi CQRS
hybrid atau penuh tidak boleh dilakukan dengan menghapus boundary Action/Query
secara langsung.

## Status Open Risk

- Open Risk runtime AccessControl: `ditutup`. Contract authorization dan role
  assignment sudah memiliki binding adapter, policy, focused test, dan full CI.
- Fondasi CQRS yang berstatus `planned` atau `not applicable` bukan defect.
  Status tersebut adalah keputusan scope CQRS-lite karena belum ada consumer
  event, kebutuhan command bus, atau pekerjaan asynchronous yang nyata.
- Migration upgrade database existing tetap menjadi release gate terkendali.
  Runbook, backup, rehearsal, downtime, dan approval release wajib tersedia
  sebelum deployment shared. Risiko ini tidak dapat dieksekusi dari workspace
  tanpa database environment yang sebenarnya.

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
