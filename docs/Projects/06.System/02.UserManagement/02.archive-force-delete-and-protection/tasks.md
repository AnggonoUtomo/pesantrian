# Tasks: Archive, Force Delete, dan Perlindungan User

## Status Checklist

Checklist ditinjau sebelum coding. Task 01 menjadi satu-satunya task aktif.

## Task 00 — Dokumentasi dan preflight

- [x] Menetapkan source, scope, urutan, dan risiko.
  - Kondisi awal: ADR-0003 telah `Accepted`, tetapi belum memiliki dokumen
    implementasi dan ADR-0004 menempatkan pagination lebih dahulu.
  - Perubahan: membuat folder `02.archive-force-delete-and-protection` serta
    mencatat prioritas security guard sebelum roadmap daftar berikutnya.
  - Alasan: invariant `SuperSystem` adalah security gap dan tidak boleh ditunda
    hanya karena urutan UI daftar.
  - Evidence: ADR-0002 diverifikasi selesai dengan 4 test/33 assertion;
    ADR-0003 dan ADR-0004 ditinjau pada 2026-08-06.

## Task 01 — Tutup guard mutation target SuperSystem

**Tujuan:** backend menolak perubahan profil dan role terhadap target
`SuperSystem`, termasuk jika request dikirim tanpa UI.

**File yang diperkirakan berubah:**

- `Application/Actions/UpdateUser.php`
- `Application/Actions/AssignUserRole.php`
- focused test UserManagement yang relevan.

- [x] Guard dan test selesai.
  - Kondisi awal: `UpdateUser` dan `AssignUserRole` belum menolak target
    protected pada Application boundary.
  - Perubahan: kedua action memuat `UserData` target melalui `UserRepository`
    dan melempar `ProtectedUserMutation` saat target tidak ditemukan atau
    `isProtected=true` sebelum persistence atau capability role dipanggil.
  - Alasan: frontend tidak boleh menjadi security boundary.
  - Acceptance: mutation protected ditolak; mutation user biasa tetap lulus.
  - Evidence: `php artisan test tests/Unit/UserManagementApplicationTest.php`
    lulus 10 test/36 assertion; presentation test lulus 8 test/56 assertion;
    `module:validate`, lint, dan type check lulus.

## Task 02 — Perjelas state arsip pada read model dan UI

- [x] State arsip selesai.
  - Kondisi awal: user terarsip dapat terlihat `active` dan UI berisiko masih
    menawarkan soft delete kedua.
  - Perubahan: `UserTable` menjadikan `Diarsipkan` sebagai badge utama,
    menampilkan status lifecycle sebagai informasi sekunder, dan menyembunyikan
    semua aksi mutation untuk row arsip. Aksi detail tetap tersedia.
  - Alasan: `deleted_at` harus ditampilkan sebagai availability state dominan.
  - Evidence: browser pada `?archive=archived` menampilkan `Diarsipkan` dan
    `Status terakhir: Tidak aktif`; hanya tombol `Lihat Alya Pratama` tersedia,
    tanpa console warning/error. `format:check`, lint, dan type check lulus.

## Task 03 — Restore user

- [x] Restore selesai.
  - Kondisi awal: capability restore belum ada.
  - Perubahan: menambah permission sensitif `user.restore`, policy `restore`,
    `RestoreUser`, contract/repository `restore()`, route, audit action
    `user.restored`, dan Dialog konfirmasi.
  - Alasan: user terarsip membutuhkan lifecycle yang aman dan dapat diaudit.
  - Evidence: feature test memulihkan user soft-deleted, memastikan event audit
    `user.restored`, dan browser membuka dialog Restore tanpa console error.

## Task 04 — Force delete user

- [x] Force delete selesai.
  - Kondisi awal: permission, policy, action, audit, UI, dan test belum ada.
  - Perubahan: menambah permission sensitif `user.force.delete`, policy
    `forceDelete`, `ForceDeleteUser`, contract/repository `forceDelete()`,
    route, audit action `user.force_deleted`, dan Dialog konfirmasi destruktif.
  - Alasan: penghapusan permanen bersifat sensitif dan harus eksplisit.
  - Evidence: feature test membuktikan record terhapus permanen, event audit
    terbit, serta user aktif dan `SuperSystem` ditolak. Browser memverifikasi
    dialog `Hapus permanen user?` tanpa console error. Regression berikutnya
    menemukan route belum masuk allowlist Ziggy; `config/ziggy.php` dan
    `ZiggyRouteTest` diperbarui agar route tersedia di frontend.

## Definition of Done ADR-0003

- [x] Mutation update dan assignment role target protected ditolak backend.
- [x] Arsip, restore, dan force delete memiliki semantics, permission, audit,
  UI, test positif/negatif, dan evidence yang jelas.
- [x] Task serta execution log diperbarui berdasarkan hasil nyata.
  - Kondisi awal: evidence quality checkpoint masih tersebar pada task
    individual.
  - Perubahan: mencatat test kontrak permission, feature lifecycle, unit action,
    browser verification, dan quality command pada execution log.
  - Alasan: seluruh perubahan ADR-0003 dapat ditinjau tanpa membaca riwayat chat.
  - Evidence: `php artisan test --filter=UserManagement` lulus 42 test/270
    assertion; format, lint, type check, module validation, dan diff check lulus.

## Revision History

| Versi | Tanggal | Perubahan |
| --- | --- | --- |
| 1.0 | 2026-08-06 | Membuat task rinci untuk implementasi ADR-0003. |
| 1.1 | 2026-08-06 | Menyelesaikan Task 01 guard backend SuperSystem. |
| 1.2 | 2026-08-06 | Menyelesaikan read model dan UI state arsip pada Task 02. |
| 1.3 | 2026-08-06 | Menyelesaikan Task 03 restore dan Task 04 force delete. |
| 1.4 | 2026-08-06 | Menutup quality checkpoint dan Definition of Done ADR-0003. |
| 1.5 | 2026-08-06 | Memperbaiki allowlist Ziggy untuk route restore dan force delete. |
| 1.6 | 2026-08-06 | Menetapkan toast dan loading button sebagai baseline mutation global. |
