# Plan Starterkit Alignment

## Konteks

Baseline SakaSantri memakai struktur `app/Modules/<Namespace>/<Module>` dengan
istilah `namespace` sebagai area bisnis. Source code starterkit saat ini masih
memakai nama opsi dan manifest field `domain`, serta module awal berada pada
`app/Modules/System/*`.

Karena route, permission, Inertia page, test, dan migration masih mengikat
identifier `System`, perpindahan ke `Console` harus dilakukan sebagai bridge
terkontrol, bukan rename massal.

## Increment 1: Audit dan generator namespace

1. Audit runtime module starterkit.
2. Catat surface kompatibilitas `System`.
3. Tambahkan dukungan input `namespace` pada generator.
4. Jadikan `domain` alias kompatibilitas.
5. Hilangkan daftar folder skeleton default; parent folder dibuat dari file
   awal yang benar-benar ditulis.

## Increment berikutnya

1. Tambahkan dokumentasi pembuatan module baru berbasis generator yang sudah
   selaras.
2. Audit consumer `System -> Console`:
   - route URL dan route name,
   - permission key,
   - Inertia component path,
   - test path dan fixture,
   - manifest/module runtime.
3. Buat strategi bridge/alias untuk `Console` jika user menyetujui migrasi.

## Hasil audit increment 1

- Runtime module starterkit valid:
  - `System/AccessControl`
  - `System/AuditLog`
  - `System/SystemSetting`
  - `System/UserManagement`
- Route publik masih memakai URL `system/*`.
- Route name masih bercampur:
  - `access-control.*`
  - `system.audit-logs.*`
  - `system.system-settings.*`
  - `system.users.*`
  - `api.v1.system-settings.*`
- Inertia page masih memakai path `System/*`.
- Public boundary aktual berada di `Application/Contracts`,
  `Application/DTO`, dan `Application/Events`.
- Migration module tetap berada di `Database/Migrations`.

Kesimpulan: `System/*` harus diperlakukan sebagai implementation bridge sampai
consumer route, permission, page, test, dan migration siap dimigrasikan.

## Di luar scope increment ini

- Rename folder `app/Modules/System/*`.
- Rename route URL `system/*`.
- Rename permission key existing.
- Pindah frontend dari `resources/js/pages/System/*` ke
  `resources/js/modules/*`.
- Membuat module produk baru.
