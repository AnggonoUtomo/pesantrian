# ADR-0005 - Identity, Akses Awal, Avatar, dan Aktivitas Login

## Status

`Accepted - 14 Agustus 2026.`

## Context

UserManagement sudah dapat mengelola lifecycle user, tetapi data identitas, akses awal, verifikasi email, dan aktivitas login belum terbaca lengkap pada UI. Avatar juga membutuhkan owner media yang jelas.

## Keputusan

1. Avatar dimiliki model `User` melalui Spatie Media Library collection `avatar` dengan `singleFile` dan conversion `avatar-thumb` 256 x 256.
2. Create user dapat menerima role awal opsional dan status awal hanya setelah authorization backend melalui public capability AccessControl.
3. User baru direct-password berstatus `active` dan belum email verified. Email verification tetap dimiliki flow Laravel/Fortify.
4. `last_login_at` diperbarui oleh listener autentikasi sukses, bukan middleware setiap request.
5. Tabel dan dialog menampilkan identity, access, serta activity dengan fallback aman bila avatar atau timestamp belum ada.

## Keputusan Disk dan URL Avatar

Avatar memakai disk `local` privat melalui konfigurasi `MEDIA_DISK`, dengan
default `local`. Response UserManagement tidak mengirim path storage internal.
URL avatar menunjuk ke route milik module yang menerapkan policy `view` sebelum
mengirim file. Keputusan ini dipilih karena avatar hanya ditampilkan pada area
System yang terotorisasi.

Jika storage dipindah ke object storage atau CDN, akses privat dan authorization
route harus tetap dipertahankan. Perubahan menjadi URL public memerlukan ADR
pengganti karena mengubah batas keamanan data user.

## Konsekuensi

- Menambah dependency, config, migration media package, dan migration `last_login_at`.
- Menambah test filesystem, event login, authorization create, dan browser flow.
- Tidak menambah invitation email, reset password, Employee, atau Profile.
- Audit login hanya ditambahkan bila consumer AuditLog dan failure contract disetujui; timestamp login tetap dapat berjalan tanpa event audit baru.

## Evidence Implementasi

- `config/media-library.php` memakai `MEDIA_DISK` dengan default `local`, dan
  disk tersebut mengarah ke `storage/app/private`.
- `UserController::avatar()` hanya dapat dicapai melalui middleware policy
  `can:view,user`; resource hanya mengirim URL route module.
- `UserManagementAvatarTest` membuktikan upload, penggantian file tunggal,
  validasi tipe/ukuran, dan penghapusan avatar.
- Persetujuan status `Accepted` diberikan user pada 14 Agustus 2026.

## Evidence Awal

Media Library memakai `HasMedia` serta `InteractsWithMedia`. Collection single file sesuai untuk avatar karena upload baru mengganti file lama. Lihat [preparing your model](https://spatie.be/docs/laravel-medialibrary/v11/basic-usage/preparing-your-model) dan [defining media collections](https://spatie.be/docs/laravel-medialibrary/v11/working-with-media-collections/defining-media-collections).
