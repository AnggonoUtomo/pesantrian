# ADR-0005 - Identity, Akses Awal, Avatar, dan Aktivitas Login

## Status

`Proposed - siap menjadi acuan implementasi bertahap.`

## Context

UserManagement sudah dapat mengelola lifecycle user, tetapi data identitas, akses awal, verifikasi email, dan aktivitas login belum terbaca lengkap pada UI. Avatar juga membutuhkan owner media yang jelas.

## Keputusan yang Diusulkan

1. Avatar dimiliki model `User` melalui Spatie Media Library collection `avatar` dengan `singleFile` dan conversion `avatar-thumb` 256 x 256.
2. Create user dapat menerima role awal opsional dan status awal hanya setelah authorization backend melalui public capability AccessControl.
3. User baru direct-password berstatus `active` dan belum email verified. Email verification tetap dimiliki flow Laravel/Fortify.
4. `last_login_at` diperbarui oleh listener autentikasi sukses, bukan middleware setiap request.
5. Tabel dan dialog menampilkan identity, access, serta activity dengan fallback aman bila avatar atau timestamp belum ada.

## Open Decision

**Disk dan URL avatar** belum diputuskan.

Rekomendasi: gunakan disk privat dan endpoint/URL melalui otorisasi karena daftar user berada di area System. Ini lebih aman, tetapi membutuhkan contract untuk menampilkan file. Alternatif public disk lebih sederhana, namun URL avatar dapat dibuka jika diketahui.

## Konsekuensi

- Menambah dependency, config, migration media package, dan migration `last_login_at`.
- Menambah test filesystem, event login, authorization create, dan browser flow.
- Tidak menambah invitation email, reset password, Employee, atau Profile.
- Audit login hanya ditambahkan bila consumer AuditLog dan failure contract disetujui; timestamp login tetap dapat berjalan tanpa event audit baru.

## Evidence Awal

Media Library memakai `HasMedia` serta `InteractsWithMedia`. Collection single file sesuai untuk avatar karena upload baru mengganti file lama. Lihat [preparing your model](https://spatie.be/docs/laravel-medialibrary/v11/basic-usage/preparing-your-model) dan [defining media collections](https://spatie.be/docs/laravel-medialibrary/v11/working-with-media-collections/defining-media-collections).
