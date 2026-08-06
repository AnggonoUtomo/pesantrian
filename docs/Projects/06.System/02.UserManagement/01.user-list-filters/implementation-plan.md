# Implementation Plan: Filter Daftar User

## Status

`Selesai — seluruh increment dan quality checkpoint tervalidasi.`

## Preflight dan Traceability

| Item | Hasil pemeriksaan |
| --- | --- |
| Mode project | `module extension` pada starter kit yang sudah memiliki UserManagement. |
| Source authoritative | `AGENTS.md`, `docs/AGENTS.md`, `docs/README.md`, dan `03.12-MODULE-COMMUNICATION-AND-EXECUTION.md`. |
| Dokumen downstream | README parent UserManagement, dokumen increment ini, test UserManagement, dan execution log. |
| Code existing | `UserListFilter`, `ListUsers`, `UserRepository`, `EloquentUserRepository`, `UserController`, `Index.tsx`, `UserTable.tsx`, dan `types.ts`. |
| Golden boundary | Query internal memakai `UserListFilter` dan `UserRepository`; role hanya melalui `RoleCatalogCapability` public. |
| Dependency | AccessControl dan catalog role sudah tersedia; tidak ada migration atau package baru. |
| Rollback trace | Perubahan hanya contract query, repository, controller, UI, dan test; dapat dikembalikan tanpa data migration. |

## Arsitektur Target

```text
Inertia toolbar + Ziggy query
    -> GET system.users.index
    -> validasi query request
    -> UserListFilter typed
    -> ListUsers Query
    -> UserRepository contract
    -> EloquentUserRepository
    -> UserResource + filters aktif
    -> tabel, empty state, dan reset UI
```

`RoleCatalogCapability` hanya menyuplai role valid dan opsi UI. Ia bukan sumber
query user dan tidak membocorkan model AccessControl kepada UserManagement.

## Urutan Increment

1. **Contract dan test RED**
   - Perbarui DTO filter serta type/filter props secara additive.
   - Tambahkan focused positive dan negative test untuk nilai filter.
   - Acceptance: test baru gagal sebelum repository dan controller diubah.

2. **Query dan presentation boundary**
   - Terapkan mode archive, status, dan role pada adapter Eloquent.
   - Validasi input pada request boundary serta kirim filter aktif melalui
     controller.
   - Acceptance: kombinasi filter tepat, invalid input ditolak, controller
     tetap tidak berisi query Eloquent.

3. **Vertical slice frontend**
   - Tambahkan select status, role, archive, submit, reset, loading, dan empty
     state filter pada tabel.
   - Pertahankan dialog mutation, shortcut lama, permission visibility, dan
     baseline visual System.
   - Acceptance: filter tersinkron URL dan kondisi reload/mobile tetap benar.

4. **Quality checkpoint**
   - Jalankan focused test, lint, types, format, build, module validation, dan
     browser check.
   - Perbarui checklist dan execution log dengan hasil nyata.
   - Acceptance: evidence lengkap atau risiko tersisa tercatat.

## Risiko dan Mitigasi

| Risiko | Mitigasi |
| --- | --- |
| Filter role menambah coupling AccessControl | Gunakan `RoleCatalogCapability` dan relasi `User`; jangan import model/private repository AccessControl. |
| Nilai query URL tidak valid | Whitelist enum dan role di request boundary; tambahkan negative test. |
| Daftar user bertambah besar | Tidak menambah pagination diam-diam; catat sebagai increment terpisah. |
| Default archive mengubah ekspektasi pengguna | Menunggu persetujuan usulan `all` sebelum coding. |
| UI filter penuh pada mobile | Gunakan toolbar wrapping dan uji viewport mobile di browser. |

## Rollback

Tidak ada migration atau perubahan data. Jika perilaku filter bermasalah, kembalikan commit increment ini; route daftar kembali hanya membaca `search` dan memakai `withTrashed()` seperti sebelumnya.

## Revision History

| Versi | Tanggal | Perubahan |
| --- | --- | --- |
| 1.0 | 2026-08-06 | Menyusun urutan contract, query, frontend, dan quality checkpoint poin 1. |
| 1.1 | 2026-08-06 | Mencatat penyelesaian contract, query, UI, dan quality checkpoint. |
