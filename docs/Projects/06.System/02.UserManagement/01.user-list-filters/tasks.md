# Tasks: Filter Daftar User

## Status Checklist

Checklist sudah ditinjau sebelum coding. Semua task implementasi belum dimulai
sampai specification disetujui.

## Task 00 — Dokumentasi dan preflight

**Tujuan:** menetapkan contract, batas module, urutan kerja, dan evidence
sebelum kode diubah.

- [x] Source dan kondisi awal ditinjau.
  - Kondisi awal: `UserListFilter` hanya memuat `search`; controller dan tabel
    hanya mengenal query pencarian.
  - Perubahan: membuat seluruh dokumen pada folder `01.user-list-filters`.
  - Alasan: poin kerja dapat ditinjau per bagian tanpa bercampur dengan
    pagination atau lifecycle user.
  - Evidence: `AGENTS.md`, baseline docs, source UserManagement, dan
    `FrontendContoh/users` telah dibaca sebelum dokumentasi dibuat.
- [x] Specification disetujui.
  - Kondisi awal: default archive masih Open Decision pada dokumen draft.
  - Perubahan: user menyetujui implementasi dengan default `archive=all`.
  - Alasan: perilaku lama selalu memakai `withTrashed()` dan harus tetap
    kompatibel.
  - Evidence: persetujuan dicatat pada execution log tanggal 2026-08-06.

## Task 01 — Perluas contract filter dan test boundary

**Tujuan:** membuat input filter typed, aman, dan kompatibel dengan query lama.

**Files yang diperkirakan berubah:**

- `app/Modules/System/UserManagement/Application/DTO/UserListFilter.php`
- request query baru atau boundary request yang sesuai
- focused test UserManagement

- [x] Contract dan validation selesai.
  - Kondisi awal: `UserListFilter::from()` hanya menerima `search`.
  - Perubahan: menambah `status`, `role`, dan `archive` sebagai field optional
    dengan enum/role whitelist.
  - Alasan: Query menerima DTO typed, bukan string request mentah.
  - Acceptance: `search` lama tetap valid; enum atau role salah ditolak.
  - Evidence: `ListUsersRequest.php` memvalidasi query; test presentation
    menolak `status`, `role`, dan `archive` yang tidak valid.

## Task 02 — Terapkan filter pada Query dan repository

**Tujuan:** membuat hasil daftar sesuai kombinasi filter tanpa side effect.

**Files yang diperkirakan berubah:**

- `Application/Queries/ListUsers.php` bila diperlukan
- `Application/Contracts/UserRepository.php` bila signature perlu diperjelas
- `Infrastructure/Persistence/Repositories/EloquentUserRepository.php`
- focused test repository atau feature test

- [x] Query filter selesai.
  - Kondisi awal: repository selalu `withTrashed()` dan hanya memfilter
    `name`/`email`.
  - Perubahan: menerapkan archive, status, dan role pada query yang sama.
  - Alasan: filter dieksekusi server-side dan hasil tidak dapat dimanipulasi
    frontend.
  - Acceptance: kombinasi memakai `AND`; role cocok untuk user yang memiliki
    role; query baca tidak mengubah state.
  - Evidence: feature test membuktikan kombinasi search/status/role/archive;
    adapter hanya memakai relasi `User::roles()` dan tidak mengimpor model atau
    repository AccessControl.

## Task 03 — Controller, props, dan toolbar filter

**Tujuan:** menghubungkan contract backend ke UI yang dapat diuji.

**Files yang diperkirakan berubah:**

- `Presentation/Controllers/UserController.php`
- `resources/js/pages/System/UserManagement/types.ts`
- `resources/js/pages/System/UserManagement/pages/Index.tsx`
- `resources/js/pages/System/UserManagement/components/UserTable.tsx`

- [x] Vertical slice filter selesai.
  - Kondisi awal: UI hanya memiliki input `Cari`.
  - Perubahan: menambah select status, role, archive, reset, loading, dan
    empty state filter; query dikirim dengan Ziggy.
  - Alasan: operator dapat menguji filter tanpa mengetik URL manual.
  - Acceptance: reload mempertahankan pilihan URL; reset menghapus query selain
    default; toolbar responsif; `/` tetap fokus ke pencarian.
  - Evidence: Chrome DevTools memeriksa URL `?status=inactive` dengan dua
    hasil, reset menjadi 12 user, empty state untuk pencarian tanpa hasil, dan
    toolbar mobile 375px tanpa console warning/error.

## Task 04 — Quality checkpoint dan dokumentasi hasil

**Tujuan:** memastikan increment siap ditinjau sebelum poin berikut.

- [x] Quality checkpoint selesai.
  - Kondisi awal: evidence implementasi belum tersedia.
  - Perubahan: menjalankan test, quality command, module validation, browser
    test, lalu memperbarui checklist dan execution log.
  - Alasan: task tidak boleh selesai tanpa bukti nyata.
  - Acceptance: semua command penting lulus atau kegagalan dicatat sebagai
    Open Risk dengan owner dan dampak.
  - Evidence: 12 focused Pest test lulus; `npm run lint:check`,
    `npm run types:check`, `npm run format:check`, `npm run build`, dan
    `php artisan module:validate System/UserManagement --json` lulus.

## Definition of Done Poin 1

- [x] Contract filter, controller, repository, dan frontend sesuai specification.
- [x] Positive/negative test dan browser check tersedia serta lulus.
- [x] Tidak ada direct dependency UserManagement ke private AccessControl.
- [x] Documentation, execution log, revision history, dan risiko diperbarui.
- [x] Checklist ditinjau ulang setelah evidence diisi.

## Revision History

| Versi | Tanggal | Perubahan |
| --- | --- | --- |
| 1.0 | 2026-08-06 | Membuat task rinci filter daftar user poin 1. |
| 1.1 | 2026-08-06 | Mengisi evidence implementasi dan menutup checklist poin 1. |
