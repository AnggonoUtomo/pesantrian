# 05. Identity, Akses CRUD, dan Avatar

## Status

`Berjalan - INC-001 kontrak dan visual identitas selesai.`

Increment ini menyempurnakan pengelolaan user agar operator dapat memahami identitas, akses, dan aktivitas user tanpa membuka banyak halaman.

## Hasil yang Ditargetkan

- Tabel menampilkan avatar, nama, email, role efektif, status, verifikasi email, terakhir login, dan aksi.
- Modal tambah, edit, dan detail memakai kelompok informasi identitas, akses, dan aktivitas.
- Pembuatan user dapat menetapkan role serta status awal hanya bila actor memiliki permission yang tepat.
- Avatar dikelola melalui Spatie Media Library sebagai satu file per user.
- `last_login_at` dicatat hanya saat autentikasi berhasil.
- Status verifikasi email memakai `email_verified_at` bawaan Laravel. Tidak ada tombol verifikasi manual pada increment ini.

## Urutan Dokumen

1. [Specification](specification.md)
2. [Implementation plan](implementation-plan.md)
3. [Tasks](tasks.md)
4. [Execution log](planning/execution-log.md)

## Batasan

- Add, edit, dan detail tetap menggunakan `Dialog` modal, bukan `Sheet`.
- Invitation email, reset password oleh operator, Profile, dan Employee bukan bagian increment ini.
- Policy, permission, serta backend tetap menjadi security authority.
- Instalasi `spatie/laravel-medialibrary` menunggu INC-003; dependency belum ada pada `composer.json`.

## Keputusan dan Risiko Terbuka

- [ADR-0005](../decisions/ADR-0005-IDENTITY-ACCESS-AVATAR-AND-LOGIN-ACTIVITY.md) mencatat rancangan avatar, akses awal, verifikasi email, dan last login.
- Disk avatar perlu dipastikan sebelum coding. Rekomendasi awal adalah disk privat agar URL media tidak dapat diakses tanpa otorisasi.
- Pengiriman email verifikasi dan invitation tetap scope terpisah karena membutuhkan konfigurasi mail serta failure contract.
