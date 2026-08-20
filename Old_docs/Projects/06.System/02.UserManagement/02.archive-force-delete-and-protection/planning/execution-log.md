# Execution Log: Archive, Force Delete, dan Perlindungan User

## 2026-08-06 — Preflight ADR-0003

- Source dibaca: `AGENTS.md`, `docs/AGENTS.md`, README UserManagement,
  ADR-0002, ADR-0003, ADR-0004, dan dokumen increment filter daftar user.
- Keputusan: ADR-0002 dinyatakan selesai. Dokumennya berstatus `Accepted` dan
  focused command berikut lulus:

  ```bash
  php artisan test tests/Feature/UserManagementImpersonationTest.php
  ```

  Hasil: 4 test lulus, 33 assertion.
- Temuan: `UpdateUser` dan `AssignUserRole` belum memiliki guard backend untuk
  target `SuperSystem`; ini bertentangan dengan invariant ADR-0003.
- Perubahan dokumentasi: membuat increment `02.archive-force-delete-and-protection`.
- Risiko: restore dan force delete belum memiliki contract atau implementasi;
  tidak dikerjakan pada Task 01.

## 2026-08-06 — Task 01: Guard backend SuperSystem

- Test gagal dibuat lebih dahulu pada `tests/Unit/UserManagementApplicationTest.php`.
  Bukti awal: update protected mencoba memanggil repository mutation dan
  assignment role belum memiliki dependency repository untuk membaca target.
- Perubahan kode:
  - `Application/Actions/UpdateUser.php` memuat target dengan `find()` dan
    menolak target tidak ditemukan atau protected sebelum `update()`.
  - `Application/Actions/AssignUserRole.php` menerima `UserRepository`, memuat
    target berdasarkan identifier, lalu menolak target tidak ditemukan atau
    protected sebelum `RoleAssignmentCapability::assignRole()`.
  - Unit test menambah jalur negatif protected dan tetap membuktikan jalur
    positif user biasa.
- Verifikasi:

  ```bash
  php artisan test tests/Unit/UserManagementApplicationTest.php
  php artisan test tests/Feature/UserManagementPresentationTest.php
  php artisan module:validate System/UserManagement --json
  npm run lint:check
  npm run types:check
  git diff --check
  ```

  Hasil: 10 unit test/36 assertion dan 8 feature test/56 assertion lulus;
  module valid, lint dan type check lulus, serta tidak ada whitespace error.
- Risiko tersisa: read model/UI arsip, restore, dan force delete belum dibuat.

## 2026-08-06 — Task 02: State arsip pada UI

- Perubahan: `resources/js/pages/System/UserManagement/components/UserTable.tsx`
  memisahkan row tabel ke komponen kecil. Jika `deletedAt` terisi, badge utama
  menjadi `Diarsipkan`, status lama tampil sebagai informasi sekunder, dan
  seluruh tombol mutation disembunyikan. Tombol detail tetap tersedia.
- Alasan: `deleted_at` adalah availability state dominan; soft delete kedua,
  update, status, impersonation, dan role assignment tidak boleh ditawarkan
  pada user terarsip sebelum lifecycle khusus tersedia.
- Verifikasi:

  ```bash
  npm run format:check
  npm run lint:check
  npm run types:check
  ```

  Hasil: seluruh command lulus. Chrome DevTools pada
  `/system/users?archive=archived` menampilkan `Diarsipkan` dan status terakhir,
  hanya memiliki aksi detail, serta tidak memiliki console warning/error.
- Risiko tersisa: contract authorization untuk restore belum ditetapkan;
  force delete membutuhkan permission sensitif yang sudah diarahkan ADR-0003.

## 2026-08-06 — Task 03 dan Task 04: Restore dan force delete

- Keputusan: user menyetujui permission terpisah. `user.restore` dan
  `user.force.delete` keduanya `sensitive: true`. Nama force delete memakai dot
  notation karena `user.force-delete` ditolak oleh `module:validate`.
- Perubahan backend:
  - `permissions.php`, `UserManagementPolicy`, `UserRepository`, dan adapter
    Eloquent menambahkan capability restore/force delete.
  - `RestoreUser` menerbitkan `user.restored`; `ForceDeleteUser` menerbitkan
    `user.force_deleted`. Keduanya memeriksa permission, state arsip, dan
    perlindungan `SuperSystem` sebelum mutation.
  - route restore/force delete memakai `withTrashed()`. Controller menerima
    `User $user`, bukan string, agar policy dapat mengevaluasi `trashed()`.
- Perubahan frontend:
  - kolom Perlindungan dihapus dari `UserTable`.
  - row arsip menampilkan action Restore dan Hapus permanen bila permission
    tersedia; keduanya memakai Dialog konfirmasi.
- Evidence:

  ```bash
  php artisan test tests/Feature/UserManagementPresentationTest.php
  php artisan test tests/Unit/UserManagementApplicationTest.php
  php artisan module:validate System/UserManagement --json
  ```

  Hasil: 10 feature test/64 assertion dan 13 unit test/52 assertion lulus;
  module valid. Chrome DevTools membuktikan kolom Perlindungan hilang, action
  arsip tersedia untuk SuperSystem, dua dialog tampil, dan console bersih.

## 2026-08-06 — Quality checkpoint ADR-0003

```bash
php artisan test --filter=UserManagement
npm run format:check
npm run lint:check
npm run types:check
php artisan module:validate System/UserManagement --json
git diff --check
```

Hasil: 42 test/270 assertion lulus. Format Prettier, ESLint, TypeScript,
validasi module, dan pemeriksaan whitespace Git juga lulus. Test contract
`UserManagementContractTest` diperbarui dari enam menjadi delapan permission
dan memverifikasi `user.restore` serta `user.force.delete` berstatus sensitif.

## 2026-08-06 — Sinkronisasi permission lokal

```bash
php artisan access-control:seed
php artisan tinker --execute="...whereIn('name', ['user.restore', 'user.force.delete'])..."
```

Hasil: seeder AccessControl selesai dan registry Spatie lokal memuat
`user.force.delete,user.restore`. Opsi `--guard` pada `permission:show` tidak
tersedia pada environment ini, sehingga verifikasi memakai query read-only.

## 2026-08-06 — Perbaikan allowlist Ziggy

- Temuan browser: `ForceDeleteUserDialog` gagal membuat URL dengan error
  `route 'system.users.force-delete' is not in the route list`.
- Penyebab: route Laravel tersedia melalui `route:list`, tetapi belum tercantum
  pada `config/ziggy.php`.
- Perubahan: menambahkan `system.users.restore` dan
  `system.users.force-delete` ke allowlist serta regression assertion pada
  `tests/Feature/ZiggyRouteTest.php`.
- Acceptance: kedua route tersedia pada payload Ziggy sebelum dipakai dialog.
- Verifikasi:

  ```bash
  php artisan test tests/Feature/ZiggyRouteTest.php
  php artisan tinker --execute="...(new Ziggy)->toArray()['routes']..."
  ```

  Hasil: 2 test/15 assertion lulus. Runtime Ziggy memuat URI restore dan force
  delete beserta parameter `{user}`. `config:clear` sudah dijalankan agar
  konfigurasi lokal aktif pada request berikutnya.

## 2026-08-06 — Standar toast dan loading mutation

- Temuan: `<Toaster />` dan hook `useFlashToast` sudah aktif secara global,
  tetapi UserManagement masih mengirim flash `success`, bukan payload
  `toast`. Tombol CRUD hanya mengubah teks ketika request berjalan.
- Perubahan:
  - `UserController` memakai `Inertia::flash('toast', ['type' => 'success',
    'message' => ...])` untuk create, update, status, role, arsip, restore,
    force delete, dan impersonation.
  - `resources/js/components/ui/loading-button.tsx` menjadi tombol standar
    yang menampilkan `Spinner` dan otomatis disabled ketika `loading=true`.
  - Dialog mutation UserManagement memakai `LoadingButton` pada tombol submit.
  - `AGENTS.md` dan `docs/AGENTS.md` mencatat toast server tunggal serta
    indikator loading sebagai aturan UI/UX global CRUD.
- Verifikasi:

  ```bash
  php artisan test --filter=UserManagement
  npm run format:check
  npm run lint:check
  npm run types:check
  git diff --check
  ```

  Hasil: 43 test/272 assertion lulus. Test presentation membuktikan create
  user mengirim session flash `inertia.flash_data.toast` dengan type `success`.
