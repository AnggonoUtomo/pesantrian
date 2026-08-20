# System/UserManagement

## Status

`UserManagement selesai untuk scope repository: UI invitation/create, edit,
detail, avatar, lifecycle bulk, restore, multi-role, impersonation, dan audit
lintas module telah diverifikasi.`

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

UserManagement memiliki `UserManagementSeeder` untuk membuat 50 user dummy pada
development. Seeder tetap berada di module dan dipanggil oleh
`database/seeders/DatabaseSeeder.php` setelah `AccessControlSeeder` sesuai
dependency order. Dengan dua akun baseline, bootstrap global menghasilkan 52
user.

Password user dummy memakai `ACCESS_CONTROL_DUMMY_PASSWORD`. Jika konfigurasi
tersebut kosong, password acak dibuat hanya di runtime development dan tidak
ditulis ke source atau log.

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
5. [ADR impersonation session dan audit](decisions/ADR-0002-IMPERSONATION-SESSION-AUDIT.md)
6. [ADR archive, force delete, dan perlindungan](decisions/ADR-0003-USER-ARCHIVE-AND-PROTECTION-LIFECYCLE.md) — diterima
7. [ADR urutan increment daftar dan lifecycle](decisions/ADR-0004-USERMANAGEMENT-INCREMENTAL-LIST-SCOPE.md) — diterima
8. [01. Filter daftar user](01.user-list-filters/README.md) — selesai

9. [02. Archive, force delete, dan perlindungan](02.archive-force-delete-and-protection/README.md) — selesai
10. [03. Pagination, role efektif, dan toolbar](03.pagination-role-visibility-and-toolbar/README.md) — selesai

## Dokumen Terkait

- [05. Identity, akses CRUD, dan avatar](05.identity-access-crud-and-avatar/README.md) - selesai
- [ADR-0005 identity, akses awal, avatar, dan aktivitas login](decisions/ADR-0005-IDENTITY-ACCESS-AVATAR-AND-LOGIN-ACTIVITY.md) - `Accepted`; avatar memakai disk privat dan route terotorisasi
- [ADR-0006 upgrade identifier user lama ke ULID](decisions/ADR-0006-LEGACY-USER-ID-TO-ULID-UPGRADE.md) - `Accepted`; upgrade bersifat forward-only dengan backup/restore
- [04. Bulk lifecycle user](04.bulk-user-lifecycle/README.md) - selesai dan browser terverifikasi
- [AccessControl](../01.AccessControl/README.md)
- [AccessControl code-flow](../01-1.AccessControl-code-flow/README.md)
- [Folder structure](../../../03-IMPLEMENTATION/03.04-FOLDER-STRUCTURE.md)
- [Module contract](../../../03-IMPLEMENTATION/03.07-MODULES.md)
- [Generator specification](../../../03-IMPLEMENTATION/03.05-GENERATOR-SPEC.md)
- [Module communication and execution](../../../03-IMPLEMENTATION/03.12-MODULE-COMMUNICATION-AND-EXECUTION.md)
- [Project rules](../../../AGENTS.md)

## Kemampuan yang Tersedia

- daftar dan detail user;
- membuat dan memperbarui data user;
- mengaktifkan atau menonaktifkan user;
- soft delete user;
- assignment role melalui public contract AccessControl;
- flow impersonation dengan permission, alasan, dan perlindungan
  `SuperSystem`.

## Riwayat Scope Lanjutan (telah ditutup)

Status saat ini: restore, invitation email, multi-role, archive/force-delete,
dan consumer AuditLog telah selesai dengan contract, guard, audit, focused test,
serta verifikasi browser. Daftar di bawah adalah catatan scope sebelum
implementasi, bukan backlog aktif atau OPEN RISK.

Deployment migration shared/production adalah handoff operasional. Operator
environment target wajib menjalankan backup/restore, pemeriksaan lock/downtime,
approval rilis, dan rollback mengikuti
[`migration-runbook.md`](migration-runbook.md). Rehearsal lokal sudah dicatat;
dokumen ini tidak mengklaim deployment target telah berlangsung.

Scope repository pada daftar historis sudah selesai. Rehearsal upgrade dari
fixture release lama juga sudah lulus. Pekerjaan yang tetap berada di luar
workspace adalah backup/restore pada environment target, pengukuran
lock/downtime terhadap data nyata, dan approval operator production.

## Aturan UI UserManagement

- Referensi visual boleh diambil dari `FrontendContoh/users`, tetapi route,
  tipe data, permission, dan payload wajib mengikuti contract module saat ini.
- Add, edit, dan view wajib menggunakan `Dialog` modal. Jangan memakai `Sheet`
  untuk alur tersebut.
- Shortcut mengikuti pola AccessControl. `/` dipakai untuk fokus search dan
  `Shift+A` untuk membuka modal tambah user. `Ctrl/Cmd+K` tetap menjadi milik
  command palette global.
- Tabel wajib memiliki state kosong, error, loading request, serta action aman.
  Perlindungan `SuperSystem` tetap dipastikan backend; kolom khusus tidak lagi
  ditampilkan pada tabel.

### Baseline warna module

UserManagement mengikuti baseline visual AccessControl dan tidak membuat surface
sendiri:

- Light: topnav, sidebar, dan card utama putih bersih; subcard sedikit lebih
  gelap dengan abu-abu netral.
- Dark: topnav, sidebar, dan card utama mengikuti palette aktif; subcard sedikit
  lebih gelap dari card utama.
- Accent summary card, icon, badge, border, hover, dan focus mengikuti fungsi
  user. Token visual berasal dari `resources/css/app.css`.
- Tabel, modal, search, dan empty state harus memakai surface serta state yang
  sama agar UserManagement menjadi contoh module System yang konsisten.

## Keputusan Scope Awal

- Status user memakai enum `active`, `inactive`, dan `suspended`.
- Create user awal memakai password lokal/development. Invitation flow ditunda.
- Soft delete hanya berlaku untuk user selain `SuperSystem`.
- Role assignment memakai public contract terpisah `RoleAssignmentCapability`.
- Vertical slice pertama hanya daftar/detail user, search/filter sederhana, dan
  state loading/empty/error.
- Impersonation menjadi increment terakhir dengan ADR session dan audit khusus.

## Pola komunikasi module

UserManagement memakai `UserRepository` dan `ImpersonationSession` sebagai
contract internal/application boundary. Untuk lintas module, UserManagement
memakai `AuthorizationCapability`, `RoleAssignmentCapability`, dan
`RoleCatalogCapability` milik AccessControl.

`ListUsers` dan `GetUser` adalah Query internal dengan `UserData` typed.
Mutation memakai Application Action. Event impersonation tetap menjadi Domain
Event internal. `UserManagementActivityOccurred` version 1 menjadi Integration
Event synchronous untuk AuditLog. Belum ada Application Event, Command Bus,
Queue/Job, Facade, atau Shared Kernel domain.

Fondasi enterprise wajib tetap dipetakan pada setiap increment UserManagement.
Status saat ini menjadi acuan: Contract/Interface dan Query/Read Contract
`implemented`; Domain Event `implemented terbatas` untuk impersonation;
Integration Event `implemented synchronous` untuk AuditLog; Application Event,
Command Bus, Queue/Job, Facade, dan Shared Kernel `planned` atau
`not applicable` sesuai consumer nyata. Perubahan
status wajib memperbarui specification, plan, tasks, test, dan ADR terkait.

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

Keputusan scope awal, impersonation session/audit, serta upgrade identifier
legacy sudah disetujui. Upgrade `BIGINT` ke ULID mengikuti ADR-0006 dan runbook
forward-only.

## Revision History

| Version | Date | Description |
| --- | --- | --- |
| 1.1 | 2026-08-06 | Menetapkan keputusan scope awal dan vertical slice |
| 1.2 | 2026-08-06 | Menyelesaikan impersonation session, audit event, dan browser flow |
| 1.3 | 2026-08-06 | Mencatat lima scope lanjutan yang belum dibuat |
| 1.4 | 2026-08-06 | Menutup scope AuditLog consumer synchronous dan memperbarui fondasi event |
| 1.5 | 2026-08-06 | Menambahkan dokumen draft increment filter daftar user poin 1 |
| 1.6 | 2026-08-06 | Menyelesaikan filter search, status, role, dan arsip UserManagement |
| 1.7 | 2026-08-06 | Menambahkan Open Decision archive, force delete, dan perlindungan SuperSystem |
| 1.8 | 2026-08-06 | Mencatat roadmap incremental pagination, role efektif, toolbar, dan scope lifecycle besar |
| 1.9 | 2026-08-06 | Menandai urutan increment daftar dan lifecycle sebagai keputusan diterima |
| 2.0 | 2026-08-06 | Menambahkan increment ADR-0003 dan memprioritaskan guard SuperSystem. |
| 2.1 | 2026-08-06 | Menyelesaikan restore dan force delete serta menghapus kolom Perlindungan tabel. |
| 2.2 | 2026-08-06 | Menyelaraskan Ziggy, toast Sonner, LoadingButton, dan aturan operasi frontend. |
| 2.3 | 2026-08-06 | Menambahkan dokumentasi increment pagination, role efektif, dan toolbar. |
| 2.4 | 2026-08-10 | Menambahkan rencana identity, akses awal, avatar Media Library, verifikasi email, dan aktivitas login. |
| 2.5 | 2026-08-10 | Menyelesaikan INC-001 read contract dan visual identitas UserManagement. |
| 2.6 | 2026-08-20 | Mencatat ADR dan rehearsal upgrade identifier legacy ke ULID. |
