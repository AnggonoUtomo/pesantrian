# ADR-0004: Urutan Increment Daftar dan Lifecycle User

## Status

`Accepted.`

## Tanggal

2026-08-06

## Context

UserManagement telah memiliki daftar user, mutation dasar, filter server-side, dan UI berbasis modal. Pekerjaan perlu dipisahkan agar contract daftar user tidak berubah bersamaan dengan lifecycle, invitation email, atau multi-role.

Filter backend untuk `search`, `status`, `role`, dan `archive` sudah selesai pada increment `01.user-list-filters`. Poin tersebut bukan pekerjaan baru; ia menjadi dependency untuk pagination dan toolbar berikutnya.

## Keputusan yang Diusulkan

Pekerjaan UserManagement ditinjau dan dikerjakan secara incremental dengan urutan berikut.

### Poin 2 — Filter backend

Status: `Selesai`.

- Contract query: `search`, `status`, `role`, dan `archive`.
- Validasi dilakukan oleh `ListUsersRequest`.
- Query memakai `UserListFilter`, `ListUsers`, dan `UserRepository`.
- UI filter dan reset memakai Ziggy serta sudah diverifikasi browser.

Tidak boleh dibuat ulang atau diubah diam-diam saat pagination ditambahkan.

### Poin 3 — Pagination server-side dan jumlah baris

Status: `Belum diputuskan untuk implementasi`.

Usulan scope:

- query `page` dan `per_page` dengan whitelist ukuran halaman;
- `UserRepository` mengembalikan typed paginated read model, bukan array tanpa metadata;
- filter poin 2 tetap dipertahankan saat berpindah halaman atau jumlah baris;
- UI menampilkan total, halaman aktif, previous/next, dan pilihan jumlah baris;
- test negative untuk `per_page` atau `page` tidak valid.

Pagination tidak mengubah route, permission, atau action mutation.

### Poin 4 — Role efektif user pada tabel dan modal detail

Status: `Belum diputuskan untuk implementasi`.

Usulan scope:

- `UserData` dan resource membawa role efektif secara typed;
- query menghindari N+1 melalui eager loading yang sesuai;
- tabel memakai badge role yang ringkas;
- modal detail menampilkan seluruh role efektif;
- data berasal dari public contract AccessControl atau relasi yang tidak mengimpor model private AccessControl.

Poin ini hanya menampilkan role. Role revoke dan multi-role mutation tidak masuk scope.

### Poin 5 — Toolbar dan shortcut filter/pagination

Status: `Belum diputuskan untuk implementasi`.

Usulan scope:

- merapikan toolbar setelah bentuk pagination disetujui;
- shortcut hanya untuk fokus search, membuka filter, dan berpindah halaman;
- tidak memakai `Ctrl/Cmd+K` karena itu milik command palette global;
- seluruh mutation user tetap memakai modal `Dialog`, bukan `Sheet`.

Shortcut baru harus diinformasikan pada UI, tidak aktif saat user mengetik pada input, dan diuji melalui browser.

### Poin 6 — Pilihan scope lifecycle besar

Status: `Ditunda sampai poin 3–5 selesai dan ditinjau`.

Satu scope berikut dipilih sebagai increment berikutnya, tidak dikerjakan bersamaan:

1. **Restore dan force delete** sesuai ADR-0003.
2. **Invitation email** dengan token sekali pakai, expired, mail fake, dan redaction.
3. **Multi-role management** termasuk revoke role, aturan `SuperSystem`, dan perubahan atomik.

## Alasan Urutan

- Pagination lebih dahulu mengatasi daftar yang tidak terbatas tanpa mengubah lifecycle user.
- Role efektif meningkatkan kemampuan operator membaca daftar tanpa capability mutation baru.
- Toolbar dan shortcut dirapikan setelah contract list stabil.
- Lifecycle besar ditunda agar restore/force delete, invitation, atau multi-role tidak bercampur dalam satu perubahan besar.

## Penyesuaian Prioritas

Pada 2026-08-06, implementasi ADR-0003 diprioritaskan sebelum poin 3. Alasannya,
guard backend terhadap mutation target `SuperSystem` adalah temuan security yang
harus ditutup lebih dahulu. Setelah ADR-0003 selesai, roadmap kembali ke poin
3, 4, dan 5.

## Batasan

- Backend tetap security authority.
- AccessControl tetap owner role dan permission.
- `SuperSystem` wajib diproteksi server-side untuk seluruh mutation sebelum restore/force delete atau multi-role diaktifkan.
- Tidak ada migration, package baru, atau perubahan authentication dalam poin 3–5 tanpa specification increment yang disetujui.

## Open Decision yang Memerlukan Persetujuan

- Setujui urutan poin 3, 4, lalu 5 sebagai roadmap berikutnya.
- Setelah poin 5, pilih satu scope lifecycle besar dari poin 6.
- Tentukan daftar ukuran `per_page` saat specification pagination dibuat; usulan awal: `10`, `25`, `50`, dan `100` dengan default `25`.

## Revision History

| Versi | Tanggal | Perubahan |
| --- | --- | --- |
| 1.0 | 2026-08-06 | Mencatat filter selesai dan roadmap pagination, role efektif, toolbar, serta lifecycle besar. |
| 1.1 | 2026-08-06 | Menyetujui urutan increment dan usulan ukuran halaman awal. |
| 1.2 | 2026-08-06 | Menunda pagination sementara untuk menutup invariant security ADR-0003. |
