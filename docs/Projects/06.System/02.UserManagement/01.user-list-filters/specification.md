# Specification: Filter Daftar User

## Status dan Owner

- Status: `Implemented — quality checkpoint lulus`.
- Owner capability: `System/UserManagement`.
- Dependency: `RoleCatalogCapability` public milik AccessControl untuk daftar
  opsi role. Query user tetap dimiliki UserManagement.

## Tujuan

Operator dengan permission `user.view` dapat mempersempit daftar user menurut
nama/email, status lifecycle, role, dan kondisi arsip tanpa mengubah data user.

## Kondisi Awal

- `UserListFilter` hanya membawa field `search`.
- `UserController::index()` hanya membaca query `search`.
- `EloquentUserRepository::list()` selalu memakai `withTrashed()`.
- `UserTable` hanya memiliki input pencarian dan tombol `Cari`.
- `roles` sudah dikirim melalui `RoleCatalogCapability`, tetapi belum dipakai
  sebagai filter.

## Contract yang Diusulkan

Route Inertia yang sama menerima query parameter tambahan. Semua parameter
bersifat opsional agar bookmark lama dengan hanya `search` tetap valid.

```text
GET /system/users?search={teks}&status={status}&role={nama-role}&archive={mode}
```

| Parameter | Nilai valid | Default | Perilaku |
| --- | --- | --- | --- |
| `search` | teks hingga 100 karakter | kosong | Mencari `name` atau `email`. |
| `status` | `active`, `inactive`, `suspended` | semua | Menyaring status user. |
| `role` | nama role dari catalog publik AccessControl | semua | Memilih user yang memiliki role tersebut. |
| `archive` | `all`, `active`, `archived` | `all` | Memilih semua, non-arsip, atau soft-deleted. |

Semua filter digabung dengan logika `AND`.

## Aturan Implementasi

- `UserListFilter` diperluas secara additive untuk `search`, `status`, `role`,
  dan `archive`.
- Validasi query dilakukan di presentation boundary. Nilai enum atau role yang
  tidak valid tidak diteruskan ke query database.
- `ListUsers` tetap Query tanpa side effect dan `UserRepository::list()` tetap
  menerima satu `UserListFilter` typed.
- Adapter Eloquent menerapkan `withTrashed`, `withoutTrashed`, atau
  `onlyTrashed`, lalu filter search, status, dan role.
- Filter role menggunakan relasi role pada `User` dan role tervalidasi dari
  `RoleCatalogCapability`; UserManagement tidak boleh mengimpor model `Role`
  atau repository private AccessControl.
- Controller tetap tipis: validasi request, membuat DTO, memanggil `ListUsers`,
  lalu mengirim filter aktif ke Inertia.
- Frontend memakai toolbar input search dan select status, role, archive,
  serta reset. Submit menggunakan Ziggy route `system.users.index`.
- Tidak ada audit event karena ini query baca. `user.view` tetap security
  boundary.

## UI dan Aksesibilitas

- Select memiliki label atau accessible name yang jelas.
- Nilai filter URL kembali tampil setelah reload.
- Empty state menjelaskan bahwa hasil tidak cocok dengan filter dan menyediakan
  tindakan reset.
- Toolbar responsif, memakai token visual System, dan shortcut `/` tetap
  memfokuskan input pencarian.

## Non-scope

Pagination, `per_page`, sorting, restore, force delete, dan multi-role
management tidak dibuat pada poin ini. Pagination menjadi increment terpisah
karena mengubah response daftar dan kontrak UI lebih luas.

## Acceptance Criteria

- [x] Actor berizin dapat memakai satu atau beberapa filter bersamaan.
- [x] Query lama yang hanya memakai `search` tetap kompatibel.
- [x] Nilai status, archive, dan role tidak valid ditolak di boundary request.
- [x] Role filter tidak membuat concrete dependency lintas module.
- [x] Reset mengembalikan URL dan tabel ke `archive=all` tanpa filter lain.
- [x] Empty state, responsive toolbar, permission visibility, dan console
  browser dapat diverifikasi.

## Test dan Verifikasi

```bash
php artisan test --filter=UserManagement
npm run lint:check
npm run types:check
npm run format:check
npm run build
php artisan module:validate System/UserManagement --json
```

Browser memeriksa query URL, kombinasi filter, reset, empty state, light/dark,
mobile, dan console error.

## Risiko dan Open Decision

| Item | Status | Catatan |
| --- | --- | --- |
| Default archive `all` | Ditutup | Disetujui untuk menjaga perilaku awal `withTrashed()`. |
| User memiliki banyak role | Terbuka | Filter cocok jika user mempunyai salah satu role bernama sama. Multi-role belum masuk scope. |
| Daftar besar | Terbuka | Tanpa pagination, seluruh hasil masih dimuat. Ditangani pada increment khusus. |

## Revision History

| Versi | Tanggal | Perubahan |
| --- | --- | --- |
| 1.0 | 2026-08-06 | Mendefinisikan contract filter additive dan batas poin 1. |
| 1.1 | 2026-08-06 | Menandai acceptance berdasarkan test dan browser verification. |
