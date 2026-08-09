# Tasks: Bulk Lifecycle User

## Task 00 — Preflight dan keputusan

- [x] Scope atomik disetujui.
  - Kondisi awal: soft delete dan force delete hanya tersedia per user.
  - Perubahan: batas 50 target, all-or-nothing, toast error sebelum mutation, dan audit per target dengan correlation ID batch.
  - Alasan: bulk destructive tidak boleh menghasilkan partial mutation.
  - Evidence: keputusan user 2026-08-10; `module:inspect`, discovery, list, dan validate lulus.

## Task 01 — Contract dan action

- [x] Contract bulk lifecycle selesai.
  - Kondisi awal: belum ada payload koleksi ID atau preflight lintas target.
  - Perubahan: menambah `BulkUserLifecycleRequest`, `BulkUserLifecycleResult`, dan `BulkUserLifecycle`. Request membatasi 1 sampai 50 ULID unik; action memeriksa seluruh target sebelum mutation.
  - Alasan: request tidak valid dan target yang salah state harus berhenti sebelum data mana pun berubah.
  - Evidence: feature test membuktikan payload kosong ditolak tanpa mutation; batch dengan target aktif pada force delete dibatalkan tanpa partial delete.

## Task 02 — Route, controller, dan audit

- [x] Presentation backend selesai.
  - Kondisi awal: route hanya menerima `{user}`.
  - Perubahan: menambah route `system.users.bulk-destroy` dan `system.users.bulk-force-delete` sebelum `/{user}`, middleware permission, controller tipis, toast sukses/error, dan allowlist Ziggy.
  - Alasan: route statis harus tidak tertangkap parameter `{user}` dan frontend hanya boleh membangun route yang diizinkan.
  - Evidence: `route:list --name=system.users.bulk` menampilkan dua route; test membuktikan archive/force delete sukses, audit, serta toast error atomik.

## Task 03 — UI selection dan dialog

- [x] Vertical slice frontend selesai secara code dan build.
  - Kondisi awal: tabel tidak memiliki selection atau bulk action.
  - Perubahan: `UserTable` menambah checkbox per user dan halaman, toolbar jumlah pilihan, serta `BulkUserLifecycleDialog` dengan confirmation dan loading state. Checkbox `SuperSystem` disabled; pilihan dibersihkan ketika filter, page, atau jumlah baris berubah. Daftar awal/aktif hanya menampilkan bulk archive, sedangkan `Arsip saja` hanya menampilkan bulk force delete.
  - Alasan: selection dibatasi pada halaman aktif dan operasi destructive wajib dikonfirmasi.
  - Evidence: ESLint dan TypeScript lulus. Browser test belum dapat dijalankan karena browser tidak tersedia pada sesi ini.

## Task 04 — Quality checkpoint

- [ ] Quality checkpoint menunggu browser test.
  - Kondisi awal: seluruh verification otomatis belum dijalankan.
  - Perubahan: PHPStan, test UserManagement, ESLint, TypeScript, diff check, dan validasi module telah dijalankan.
  - Evidence: `composer ci:check` menghasilkan 259 test/1206 assertion lulus,
    PHPStan 0 error, serta validasi module lulus.
  - Risiko: browser controller sesi ini melaporkan tidak ada browser tersedia, sehingga keyboard, dialog, dan console runtime belum dapat diverifikasi.
