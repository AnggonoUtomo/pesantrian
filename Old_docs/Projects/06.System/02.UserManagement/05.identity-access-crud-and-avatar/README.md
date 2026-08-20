# 05. Identity, Akses CRUD, dan Avatar

## Status

`Selesai - identity, akses CRUD, avatar, verifikasi email native, dan aktivitas
login telah memiliki vertical slice serta evidence.`

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
- Invitation email telah diselesaikan pada flow UserManagement terpisah; token
  reset password tetap dikelola native Laravel dan tidak dicatat pada log.
- Reset password oleh operator, Profile, dan Employee bukan bagian increment ini.
- Policy, permission, serta backend tetap menjadi security authority.
- `spatie/laravel-medialibrary` telah dipakai untuk collection avatar tunggal
  dengan validasi file; akses media berjalan melalui route module terotorisasi.

## Keputusan dan Status Risiko

- [ADR-0005](../decisions/ADR-0005-IDENTITY-ACCESS-AVATAR-AND-LOGIN-ACTIVITY.md) mencatat rancangan avatar, akses awal, verifikasi email, dan last login.
- Avatar disimpan sebagai media module dan ditampilkan lewat route avatar yang
  memerlukan authorization `view`; tidak ada URL storage mentah pada read model.
- Invitation email serta konfigurasi mail terenkripsi telah selesai dengan
  failure rollback dan evidence MailHog. Verifikasi email tetap native Laravel.
- Tidak ada OPEN RISK implementasi pada increment identity-access-avatar.
