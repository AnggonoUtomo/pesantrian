# System/UserManagement

## Status

`Ready for Task 09`.

UserManagement adalah module business pertama setelah AccessControl. Module ini
mengelola lifecycle user di atas tabel `users` starter kit. Authentication,
Passkey, 2FA, dan password flow bawaan Laravel tetap digunakan kembali.

Panduan migration shared/production tersedia pada
[`migration-runbook.md`](migration-runbook.md).

Migration tetap dimiliki module dan didaftarkan oleh `ServiceProvider` melalui
`loadMigrationsFrom()`. Bootstrap database memakai satu entry point global:

```bash
php artisan migrate:fresh --seed
```

UserManagement belum memiliki seeder data awal sendiri; jika nanti ditambahkan,
class seeder tetap berada di module dan wajib didaftarkan ke
`database/seeders/DatabaseSeeder.php` sesuai dependency order.

Keputusan boundary dan scope awal sudah disetujui. Implementasi dimulai dari
vertical slice read-only user list sebelum mutation, role assignment, atau
impersonation.

## Boundary Module

UserManagement berada di dalam parent boundary `System`:

```text
app/Modules/System/
├── AccessControl/
├── UserManagement/
├── AuditLog/
└── SystemSetting/
```

Namespace module adalah `App\Modules\System\UserManagement`. Parent `System`
menjadi kelompok capability system, sedangkan UserManagement tetap memiliki
owner, permission identity, contract, test, migration, dan README sendiri.
UserManagement tidak boleh memindahkan lifecycle user ke `app/Models` atau
mengambil alih private implementation AccessControl.

## Urutan Baca

1. [Specification](specification.md)
2. [Implementation plan](implementation-plan.md)
3. [Tasks](tasks.md)
4. [ADR boundary](decisions/ADR-0001-USERMANAGEMENT-BOUNDARY.md)

## Dokumen Terkait

- [AccessControl](../01.AccessControl/README.md)
- [AccessControl code-flow](../01-1.AccessControl-code-flow/README.md)
- [Folder structure](../../../03-IMPLEMENTATION/03.04-FOLDER-STRUCTURE.md)
- [Module contract](../../../03-IMPLEMENTATION/03.07-MODULES.md)
- [Generator specification](../../../03-IMPLEMENTATION/03.05-GENERATOR-SPEC.md)
- [Project rules](../../../AGENTS.md)

## Kemampuan yang Direncanakan

- daftar dan detail user;
- membuat dan memperbarui data user;
- mengaktifkan atau menonaktifkan user;
- soft delete user;
- assignment role melalui public contract AccessControl;
- flow impersonation dengan permission, alasan, dan perlindungan
  `SuperSystem`.

## Keputusan Scope Awal

- Status user memakai enum `active`, `inactive`, dan `suspended`.
- Create user awal memakai password lokal/development. Invitation flow ditunda.
- Soft delete hanya berlaku untuk user selain `SuperSystem`.
- Role assignment memakai public contract terpisah `RoleAssignmentCapability`.
- Vertical slice pertama hanya daftar/detail user, search/filter sederhana, dan
  state loading/empty/error.
- Impersonation menjadi increment terakhir dengan ADR session dan audit khusus.

## Cara Verifikasi Awal

```bash
php artisan module:discover --json
php artisan module:validate --json
php artisan module:list --json
php artisan module:make UserManagement --domain=System --profile=default-v1 --dry-run --json
```

## Prompt Generator Resmi

Prompt berikut menjadi acuan sebelum menjalankan generator:

```text
Lakukan Project Intake dan Existing Module Inventory terlebih dahulu.
Verifikasi module yang sudah ada dengan module:discover, module:validate,
module:list, dan module:inspect System/AccessControl. Jangan membuat duplicate
module.

Buat module UserManagement pada domain System dengan profile default-v1
menggunakan:

php artisan module:make UserManagement --domain=System --profile=default-v1 --dry-run --json

Setelah hasil dry-run disetujui, jalankan pembuatan aktual dengan --force --yes.
Ikuti struktur DDD-lite, manifest schema, permission schema, public contract,
README module, dan test contract pada dokumentasi repository.
Jangan membuat business logic sebelum increment sebelumnya diverifikasi.
```

Hasil yang diharapkan dari dry-run adalah output JSON dengan code
`MODULE_PREVIEWED`, target `app/Modules/System/UserManagement`, daftar planned
file, dan tidak ada file yang ditulis. Hasil pembuatan aktual diharapkan
memiliki code `MODULE_CREATED` dan struktur skeleton canonical. Generator tidak
membuat migration business, permission final, test behavior, atau frontend
business secara otomatis.

## Status Keputusan

Keputusan scope awal sudah disetujui. Detail session impersonation, audit event,
dan route keluar tetap harus ditetapkan dalam ADR khusus pada Task 09 sebelum
fitur impersonation dibuat.

## Revision History

| Version | Date | Description |
| --- | --- | --- |
| 1.1 | 2026-08-06 | Menetapkan keputusan scope awal dan vertical slice |
